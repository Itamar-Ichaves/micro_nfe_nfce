<?php

namespace App\Service;

use App\Models\Cartao;
use App\Models\Destinatario;
use App\Models\Emitente;
use App\Models\IdeNfe;
use app\models\model\ConfiguracaoNfe;
use App\Models\Pagamento;
use App\Models\Produto;
use App\Models\TotalNfe;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use stdClass;

class ValidaDadosNfeService
{
    public static function validaDadosNfe($dados)
    { 
     // i($dados["pagamentos"]);
        $retorno = new stdClass();
        $notafiscal = new stdClass();

        try {
            if (!isset($dados["ide"])) {
                $retorno->titulo = "Erro ao ler objeto ide";
                throw new \Exception("É obrigatório o node IDE");
            }

            if (!isset($dados["emitente"])) {
                $retorno->titulo = "Erro ao ler objeto Emitente";
                throw new \Exception("É obrigatório o node EMITENTE");
            }

            if (!isset($dados["destinatario"])) {
                $retorno->titulo = "Erro ao ler objeto Destinatario";
                throw new \Exception("É obrigatório o node DESTINATARIO");
            }

            if (!isset($dados["itens"])) {
                $retorno->titulo = "Erro ao ler objeto";
                throw new \Exception("É obrigatório o envio do node Itens para emissão da NFE");
            }

            if (!isset($dados["pagamentos"])) {
                $retorno->titulo = "Erro ao ler objeto";
                throw new \Exception("É obrigatório o envio do node Pagamento para emissão da NFE");
            }

            //NODE ide
            $ide = self::validaIde($dados["ide"], $dados["emitente"], $dados["destinatario"]);
            $notafiscal->ide = $ide;

            //NODE Emitente
            $emitente = self::validaEmitente($dados["emitente"]);
            $notafiscal->emitente = $emitente;

            //NODE destinatario
            $destinatario = self::validaDestinatario($dados["destinatario"]);
            $notafiscal->destinatario = $destinatario;

            $total = new TotalNfe();
            foreach ($dados['itens'] as $item) {
                if (!isset($item['produto'])) {
                    $retorno->titulo = "Erro ao ler objeto";
                    throw new \Exception("É obrigatório o envio do node Produto para emissão da NFE");
                }

                $item['icms']['ipi'] = 0;
                $ipi = null;
                if (isset($item['ipi'])) {
                    $ipi = ValidarImpostoNfeService::validarIpi($item['ipi']);
                    if ($ipi->vIPI) {
                        $item['icms']['ipi'] = $ipi->vIPI;
                    }
                }
               
                if (!isset($item['icms'])) {
                    throw new \Exception('É obrigatório o envio do node icms para emissão da nota');
                }

                if (!isset($item['pis'])) {
                    throw new \Exception('É obrigatório o envio do node pis para emissão da nota');
                }

                if (!isset($item['cofins'])) {
                    throw new \Exception('É obrigatório o envio do node cofins para emissão da nota');
                }

                $it= new stdClass;
                $it->produto = self::validaProduto($item['produto']);
                $it->ipi= $ipi;
                $it->icms = ValidarImpostoNfeService::validarIcms($item['icms']);
                $it->pis = ValidarImpostoNfeService::validarPis($item['pis']);
                $it->cofins = ValidarImpostoNfeService::validarCofins($item['cofins']);

               

                // Totais
                $total->vBC        += $it->icms->vBC;
                $total->vICMS      += $it->icms->vICMS;
                $total->vICMSDeson += $it->icms->vICMSDeson;
                $total->vBCST      += $it->icms->vBCST;
                $total->vProd      += $it->produto->vProd;
                $total->vFrete     += $it->produto->vFrete;
                $total->vSeg       += $it->produto->vSeg;
                $total->vDesc      += $it->produto->vDesc;
                $total->vII        += null;
                $total->vIPI        = $it->ipi->vIPI;
                $total->vPIS        = $it->pis->vPIS;
                $total->vCOFINS     = $it->cofins->vCOFINS;
                $total->vOutro      = $it->produto->vOutro;
               // $total->vIPIDevol   = $it->vIPIDevol;
                //$total->vTotTrib    = $it->vTotTrib;
                $total->vFCP        = $it->icms->vFCP;
                $total->vFCPST      = $it->icms->vFCPST;
                $total->vFCPSTRet   = $it->icms->vFCPSTRet;
                $total->vFCPUFDest  = $it->icms->vFCPUFDest;
               // $total->vICMSUFRemet = $it->vICMSUFRemet;
                $total->vNF         = $it->produto->vProd + $it->produto->vFrete + $it->produto->vSeg + $it->produto->vOutro + $total->vIPI + $it->icms->vFCPST - $it->produto->vDesc - $it->icms->vICMSDeson;

                $notafiscal->itens[] = $it;
            }
            $notafiscal->total =$total;
             //Nó Pagamento
             $pagamentos   = self::validarPagamentos($dados["pagamentos"]);
             $notafiscal->pagamentos = $pagamentos;
 
             //Popoular Configuracao
            //$configuracao           = self::validarConfiguracao($dados["emitente"], $dados["ide"], $dados["certificado"]);
           // $notafiscal->configuracao = $configuracao;


            $retorno->tem_erro = false;
            $retorno->erro = "";
            $retorno->notafiscal = $notafiscal;
            return $retorno;
        } catch (\Throwable $th) {
            $retorno->tem_erro = true;
            $retorno->erro = $th->getMessage();
            $retorno->notafiscal = null;
            return $retorno;
        }
    }

    public static function validaIde($array, $emitente, $destinatario)
    {
        $dados = (object) $array;
        $emitente = (object) $emitente;
        $destinatario = (object) $destinatario;

        $requiredFields = [
            'nNF', 'natOp', 'mod', 'serie', 'dhEmi', 'tpImp', 'tpEmis',
            'tpAmb', 'finNFe', 'indFinal', 'procEmi', 'verProc', 'modFrete'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($dados->$field) || is_null($dados->$field)) {
                throw new \Exception("O Campo $field é Obrigatório");
            }
        }

        if (!isset($emitente->UF) || is_null($emitente->UF)) {
            throw new \Exception('O Campo UF do node Emitente é Obrigatório');
        }

        if (!isset($destinatario->UF) || is_null($destinatario->UF)) {
            throw new \Exception('O Campo UF do node Destinatario é Obrigatório');
        }

        if ($emitente->UF != "EX") {
            if ($emitente->UF == $destinatario->UF) {
                $dados->idDest = config("constanteNota.idDest.INTERNA");
            } else {
                $dados->idDest = config("constanteNota.idDest.INTERESTADUAL");
            }
        } else {
            $dados->idDest = config("constanteNota.idDest.EXTERIOR");
        }

        $ide = new IdeNfe();
        $ide->setarDados($dados);
        return $ide;
    }

    public static function validaEmitente($array)
    {
        $dados = (object) $array;
        $requiredFields = [
            'CNPJ', 'xNome', 'xLgr', 'nro', 'xBairro', 'cMun', 'xMun', 'UF',
            'CEP', 'IE', 'CRT'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($dados->$field) || is_null($dados->$field)) {
                throw new \Exception("O Campo $field do node Emitente é Obrigatório");
            }
        }

        $emitente = new Emitente();
        $emitente->setarDados($array);
        return $emitente;
    }

    public static function validaDestinatario($array)
    {
        $dados = (object) $array;
        $requiredFields = [
            'xNome', 'xLgr', 'nro', 'xBairro', 'cMun', 'UF', 'CEP', 'CPF_CNPJ', 'indIEDest'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($dados->$field) || blank($dados->$field) || is_null($dados->$field)) {
                throw new \Exception("O Campo $field do node Destinatario é Obrigatório");
            }
        }

        $cnpj = tira_mascara($dados->CPF_CNPJ);
        if (strlen($cnpj) == 14) {
            $dados->CNPJ = $cnpj;
            $dados->CPF = null;
        } else {
            $dados->CPF = $cnpj;
            $dados->CNPJ = null;
        }

        $destinatario = new Destinatario();
        $destinatario->setarDados($array);
        return $destinatario;
    }

    public static function validaProduto($array)
    {
        $dados = (object) $array;
        $requiredFields = [
            'cProd', 'cEAN', 'xProd', 'NCM', 'CFOP', 'uCom', 'qCom', 'vUnCom', 'vProd',
            'cEANTrib', 'uTrib', 'qTrib', 'vUnTrib', 'indTot'
        ];

        foreach ($requiredFields as $field) {
            if (!isset($dados->$field) || is_null($dados->$field)) {
                throw new \Exception("O Campo $field do node Produto é Obrigatório");
            }
        }

        $produto = new Produto();
        $produto->setarDados($array);
        return $produto;
    }

    public static function validarPagamentos($array){
         
        if(count($array) <=0) {
            throw new \Exception('É Obrigatório ter pelo menos um pagamento');
        }
        $pagamentos = array();
        foreach($array as $pag){
            $detalhe = $pag["detalhe"] ?? null;
            if(!$detalhe){
                throw new \Exception('É obrigatório informar os detalhes do pagamento');
            }
            if(!isset($detalhe["tPag"]) ||  is_null($detalhe["tPag"])) {
                throw new \Exception('O Campo tPag do Node Pagamento é Obrigatório');
            }
            if(!isset($detalhe["vPag"]) ||  is_null($detalhe["vPag"])) {
                throw new \Exception('O Campo vPag do Node Pagamento é Obrigatório');
            }

            $pagamento = new Pagamento();
            $pagamento->setarDados($detalhe);

            $card = $pag["cartao"] ?? null;
            $cartao = null;
          
            if($card){
                if(!isset($card["tpIntegra"]) ||  is_null($card["tpIntegra"])) {
                    throw new \Exception('O Campo tpIntegra do Node Pagamento é Obrigatório');
                }
                $cartao = new Cartao();
                $cartao->setarDados($card);
            }

            $pagamentos[] = array(
                "pagamento" => $pagamento,
                "cartao"    => $cartao
            );

        }
        
        return $pagamentos;
    }

 public static function validarConfiguracao($emitente, $ide, $certificado){
        $arr = [
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb"       => intVal($ide->tpAmb),
            "razaosocial" => $emitente->xNome,
            "cnpj"        => $emitente->CNPJ,
            "siglaUF"     => $emitente->UF,
            "schemes"     => "PL_009_V4",
            "versao"      => '4.00',
            "tokenIBPT"   => "",
            "CSC"         => "",
            "CSCid"       => "",
            "proxyConf"   => [
                "proxyIp"   => "",
                "proxyPort" => "",
                "proxyUser" => "",
                "proxyPass" => ""
            ]
        ];
        $objeto                     = new ConfiguracaoNfe();
        $configJson                 = json_encode($arr);
        $objeto->cnpj               = $emitente->CNPJ;
        $objeto->tpAmb              = $ide->tpAmb;
        $objeto->tools              = new Tools($configJson, Certificate::readPfx($certificado->arquivo_binario, $certificado->senha));
        $objeto->pastaAmbiente      = ($ide->tpAmb == "1") ? "producao" : "homologacao";
        $objeto->pastaEmpresa       = $emitente->CNPJ;
        $objeto->tools->model($ide->mod);
       // return $objeto;
    }
}

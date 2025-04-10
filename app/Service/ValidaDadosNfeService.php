<?php

namespace App\Service;

use App\Models\{
    Cartao, Destinatario, Duplicata, Emitente, Fatura, IdeNfe, Pagamento, Produto, TotalNfe, Certificado_digital
};
 
use App\Models\AutXml;
use App\Models\ConfiguracaoNfe;
use App\Models\RespTecnico;
use App\Service\CertificadoDigitalService;
use NFePHP\Common\Certificate;
use NFePHP\NFe\Tools;
use stdClass;

class ValidaDadosNfeService
{
    protected $certificadoService;

    public function __construct(CertificadoDigitalService $certificadoService)
    {
        $this->certificadoService = $certificadoService;
    }
    
    /**
     * Valida os dados da NF-e.
     *
     * @param array $dados Dados da NF-e a serem validados.
     * @return stdClass Objeto contendo os dados validados.
     * @throws \Exception Caso algum dado obrigatório esteja ausente.
     */
    public static function validaDadosNfe(array $dados, $token_company, $token_emitente): stdClass
    {
         
        $retorno = new stdClass();
        $notafiscal = new stdClass();

        // Validação dos nós principais
        self::validarNodePrincipal($dados);
        
        // Validação do nó IDE
        $ide = self::validaIde($dados['ide'], $dados['emitente'], $dados['destinatario']);
        $notafiscal->ide = $ide;
        
        // Validação do nó Emitente
        $emitente = self::validaEmitente($dados['emitente']);
        $notafiscal->emitente = $emitente;
        
        // Validação do nó Destinatário
        $destinatario = self::validaDestinatario($dados['destinatario']);
        $notafiscal->destinatario = $destinatario;
        
        // Validação dos itens
        $total = new TotalNfe();
        $notafiscal->itens = self::validaItens($dados['itens'], $total);
        
        // Nó Pagamento
        $notafiscal->pagamentos = self::validarPagamentos($dados['pagamentos'] ?? []);

        // Nó Transportadora (opcional)
        if (!empty($dados['transporte'])) {
            $notafiscal->transporte = self::validarTransporte($dados['transporte']);
        }
         
        // Nó Cobrança (opcional)
        /*if (!empty($dados['cobranca'])) {
            $notafiscal->cobranca = self::validarCobranca($dados['cobranca']);
        }*/
        
        // no infoResp Tecnico
        if (!empty($dados['infRespTec'])) {
            $notafiscal->infoRespTecnico = self::validarInfoRespTecnico($dados['infRespTec']);
        }
         
        // no autXml
        if (!empty($dados['autXML'])) {
            $notafiscal->autXML = self::validarAutXml($dados['autXML']);
        }
        // Nó Adicionais (opcional)
        if (!empty($dados['adicionais'])) {
            $notafiscal->adicionais = self::validarAdicionais($dados['adicionais']);
        }

        //$service = new CertificadoDigitalService();
        $certificado = CertificadoDigitalService::lerCertificadoDigital($token_company);
       
         
        
        $notafiscal->certificado_digital = $certificado;
        

        // Configuração
        $notafiscal->configuracao = self::validarConfiguracao($emitente, $ide, $certificado);
        
        $retorno->tem_erro = false;
        $retorno->erro = '';
        $retorno->notafiscal = $notafiscal;

        return $retorno;
    }

    /**
     * Valida os nós principais da NF-e.
     *
     * @param array $dados Dados da NF-e.
     * @throws \Exception Caso algum nó principal esteja ausente.
     */
    private static function validarNodePrincipal(array $dados): void
    {
        //dd($dados);
        $nodesObrigatorios = ['ide', 'emitente', 'destinatario', 'itens'];

        foreach ($nodesObrigatorios as $node) {
            if (empty($dados[$node])) {
                throw new \Exception("obrigatorio o envio do node '{$node}' para emissao da NF-e.");
            }
        }
    }

    /**
     * Valida os itens da NF-e.
     *
     * @param array $itens Itens da NF-e.
     * @param TotalNfe $total Objeto para cálculo dos totais.
     * @return array Lista de itens validados.
     * @throws \Exception Caso algum item esteja inválido.
     */
    private static function validaItens(array $itens, TotalNfe $total): array
    {
        $itensValidados = [];
         
        foreach ($itens as $item) {
            $it = new stdClass();
             
            if (empty($item['produto'])) {
                throw new \Exception("\ obrigatorio o envio do node 'Produto' para emissao da NF-e.");
            }

            $it->produto = self::validaProduto($item['produto']);
            $it->icms = ValidarImpostoNfeService::validarIcms($item['icms'] ?? []);
            $it->pis = ValidarImpostoNfeService::validarPis($item['pis'] ?? []);
            $it->cofins = ValidarImpostoNfeService::validarCofins($item['cofins'] ?? []);

            if (!empty($item['ipi'])) {
                $it->ipi = ValidarImpostoNfeService::validarIpi($item['ipi']);
            }

            self::calcularTotais($it, $total);
            $itensValidados[] = $it;

            
        }
         
        return $itensValidados;
    }

    /**
     * Calcula os totais da NF-e com base no item atual.
     *
     * @param stdClass $item Item da NF-e.
     * @param TotalNfe $total Objeto para acumular os totais.
     */
    private static function calcularTotais(stdClass $item, TotalNfe $total): void
{
    // Inicializar valores do ICMS com 0, caso não estejam presentes
    $vBC = $item->icms->vBC ?? 0;
    $vICMS = $item->icms->vICMS ?? 0;

    // Verificar CSTs que desconsideram a base e o ICMS
    if (in_array($item->icms->CST, ['102', '103', '300', '400'], true)) {
        $vBC = 0;
        $vICMS = 0;
    }

    // Acumular valores nos totais
    $total->vBC += round($vBC, 2);
    $total->vICMS += round($vICMS, 2);
    $total->vProd += round($item->produto->vProd, 2);
    $total->vIPI += round($item->ipi->vIPI ?? 0, 2);
    $total->vPIS += round($item->pis->vPIS ?? 0, 2);
    $total->vCOFINS += round($item->cofins->vCOFINS ?? 0, 2);
}


     



    public static function validaIde($array, $emitente, $destinatario)
    {
        $dados = (object) $array;
        $emitente = (object) $emitente;
        $destinatario = (object) $destinatario;

        $requiredFields = [
            'nNF', 'natOp', 'mod', 'serie', 'dhEmi', 'tpImp', 'tpEmis',
            'tpAmb', 'finNFe', 'indFinal', 'procEmi', 'verProc' 
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

    // Verificar e adicionar CPF/CNPJ no array
    $cnpj = tira_mascara($dados->CPF_CNPJ);
    if (strlen($cnpj) == 14) {
        $array['CNPJ'] = $cnpj;
        $array['CPF'] = null;
    } else {
        $array['CPF'] = $cnpj;
        $array['CNPJ'] = null;
    }

    // Criar e configurar o destinatário
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

    public static function validarPagamentos(array $array): array
{
    // Verifica se o array de pagamentos não está vazio
    if (count($array) <= 0) {
        throw new \Exception('É Obrigatório ter pelo menos um pagamento');
    }

    $pagamentos = [];
   //dd( $array );
    foreach ($array as $pag) {
        // Verifica se o detalhe do pagamento está presente
        $detalhe = $pag["detalhe"] ?? null;
        
        if (is_null($detalhe)) {
            throw new \Exception('É obrigatório informar os detalhes do pagamento');
        }
        
        // Verifica os campos obrigatórios de tPag e vPag
        if (empty($detalhe["tPag"])) {
            throw new \Exception('O Campo tPag do Node Pagamento é Obrigatório detalhes do pagamento');
        }
        if (empty($detalhe["vPag"])) {
            throw new \Exception('O Campo vPag do Node Pagamento é Obrigatório');
        }

        // Cria um objeto de Pagamento e seta os dados
        $pagamento = new Pagamento();
        $pagamento->setarDados($detalhe);

        // Verifica se existe o cartão  
        $cartao = null;
        $card = $pag["cartao"] ?? null;
        //dd($card);
        if ($card) {
            if (empty($card["tpIntegra"])) {
                throw new \Exception('O Campo tpIntegra do Node Pagamento é Obrigatório');
            }

        }

        // Cria um objeto Cartao e seta os dados
        $cartao = new Cartao();
        $cartao->setarDados($card);

        
        // Adiciona o pagamento e o cartão à lista
        $pagamentos[] = [
            "pagamento" => $pagamento,
            "cartao"    => $cartao
        ];
    }

    return $pagamentos;
}


public static function validarConfiguracao($emitente, $ide, $certificado)
{
   //  dd($emitente->CNPJ);
    // Array de configuração para a NFe
    $arr = [
        "atualizacao" => date('Y-m-d H:i:s'), // Corrigido para hora no formato de 24 horas
        "tpAmb"       => intVal($ide->tpAmb), // Certificando que tpAmb é inteiro
        "razaosocial" => $emitente->xNome,
        "cnpj"        => $emitente->CNPJ,
        "siglaUF"     => $emitente->UF,
        "schemes"     => "PL_009_V4", // Versão do schema
        "versao"      => '4.00', // Versão da NFe
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

   
   // $objeto->pastaAmbiente      = ($ide->tpAmb == "1") ? "producao" : "homologacao";
    // Criação do objeto de configuração
    $objeto = new ConfiguracaoNfe();

    // Codificando o array em JSON
    $configJson = json_encode($arr);

    // Atribuindo valores ao objeto ConfiguracaoNfe
    $objeto->cnpj               = $emitente->CNPJ;
    $objeto->tpAmb              = $ide->tpAmb;
    $objeto->tools              = new Tools($configJson, Certificate::readPfx($certificado->binario, $certificado->dados['senha']));
    $objeto->pastaAmbiente      = ($ide->tpAmb == "1") ? "producao" : "homologacao";

    // Caso a pasta já tenha sido previamente configurada, utilize a variável de caminho diretamente
    $objeto->pastaEmpresa       = $emitente->CNPJ; // Caminho já existente
    $objeto->tools->model($ide->mod);
    
   // dd($objeto);
    return $objeto;
}

    public static function validarCobranca($dados_cobranca){
        $cobranca = new stdClass();
        //$cobranca->fatura = null;
       // $cobranca->duplicatas = array();

        //Dados da fatura
        if(isset($dados_cobranca->fatura)){
            $fatura = new Fatura();
            $fatura->setarDados($dados_cobranca->fatura);
            $cobranca->fatura = $fatura;
        }

        //Dados da Duplicata
        if(isset($dados_cobranca->duplicatas)){
            if(count($dados_cobranca->duplicatas) <=0) {
                throw new \Exception('É Obrigatório ter pelo menos uma duplicata');
            }
            $i = 1;
            foreach($dados_cobranca->duplicatas as $dup){
                if(!isset($dup->dVenc) ||  is_null($dup->dVenc)) {
                    throw new \Exception('O Campo dVenc do Node Duplicata é Obrigatório');
                }
                if(!isset($dup->vDup) ||  is_null($dup->vDup)) {
                    throw new \Exception('O Campo vDup do Node Duplicata é Obrigatório');
                }
                $dup->nDup = zeroEsquerda($i++,3);
                $duplicata = new Duplicata();
                $duplicata->setarDados($dup);
                $cobranca->duplicatas[] = $duplicata;
            }
        }
        return $cobranca;
    }

    public static function validarInfoRespTecnico($infRespTec)
{
    $dados = (object) $infRespTec;
    $requiredFields = ['CNPJ', 'xContato', 'email', 'fone'];

    foreach ($requiredFields as $field) {
        if (!isset($dados->$field) || blank($dados->$field) || is_null($dados->$field)) {
            throw new \Exception("O campo $field do node InfoRespTecnico é obrigatório.");
        }
    }

    // Cria o objeto RespTecnico com os dados validados
    $respTec = new RespTecnico();
    $respTec->setarDados($infRespTec);
    
    return $respTec;
}


public static function validarAutXml($autXML)
{
    $dados = (object) $autXML;

    // Verifica se CNPJ ou CPF está preenchido
    if (
        (!isset($dados->CNPJ) || blank($dados->CNPJ) || is_null($dados->CNPJ)) 
    ) {
        throw new \Exception("É obrigatório informar o CNPJ no node AutXml.");
    }

    // Valida formato do CNPJ (14 dígitos)
    if (isset($dados->CNPJ) && !blank($dados->CNPJ)) {
        if (!preg_match('/^\d{14}$/', $dados->CNPJ)) {
            throw new \Exception("O CNPJ informado no node AutXml não é válido.");
        }
    }

    // Cria a instância de AutXml e configura os dados
    $aut = new AutXml();
    $aut->setarDados($autXML);
    
    return $aut;
}




}
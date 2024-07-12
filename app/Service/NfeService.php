<?php

namespace app\Service;

use App\Models\CofinsNfe;
use App\Models\Destinatario;
use App\Models\Duplicata;
use App\Models\Emitente;
use App\Models\Fatura;
use App\Models\IcmsNfe;
use App\Models\Ide;
use App\Models\IdeNfe;
use App\Models\IpiNfe;
use App\Models\Pagamento;
use App\Models\PisNfe;
use App\Models\Produto;
use App\Models\Reboque;
use App\Models\RetencaoTransporte;
use App\Models\TotalNfe;
use App\Models\Transportadora;
use App\Models\Veiculo;
use App\Models\Volume;
use Exception;
use NFePHP\Common\Certificate;
use NFePHP\DA\NFe\Daevento;
use NFePHP\DA\NFe\Danfe;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Make;
use stdClass;

class NfeService{
    public static function gerarNfe($notafiscal){
        //dd($notafiscal);
        $nfe = new Make();
        $std = new stdClass();
        $std->versao = '4.00'; //versão do layout (string)
        $std->Id = ''; //se o Id de 44 digitos não for passado será gerado automaticamente
        $std->pk_nItem = null; //deixe essa variavel sempre como NULL
        $nfe->taginfNFe($std);

        IdeNfe::montarXml($nfe, $notafiscal->ide);
        Emitente::montarXml($nfe, $notafiscal->emitente);
        Destinatario::montarXml($nfe, $notafiscal->destinatario);
        
        $cont = 1;
        foreach($notafiscal->itens as $item){
            Produto::montarXml($cont, $nfe, $item->produto);
            if(isset($item->ipi->CST)){
                IpiNfe::montarXml($cont,$nfe, $item->ipi);
            }
            IcmsNfe::montarXml($cont,$nfe, $item->icms);
            PisNfe::montarXml($cont,$nfe, $item->pis);
            CofinsNfe::montarXml($cont,$nfe, $item->cofins);
        
            $cont++;
        }
        $std           = new \stdClass();
        $std->modFrete = $notafiscal->ide->modFrete ;
        $nfe->tagtransp($std);

        if($notafiscal->ide->modFrete!="9"){
            if(isset($notafiscal->transporte)){
                $transporte = $notafiscal->transporte;

                if($transporte->transportadora){
                    Transportadora::montarXml($nfe, $transporte->transportadora);
                }
                if($transporte->retencao){
                    RetencaoTransporte::montarXml($nfe, $transporte->retencao);
                }
                if($transporte->veiculo){
                    Veiculo::montarXml($nfe, $transporte->veiculo);
                }
                if($transporte->reboque){
                    Reboque::montarXml($nfe, $transporte->reboque);
                }
                if($transporte->volume){
                    Volume::montarXml($nfe, $transporte->volume);
                }                
            }
        }

         //Pagamentos
        $std                 = new \stdClass();
        $std->vTroco         = $notafiscal->ide->vTroco ?? null ;
        $nfe->tagpag($std);
        
        foreach($notafiscal->pagamentos as $pagamento){
            Pagamento::montarXml($nfe,$pagamento["pagamento"], $pagamento["cartao"]);
        }

        if(isset($notafiscal->cobranca)){
            Fatura::montarXml($nfe, $notafiscal->cobranca->fatura);
            foreach($notafiscal->cobranca->duplicatas as $dup){
                 Duplicata::montarXml($nfe, $dup);
            }
         }

         TotalNfe::montarXml($nfe, $notafiscal->total);
         i($nfe);
    }


    

    public static function gerarXml($nfe, $configuracao){
        $retorno = new stdClass;
        try {
            $resultado = $nfe->montaNFe();
            if($resultado){
                $xml    = $nfe->getXML();
                $chave  = $nfe->getChave();

                $path ="notas/". $configuracao->pastaEmpresa."/xml/nfe/".$configuracao->pastaAmbiente."/temporarias/";
                $nome_arquivo = $chave."-nfe.xml";

                if (!file_exists($path)){
                    mkdir($path, 07777, true);
                }

                file_put_contents($path.$nome_arquivo, $xml);
                chmod($path, 07777);

                $retorno->tem_erro  = false;
                $retorno->titulo    = "arquivo XML gerado com Sucesso";
                $retorno->erro      = "";
                $retorno->chave     = $chave;
                $retorno->xml       = $xml;
            }else{
                $retorno->tem_erro  = true;
                $retorno->titulo    = "Não foi possível gerar o XML";
                $retorno->erro      = $nfe->getErrors();
            }
        } catch (\Throwable $th) {
            $retorno->tem_erro = true;
            $retorno->titulo = "Não foi possível gerar o XML";
            if($nfe->getErrors() !=null){
                $retorno->erro = $nfe->getErrors();
            }else{
                $retorno->erro = $th->getMessage();
            }
        }

        return $retorno;
    } 

    public static function assinarXml($xml, $chave, $configuracao){
        $retorno = new \stdClass();
        try {
            $response = $configuracao->tools->signNFe($xml);

            $path ="notas/". $configuracao->pastaEmpresa."/xml/nfe/".$configuracao->pastaAmbiente."/assinadas/";
            $nome_arquivo = $chave."-nfe.xml";

            if (!file_exists($path)){
                mkdir($path, 07777, true);
            }

            file_put_contents($path.$nome_arquivo, $response);
            chmod($path, 07777);

            $retorno->tem_erro  = false;
            $retorno->titulo    = "XML assinado com sucesso";
            $retorno->erro      = "";
            $retorno->xml       = $response;

        } catch (\Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "Erro ao assinar o XML";
            $retorno->erro      = $e->getMessage();
        }

       
    }


    public static function enviarXML($xml, $chave, $configuracao, $nNF){
        $retorno = new \stdClass();
        try {
            $idLote = str_pad($nNF, 15, '0', STR_PAD_LEFT);
            //envia o xml para pedir autorização ao SEFAZ
            $resp = $configuracao->tools->sefazEnviaLote([$xml], $idLote);
            sleep(2);
            //transforma o xml de retorno em um stdClass
            $st = new Standardize();
            $std = $st->toStd($resp);
            if ($std->cStat != 103) {
                $retorno->tem_erro  = true;
                $retorno->titulo    = "Não foi possível enviar o XML para a Sefaz";
                $retorno->erro      = "[$std->cStat] $std->xMotivo";
                return $retorno;
            }
            $retorno->tem_erro  = false;
            $retorno->titulo    = "XML enviado com sucesso";
            $retorno->erro      = "";
            $retorno->recibo    = $std->infRec->nRec;

        } catch (\Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "Erro ao enviar o lote para a Sefaz";
            $retorno->erro      = $e->getMessage();
        }
        return $retorno;
    }

    public static function consultarPorRecibo($xml, $chave, $recibo, $configuracao){
        $retorno = new \stdClass();
        try {
            //consulta número de recibo
            //$numeroRecibo = número do recíbo do envio do lote
            $xmlResp = $configuracao->tools->sefazConsultaRecibo($recibo, $configuracao->tpAmb);

            //transforma o xml de retorno em um stdClass
            $st = new Standardize();
            $std = $st->toStd($xmlResp);

            if ($std->cStat=='103') { //lote enviado
                $retorno->tem_erro  = true;
                $retorno->titulo    = "Protocolo ainda não disponível";
                $retorno->erro      = "O lote ainda não foi processado";
                $retorno->status    = "EM_PROCESSAMENTO";

                return $retorno;
            }
            if ($std->cStat=='105') { //lote em processamento
                $retorno->tem_erro  = true;
                $retorno->titulo    = "Protocolo sendo processado";
                $retorno->erro      = "Lote em processamento, tente, mais tarde";
                $retorno->status    = "EM_PROCESSAMENTOI";
                return $retorno;
            }

            if ($std->cStat=='104') { //lote processado (tudo ok)
                if ($std->protNFe->infProt->cStat=='100') { //Autorizado o uso da NF-e
                    $protocolo = $std->protNFe->infProt->nProt;
                    $xml_autorizado = Complements::toAuthorize($xml, $xmlResp);

                    $path           = "notas/". $configuracao->pastaEmpresa."/xml/nfe/". $configuracao->pastaAmbiente."/autorizadas/";
                    $nome_arquivo   = $chave."-nfe.xml";

                    if (!file_exists($path)){
                        mkdir($path, 07777, true);
                    }

                    file_put_contents($path.$nome_arquivo, $xml_autorizado);
                    chmod($path, 07777);

                   // Service::inserir(["cnpj" =>$configuracao->cnpj,"chave"=>$chave,"protocolo"=>$protocolo,"recibo"=>$recibo, "tipo"=>"nfe"],"nota");

                    $retorno->tem_erro  = false;
                    $retorno->titulo    = "XML autorizado com sucesso";
                    $retorno->erro      = "";
                    $retorno->recibo    = $recibo;
                    $retorno->chave     = $chave;
                    $retorno->status    = "AUTORIZADO";
                    $retorno->protocolo = $protocolo;
                    $retorno->xml       = $xmlResp;

                } elseif (in_array($std->protNFe->infProt->cStat,["110", "301", "302"])) { //DENEGADAS
                    $path           = "notas/". $configuracao->pastaEmpresa."/xml/nfe/". $configuracao->pastaAmbiente."/denegadas/";
                    $nome_arquivo   = $chave."-nfe.xml";

                    if (!file_exists($path)){
                        mkdir($path, 07777, true);
                    }
                   // Service::inserir(["cnpj" =>$configuracao->cnpj,"chave"=>$chave,"recibo"=>$recibo],"nota");
                    $retorno->tem_erro  = true;
                    $retorno->titulo    = "Nota Denegada";
                    $retorno->erro      = $std->protNFe->infProt->cStat . ":". $std->protNFe->infProt->xMotivo ;
                    $retorno->protocolo = $std->protNFe->infProt->nProt;
                    $retorno->xml       = $xmlResp;
                    $retorno->status    = "DENEGADA";
                    $retorno->cstat     = $std->protNFe->infProt->cStat;
                    return $retorno;
                } else { //não autorizada (rejeição)
                    $retorno->tem_erro  = true;
                    $retorno->titulo    = "Nota Rejeitada";
                    $retorno->erro      = $std->protNFe->infProt->cStat . ":". $std->protNFe->infProt->xMotivo ;
                    $retorno->cstat     = $std->protNFe->infProt->cStat;
                    $retorno->status    = "REJEITADO";
                    return $retorno;
                }
            } else { //outros erros possíveis
                $retorno->tem_erro  = true;
                $retorno->titulo    = "Nota Rejeitada";
                $retorno->erro      = $std->cStat . ":". $std->xMotivo ;
                $retorno->cstat     = $std->cStat;
                $retorno->status    = "REJEITADO";
                return $retorno;
            }

        } catch (\Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "Erro ao consultar a nota na Sefaz";
            $retorno->erro      = $e->getMessage();
            $retorno->status    = "REJEITADO";
            return $retorno;
        }
        return $retorno;
    }
   
    public static function danfe($xml){
        $retorno = new \stdClass();
        try {
          // $logo = 'data://text/plain;base64,'. base64_encode(file_get_contents(realpath(__DIR__ . '/../images/tulipas.png')));
            //$logo = realpath(__DIR__ . '/../images/tulipas.png');

            $danfe = new Danfe($xml);
            
            $danfe->exibirTextoFatura = true;
            $danfe->exibirPIS = true;
           // $danfe->exibirCOFINS = true;
            $danfe->exibirIcmsInterestadual = false;
            $danfe->exibirValorTributos = true;
            $danfe->descProdInfoComplemento = false;
            $danfe->exibirNumeroItemPedido = false;
            $danfe->setOcultarUnidadeTributavel(true);
            $danfe->obsContShow(false);
            $danfe->printParameters(
                $orientacao = 'P',
                $papel = 'A4',
                $margSup = 2,
                $margEsq = 2
                );
           // $danfe->logoParameters($logo, $logoAlign = 'C', $mode_bw = false);
            $danfe->setDefaultFont($font = 'times');
            $danfe->setDefaultDecimalPlaces(4);
            $danfe->debugMode(false);
            $danfe->creditsIntegratorFooter('mjailton Sistemas - mjailton.com.br');            
            //Gera o PDF
            $pdf = $danfe->render();

            $retorno->tem_erro  = false;
            $retorno->titulo    = "Pdf gerado com sucesso";
            $retorno->erro      = "";
            $retorno->pdf       = $pdf;
            return $retorno;
        } catch (\Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "Erro gerar o PDF";
            $retorno->erro      = $e->getMessage();
            $retorno->pdf       = NULL;
            return $retorno;
        }
        return $retorno;
    }

    public static function cancelarNfe($justificativa, $chave, $configuracao){
        $retorno = new \stdClass();
        try {
            $chave          = $chave;
            $response       = $configuracao->tools->sefazConsultaChave($chave);

            $stdCl  = new Standardize($response);
            $std    = $stdCl->toStd();
            $xJust  = $justificativa;
            $nProt  = $std->protNFe->infProt->nProt;

            $response   = $configuracao->tools->sefazCancela($chave, $xJust, $nProt);
            $stdCl      = new Standardize($response);
            $std        = $stdCl->toStd();


            if ($std->cStat != 128) {
                //erro registrar e voltar
                $retorno->tem_erro  = true;
                $retorno->titulo    = "O lote nem foi processado, houve um problema " ;
                $retorno->erro      = $std->xMotivo;
                return $retorno;
            } else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '101' || $cStat == '135' || $cStat == '155') {

                    //Arquivo Original
                    $path_original   = "notas/". $configuracao->pastaEmpresa."/xml/nfe/". $configuracao->pastaAmbiente."/autorizadas/" ;
                    $xml_original    = file_get_contents($path_original. $chave."-nfe.xml");
                    $xml_cancelado = Complements::cancelRegister($xml_original, $response);


                    $path_cancelado   = "notas/". $configuracao->pastaEmpresa."/xml/nfe/". $configuracao->pastaAmbiente."/autorizadas/" ;
                    if (!file_exists($path_cancelado)){
                        mkdir($path_cancelado, 07777, true);
                    }

                    file_put_contents( $path_cancelado . $chave."-nfe.xml" , $xml_cancelado);
                    chmod($path_cancelado, 07777);
                    //Nota::Create(["cnpj" =>$configuracao->cnpj,"chave"=>$chave,"protocolo"=>$protocolo,"recibo"=>$recibo, "tipo"=>"nfe"]);

                    $retorno->tem_erro  = false;
                    $retorno->titulo    = "Nota cancelada com sucesso";
                    $retorno->erro      = "";                    
                    $retorno->cStat     = $cStat;
                    $retorno->retorno   = $std;
                    $retorno->xml       = $xml_cancelado;
                    return $retorno;
                } else {
                    $retorno->tem_erro  = true;
                    $retorno->titulo    = "01: Não foi Possível Fazer o Cancelamento";
                    $retorno->erro      = $std->retEvento->infEvento->xMotivo ?? " Não foi Possível Fazer o Cancelamento";
                    $retorno->retorno   = $std;
                    return $retorno;
                }
            }
        } catch (\Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "02: Não foi Possível Fazer o Cancelamento";
            $retorno->erro      = $e->getMessage();
            return $retorno;
        }

        return $retorno;
    }

    public static function cartaCorrecao($justificativa, $sequencia, $chave, $configuracao){
        $retorno            = new \stdClass();
        try {
            $nSeqEvento     = $sequencia;

            $response = $configuracao->tools->sefazCCe($chave, $justificativa, $nSeqEvento);
            sleep(1);
            $stdCl = new Standardize($response);

            $std = $stdCl->toStd();
            $arr = $stdCl->toArray();
            sleep(1);
            if ($std->cStat != 128) {
                $retorno->tem_erro  = true;
                $retorno->titulo    = "Erro gerar o Carta de Correção";
                $retorno->erro      = $std->xMotivo;
                $retorno->retorno   = NULL;
                return $retorno;
            }else {
                $cStat = $std->retEvento->infEvento->cStat;
                if ($cStat == '135' || $cStat == '136') {

                    $xml = Complements::toAuthorize($configuracao->tools->lastRequest, $response);

                    $path           = "notas/". $configuracao->pastaEmpresa."/xml/nfe/". $configuracao->pastaAmbiente."/cartacorrecao/";
                    $nome_arquivo   = $chave."-nfe.xml";

                    if (!file_exists($path)){
                        mkdir($path, 07777, true);
                    }

                    file_put_contents($path.$nome_arquivo, $xml);
                    chmod($path, 07777);

                    $retorno->tem_erro  = false;
                    $retorno->titulo    = "Carta de Correção gerada com sucesso";
                    $retorno->erro      = "";
                    $retorno->cStat     = $cStat;
                    $retorno->retorno   = $std;
                    $retorno->xml       = $xml;
                    return $retorno;
                } else {
                    $retorno->tem_erro  = true;
                    $retorno->titulo    = "Erro gerar o Carta de Correção";
                    $retorno->erro      = $std->retEvento->infEvento->xMotivo ?? "Erro";
                    $retorno->retorno   = $std;
                    return $retorno;
                }
            }
        } catch (\Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "Erro gerar o Carta de Correção";
            $retorno->erro      = $e->getMessage();
            $retorno->retorno   = NULL;
            return $retorno;
        }
        
        return $retorno;
    }
    
    public static function cce($tpAmb, $chave, $cnpj, $emitente){
        $pastaAmbiente      = ($tpAmb == "1") ? "producao" : "homologacao";       
        
        $retorno = new \stdClass();
        try {
            $path               = "notas/". $cnpj."/xml/nfe/".$pastaAmbiente."/cartacorrecao/".$chave."-nfe.xml";
            if(!file_exists($path)){
                throw new Exception("Arquivo não encontrado");
            }
            
            $xml                = file_get_contents($path);
            $daevento = new Daevento($xml, objToArray($emitente));
            $daevento->debugMode(true);
            $daevento->creditsIntegratorFooter('mjailton Sistemas - mjailton.com.brr');
            $pdf = $daevento->render();
            $retorno->tem_erro  = false;
            $retorno->titulo    = "Pdf gerado com sucesso";
            $retorno->erro      = "";
            $retorno->pdf       = $pdf;
            return $retorno;
        } catch (Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "Erro gerar o PDF";
            $retorno->erro      = $e->getMessage();
            $retorno->pdf       = NULL;
            return $retorno;
        }

        return $retorno;
    }

    public static function inutilizar($justificativa, $nSerie, $nIni, $nFin, $configuracao){
        $retorno = new \stdClass();
        try {
            $response   = $configuracao->tools->sefazInutiliza($nSerie, $nIni, $nFin, $justificativa);
            $stdCl      = new Standardize($response);
            $std        = $stdCl->toStd();
            $protocolo  = $std->infInut->nProt ?? null;
            if($protocolo){
                $retorno->tem_erro  = false;
                $retorno->titulo    = "Nota Inutilizada com sucesso";
                $retorno->erro      = "";
                $retorno->resultado = $std;
                return $retorno;
            }else{
                $retorno->tem_erro  = true;
                $retorno->titulo    = "Erro ao inutilizar a nota";
                $retorno->erro      = $std->infInut->xMotivo ?? null;
                return $retorno;
            }

        } catch (\Exception $e) {
            $retorno->tem_erro  = true;
            $retorno->titulo    = "Erro ao inutilizar a nota";
            $retorno->erro      = $e->getMessage();
        }
        return $retorno;
    }

    
    

}

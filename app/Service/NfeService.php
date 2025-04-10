<?php

namespace app\Service;

use App\Models\
{
    AutXml,
    CofinsNfe,
    Destinatario,
    Duplicata,
    Emitente,   
    Fatura, 
    IcmsNfe,
    NotaNfe,
    IdeNfe, 
    IpiNfe,
    Pagamento,  
    PisNfe,
    Produto,
    Reboque,
    RespTecnico,
    RetencaoTransporte,
    TotalNfe,
    Transportadora,
    Veiculo,
    Volume  
};
use App\Models\ConfiguracaoNfe;
use Exception; 
use NFePHP\Common\Certificate;
use NFePHP\DA\NFe\Daevento;
use NFePHP\DA\NFe\Danfe;
use NFePHP\NFe\Common\Standardize;
use NFePHP\NFe\Tools;
use NFePHP\NFe\Complements;
use NFePHP\NFe\Make;
use stdClass;
use App\Service\UtilService;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Log;

class NfeService{

    
    public static function gerarNfe($notafiscal, $token_company, $token_emitente)
    {
        $nfe = new Make();
    
        // Configuração inicial da NFe
        $std = new stdClass();
        $std->versao = '4.00'; // Versão do layout
        $std->Id = '';         // ID será gerado automaticamente se vazio
        $std->pk_nItem = null; // Sempre NULL
        $nfe->taginfNFe($std);
    
        // Montagem dos elementos principais da NFe
        IdeNfe::montarXml($nfe, $notafiscal->ide);
        Emitente::montarXml($nfe, $notafiscal->emitente);
        Destinatario::montarXml($nfe, $notafiscal->destinatario);
    
        // Inicialização dos totais
        $totais = (object)[
            'vProd' => 0.0,
            'vICMS' => 0.0,
            'vIPI' => 0.0,
            'vPIS' => 0.0,
            'vCOFINS' => 0.0,
            'vNF' => 0.0,
        ];
    
        $cont = 1;
    
        // Iterar pelos itens
        foreach ($notafiscal->itens as $item) {
            $std = new stdClass();
            $std->item = $cont;
            $std->vTotTrib = null; // Valor total de tributos
            $nfe->tagimposto($std);
    
            // Montar o XML dos itens
            Produto::montarXml($cont, $nfe, $item->produto);
    
            // Acumular valores para os totais
            $totais->vProd += $item->produto->vProd ?? 0.0;
    
            if (isset($item->ipi->CST)) {
                IpiNfe::montarXml($cont, $nfe, $item->ipi);
                $totais->vIPI += $item->ipi->vIPI ?? 0.0;
            }
    
            IcmsNfe::montarXml($cont, $nfe, $item->icms);
            $totais->vICMS += $item->icms->vICMS ?? 0.0;
    
            PisNfe::montarXml($cont, $nfe, $item->pis);
            $totais->vPIS += $item->pis->vPIS ?? 0.0;
    
            CofinsNfe::montarXml($cont, $nfe, $item->cofins);
            $totais->vCOFINS += $item->cofins->vCOFINS ?? 0.0;
    
            $cont++;
        }
    
        // Calcular o valor total da nota
        $totais->vNF = $totais->vProd + $totais->vICMS + $totais->vIPI + $totais->vPIS + $totais->vCOFINS;
    
        // Transporte
        $std = new \stdClass();
        $std->modFrete = $notafiscal->ide->modFrete;
        $nfe->tagtransp($std);
    
        if ($notafiscal->ide->modFrete != "9" && isset($notafiscal->transporte)) {
            $transporte = $notafiscal->transporte;
    
            if ($transporte->transportadora) {
                Transportadora::montarXml($nfe, $transporte->transportadora);
            }
            if ($transporte->retencao) {
                RetencaoTransporte::montarXml($nfe, $transporte->retencao);
            }
            if ($transporte->veiculo) {
                Veiculo::montarXml($nfe, $transporte->veiculo);
            }
            if ($transporte->reboque) {
                Reboque::montarXml($nfe, $transporte->reboque);
            }
            if ($transporte->volume) {
                Volume::montarXml($nfe, $transporte->volume);
            }
        }
    
        // Responsável técnico
        RespTecnico::montarXml($nfe, $notafiscal->infoRespTecnico);
    
        // Autorização XML
        AutXml::montarXml($nfe, $notafiscal->autXML);
    
        // Pagamentos
        $std = new \stdClass();
        $std->vTroco = $notafiscal->ide->vTroco ?? null;
        $nfe->tagpag($std);
    
        foreach ($notafiscal->pagamentos as $pagamento) {
            Pagamento::montarXml($nfe, $pagamento["pagamento"], $pagamento["cartao"]);
        }
    
        // Cobrança e duplicatas
        if (isset($notafiscal->cobranca)) {
            Fatura::montarXml($nfe, $notafiscal->cobranca->fatura);
            foreach ($notafiscal->cobranca->duplicatas as $dup) {
                Duplicata::montarXml($nfe, $dup);
            }
        }
    
        // Atualizar os valores totais no objeto $notafiscal->total
        $notafiscal->total = $totais;
    
        // Montar o XML dos totais
        TotalNfe::montarXml($nfe, $notafiscal->total);
    
        // Retornar a NFe gerada
        return self::gerarXml($nfe, $notafiscal, $token_company, $token_emitente);
    }
    



    

    public static function gerarXml($nfe, $notafiscal, $token_company, $token_emitente)
    {
        
        $pastaAmbiente= $notafiscal->configuracao->pastaAmbiente;
        $retorno = new stdClass;
        try {
            $resultado = $nfe->montaNFe();
            if($resultado){
                $xml    = $nfe->getXML();
                $chave  = $nfe->getChave();

                $path = storage_path("app/{$token_company}/{$token_emitente}/nfe/{$pastaAmbiente}/xml/temporarias/");
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

    public static function assinarXml($xml, $chave, $configuracao, $token_company, $token_emitente, $pastaAmbiente)
    {
        $retorno = new \stdClass();
        try {
            $response = $configuracao->tools->signNFe($xml);

            $path = storage_path("app/{$token_company}/{$token_emitente}/nfe/{$pastaAmbiente}/xml/assinadas/");
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

       return $retorno;
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

    public static function consultarPorRecibo($xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $nfe_id)
{
    $retorno = new \stdClass();
    try {
        // Consulta número do recibo na SEFAZ
        $xmlResp = $configuracao->tools->sefazConsultaRecibo($recibo, $configuracao->tpAmb);

        // Transforma o XML de retorno em um objeto stdClass
        $st = new Standardize();
        $std = $st->toStd($xmlResp);

        // Processa o status retornado pela SEFAZ
        return self::processarStatusRecibo($std, $xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp, $nfe_id);
    } catch (\Exception $e) {
        return self::retornoErro("Erro ao consultar a nota na SEFAZ", $e->getMessage(), "REJEITADO");
    }
}

private static function processarStatusRecibo($std, $xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp, $nfe_id)
{
    if ($std->cStat == '103') {
        return self::retornoErro("Protocolo ainda não disponível", "O lote ainda não foi processado", "EM_PROCESSAMENTO");
    }

    if ($std->cStat == '105') {
        return self::retornoErro("Protocolo sendo processado", "Lote em processamento, tente mais tarde", "EM_PROCESSAMENTO");
    }

    if ($std->cStat == '104') { // Lote processado
        return self::processarResultadoLote($std, $xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp, $nfe_id);
    }

    return self::retornoErro("Nota Rejeitada", "{$std->cStat}: {$std->xMotivo}", "REJEITADO", $std->cStat);
}

private static function processarResultadoLote($std, $xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp, $nfe_id)
{
    if ($std->protNFe->infProt->cStat == '100') { // Autorizado
        return self::salvarXmlAutorizado($std, $xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp, $nfe_id);
    }

    if (in_array($std->protNFe->infProt->cStat, ["110", "301", "302"])) { // Denegada
        return self::salvarXmlDenegado($std, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp);
    }

    // Rejeitada
    return self::retornoErro(
        "Nota Rejeitada",
        "{$std->protNFe->infProt->cStat}: {$std->protNFe->infProt->xMotivo}",
        "REJEITADO",
        $std->protNFe->infProt->cStat
    );
}

private static function salvarXmlAutorizado($std, $xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp, $nfe_id)
{
    $protocolo = $std->protNFe->infProt->nProt;
    $xmlAutorizado = Complements::toAuthorize($xml, $xmlResp);
    
    // Salvar o XML autorizado
    $caminho_nome = self::salvarArquivo($token_company, $token_emitente, $pastaAmbiente, "autorizadas", $chave, $xmlAutorizado);
    
    // Gerar o DANFE (mas não salvar ainda, será feito no cliente se necessário)
    $danfeResult = self::gerarDanfe($xmlAutorizado, $token_company, $chave);
    
    // Salvar dados no banco
    $nota = NotaNfe::create([
        "token_company" => $token_company,
        "token_emitente" => $token_emitente,
        "nfe_id" => $nfe_id,
        "cnpj" => $configuracao->cnpj,
        "chave" => $chave,
        "protocolo" => $protocolo,
        "status" => "AUTORIZADO",
        "recibo" => $recibo,
        "caminho" => $caminho_nome['caminho'],
        "nomeArquivo" => $caminho_nome['nomeArquivo'],
        "dhEmi" => $std->protNFe->infProt->dhRecbto,
        "dhSaiEnt" => $std->protNFe->infProt->dhRecbto
    ]);
    
    // Retornar no formato esperado pelo cliente
    return [
        "tem_erro" => false,
        "mensagem" => "XML transmitido com sucesso.",
        "protocolo" => [
            "tem_erro" => false,
            "titulo" => "XML autorizado com sucesso",
            "erro" => "",
            "recibo" => $recibo,
            "nfe_id" => $nfe_id,
            "chave" => $chave,
            "status" => "AUTORIZADO",
            "protocolo" => $protocolo,
            "xml" => $xmlResp,
            "nota_id" => $nota->id,
            "danfe" => $danfeResult->success ? $danfeResult->pdf_base64 : null
        ]
    ];
}

private static function gerarDanfe($xmlAutorizado, $token_company, $chave, $download = false)
{
    try {
        // Configurar o DANFE
        $danfe = new \NFePHP\DA\NFe\Danfe($xmlAutorizado);
        $danfe->debugMode(false);
        $danfe->setDefaultFont('helvetica');
        $danfe->setOcultarUnidadeTributavel(false);
        
        // Configurações opcionais (logo)
        $logoPath = storage_path("app/{$token_company}/logo.jpg");
        if (file_exists($logoPath)) {
            $danfe->setLogo($logoPath);
        }
        
        // Gerar PDF
        $pdfContent = $danfe->render();
        
        // Se for para download direto
        if ($download) {
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="DANFE_' . $chave . '.pdf"');
            header('Content-Length: ' . strlen($pdfContent));
            echo $pdfContent;
            exit;
        }
        
        return (object)[
            'success' => true,
            'pdf_content' => $pdfContent,
            'pdf_base64' => base64_encode($pdfContent),
            'chave' => $chave
        ];
        
    } catch (\Exception $e) {
         Log::error('Erro ao gerar DANFE', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        return (object)[
            'success' => false,
            'error' => $e->getMessage()
        ];
    }
}

// Método para download do DANFE separadamente
public static function downloadDanfe($chave, $token_company)
{
    try {
        // Buscar a nota no banco de dados
        $nota = NotaNfe::where('chave', $chave)
                      ->where('token_company', $token_company)
                      ->firstOrFail();
        
        // Buscar o XML autorizado
        $xmlPath = storage_path('app') . $nota->caminho . '/' . $nota->nomeArquivo;
        $xmlAutorizado = file_get_contents($xmlPath);
        
        // Gerar e fazer download do DANFE
        self::gerarDanfe($xmlAutorizado, $token_company, $chave, true);
        
    } catch (\Exception $e) {
        \Log::error('Erro ao baixar DANFE', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        
        if (request()->wantsJson()) {
            return response()->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 500);
        }
        
        abort(500, 'Erro ao gerar DANFE: ' . $e->getMessage());
    }
}



private static function salvarXmlDenegado($std, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $xmlResp)
{
    self::salvarArquivo($token_company, $token_emitente, $pastaAmbiente, "denegadas", $chave, $xmlResp);

    NotaNfe::create([
        "cnpj" => $configuracao->cnpj,
        "chave" => $chave,
        "recibo" => $recibo,
    ]);

    return self::retornoErro(
        "Nota Denegada",
        "{$std->protNFe->infProt->cStat}: {$std->protNFe->infProt->xMotivo}",
        "DENEGADA",
        $std->protNFe->infProt->cStat,
        $xmlResp
    );
}

private static function salvarArquivo($token_company, $token_emitente, $pastaAmbiente, $tipo, $chave, $conteudo)
{
    $path = storage_path("app/{$token_company}/{$token_emitente}/nfe/{$pastaAmbiente}/xml/{$tipo}/");
    $nomeArquivo = "{$chave}-nfe.xml";

    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }

    file_put_contents($path . $nomeArquivo, $conteudo);
    chmod($path, 0777);

     return [
        'caminho' => $path, 
        'nomeArquivo' => $nomeArquivo
    ];
}

private static function retornoErro($titulo, $erro, $status, $cstat = null, $xml = null)
{
    return (object)[
        "tem_erro" => true,
        "titulo" => $titulo,
        "erro" => $erro,
        "status" => $status,
        "cstat" => $cstat,
        "xml" => $xml,
    ];
}

   
 

    public static function cancelarNfe($justificativa, $nfe, $configuracao)
{
    $retorno = new \stdClass();

    try {
        // Validar existência do protocolo
        if (empty($nfe->protocolo)) {
            throw new \Exception("Protocolo de autorização não encontrado para a nota fiscal.");
        }

        // Cancelar a NF-e
        $xJust = $justificativa;
        $response = $configuracao->tools->sefazCancela($nfe->chave, $xJust, $nfe->protocolo);
        $stdCl = new Standardize($response);
        $std = $stdCl->toStd();

        // Verificar status do cancelamento
        if ($std->cStat != 128) {
            $retorno->tem_erro = true;
            $retorno->titulo = "Erro no cancelamento";
            $retorno->erro = $std->xMotivo ?? "Erro desconhecido no cancelamento.";
            return $retorno;
        }

        // Verificar status do evento
        $cStat = $std->retEvento->infEvento->cStat;
        if (in_array($cStat, ['101', '135', '155'])) {
            // Caminho do XML original
            $path_original = storage_path("app/{$configuracao->pastaEmpresa}/xml/nfe/{$configuracao->pastaAmbiente}/autorizadas/");
            $xml_original = @file_get_contents($path_original . $nfe->chave . "-nfe.xml");

            if (!$xml_original) {
                throw new \Exception("XML original não encontrado no caminho especificado.");
            }

            // Gerar o XML de cancelamento
            $xml_cancelado = Complements::cancelRegister($xml_original, $response);

            // Caminho para salvar o XML cancelado
            $path_cancelado = storage_path("app/{$configuracao->pastaEmpresa}/xml/nfe/{$configuracao->pastaAmbiente}/canceladas/");
            self::verificarCriarDiretorio($path_cancelado);

            file_put_contents($path_cancelado . $nfe->chave . "-nfe.xml", $xml_cancelado);
            chmod($path_cancelado . $nfe->chave . "-nfe.xml", 0777);

            // Retornar sucesso
            $retorno->tem_erro = false;
            $retorno->titulo = "Nota cancelada com sucesso";
            $retorno->erro = "";
            $retorno->cStat = $cStat;
            $retorno->retorno = $std;
            $retorno->xml = $xml_cancelado;

            return $retorno;
        } else {
            // Cancelamento não autorizado
            $retorno->tem_erro = true;
            $retorno->titulo = "Erro no cancelamento";
            $retorno->erro = $std->retEvento->infEvento->xMotivo ?? "Cancelamento não autorizado.";
            $retorno->retorno = $std;
            return $retorno;
        }
    } catch (\Exception $e) {
        // Erro no processo
        $retorno->tem_erro = true;
        $retorno->titulo = "Erro ao processar cancelamento";
        $retorno->erro = $e->getMessage();
        return $retorno;
    }
}

/**
 * Verifica e cria o diretório, se necessário.
 */
private static function verificarCriarDiretorio($path)
{
    if (!file_exists($path)) {
        mkdir($path, 0777, true);
    }
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

    public static function configuracaoNfe($request, $certificado)
{ 
    // Acessando os dados diretamente do array e garantindo que os valores sejam os tipos esperados
    $tpAmb = (int) $request['tpAmb']; // Garantir que tpAmb é um inteiro
    $CNPJ = $request['CNPJ'];
    $xNome = $request['xNome'];
    $UF = UtilService::getUf(intval($request['cUF']));
   
    $mod = isset($request['mod']) ? $request['mod'] : '55';  // Padrão para NFe

    // Configuração do array com os dados recebidos
    $arr = [ 
        "atualizacao" => date('Y-m-d h:i:s'),
        "tpAmb"       => $tpAmb,
        "razaosocial" => $xNome,
        "cnpj"        => $CNPJ,
        "siglaUF"     => $UF,
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

    // Instanciando a configuração
    $objeto = new ConfiguracaoNfe();
    $configJson = json_encode($arr);

    // Certificado digital
    $certificado_digital = $certificado->binario;
    $senha = $certificado->dados['senha'];

    // Populando o objeto Configuração
    $objeto->cnpj = $CNPJ;
    $objeto->tpAmb = $tpAmb;

    // Ferramentas para a configuração
    $objeto->tools = new Tools($configJson, Certificate::readPfx($certificado_digital, $senha));

    // Pasta de ambiente (produção ou homologação)
    $objeto->pastaAmbiente = ($tpAmb == 1) ? "producao" : "homologacao";

    // Caminho da empresa para os arquivos NFe
    $objeto->pastaEmpresa = storage_path("app/{$request['token_company']}/{$request['token_emitente']}/nfe/");

    // Definindo o modelo do XML
    $objeto->tools->model($mod);

    // Retornar o objeto de configuração
    return $objeto;
}


   

}

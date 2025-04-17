<?php

namespace App\Http\Controllers\Nfe;

use App\Http\Controllers\Controller; 
use App\Models\NotaNfe;
use App\Repository\NfeRepository;
use App\Service\CertificadoDigitalService;
use app\Service\NfeService;
use App\Service\ValidaDadosNfeService; 
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request; 
use Response;

class NFeController extends Controller
{

    protected $nfeRepository;

    protected $nfeService;

    public function __construct(
        NfeRepository $nfeRepository,
        NfeService $nfeService
        )
    {
        $this->nfeRepository = $nfeRepository;
        $this->nfeService = $nfeService;
    }

    public function transmitir(Request $request): JsonResponse
{
    //return response()->json(['data' => $request->all()]);
    $retorno = new \stdClass();
    $retorno->tem_erro = false;

    try {
         
        $dados = $request->all();
        $token_company = $request->token_company;
        $token_emitente = $request->token_emitente;
        $nfe_id = $request->nfe_id;
        //return response()->json(['data' => $dados]);

        // Remover os dados específicos do array $dados
        unset($dados['token_company']);
        unset($dados['token_emitente']);
        unset($dados['nfe_id']);
        
        
        $dados_validos = ValidaDadosNfeService::validaDadosNfe($dados, $token_company, $token_emitente);
       // dd($dados_validos);
        if ($dados_validos->tem_erro) {
            throw new \Exception("Erro na validação dos dados: " . $dados_validos->erro);
        }
        
        $configuracao = $dados_validos->notafiscal->configuracao;
        $pastaAmbiente = $configuracao->pastaAmbiente;

        // Etapa 1: Gerar o XML da NF-e
        $xml = NfeService::gerarNfe($dados_validos->notafiscal, $token_company, $token_emitente);
        if ($xml->tem_erro) {
            throw new \Exception("Erro ao gerar XML: " . $xml->erro);
        }

        // Etapa 2: Assinar o XML gerado
        $xml_assinado = NfeService::assinarXml($xml->xml, $xml->chave, $configuracao, $token_company, $token_emitente, $pastaAmbiente);
        if ($xml_assinado->tem_erro) {
            throw new \Exception("Erro ao assinar XML: " . $xml_assinado->erro);
        }

        // Etapa 3: Enviar o XML assinado para a SEFAZ
        $envio = NfeService::enviarXML($xml_assinado->xml, $xml->chave, $configuracao, $dados_validos->notafiscal->ide->nNF);
        if ($envio->tem_erro) {
            throw new \Exception("Erro ao enviar XML: " . $envio->erro);
        }

        // Etapa 4: Consultar o recibo do envio até obter um resultado final
        $protocolo = $this->consultarReciboComRetry($envio->recibo, $xml_assinado->xml, $xml->chave, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $nfe_id);

        if ($protocolo->tem_erro) {
            throw new \Exception("Erro ao consultar recibo: " . $protocolo->erro);
        }
        
        // Sucesso: Retornar o protocolo final
        $retorno->mensagem = "XML transmitido com sucesso.";
        $retorno->protocolo = $protocolo;
        return response()->json(['data' => $retorno]);
    } catch (\Exception $e) {
        $retorno->tem_erro = true;
        $retorno->erro = $e->getMessage();
        return response()->json(['data' => $retorno]);
    }
}

/**
 * Consulta o recibo da NF-e com tentativas de repetição (retry).
 */
private function consultarReciboComRetry($recibo, $xml, $chave, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $nfe_id)
{
    $maxTentativas = 3;
    $intervalo = 3; // Segundos
    $tentativas = 0;

    do {
        sleep($intervalo);
        $tentativas++;

        $protocolo = NfeService::consultarPorRecibo($xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente, $nfe_id);

        if ($protocolo->status !== "Em processamento") {
            return $protocolo; // Resultado final encontrado
        }
    } while ($tentativas < $maxTentativas);

    // Se ainda estiver "Em processamento" após todas as tentativas
    $protocolo->tem_erro = true;
    $protocolo->erro = "Recibo não processado após {$maxTentativas} tentativas.";
    return $protocolo;
}

public function getNfesForCompany(Request $request): JsonResponse
{  
    $token_company = $request->token_company;
    $token_emitente = $request->token_emitente;
    $nfe = $this->nfeRepository->getNfesForCompany($token_company, $token_emitente);

    if (!$nfe) {
        return response()->json(['error' => 'Nenhuma NFe encontrada.'], 404);
    }
   
    return response()->json(['data' => $nfe]);
}
 
public function cancelarNfe(Request $request)
{
     
    $id = $request->id;

    $token_company = $request->token_company;
    $token_emitente = $request->token_emitente;
    
    $certificado = CertificadoDigitalService::lerCertificadoDigital($token_company);
    $config = $this->nfeService->configuracaoNfe($request->all(), $certificado);
    $nfe = $this->nfeRepository->getNfeId($id, $token_company, $token_emitente);
    //dd($nfe);
    $nfeService = $this->nfeService->cancelarNfe($request->justificativa, $nfe, $config);
    return Response::json($nfeService);
}

public function obterDanfe(Request $request, $chave)
{
    try {
        // Recuperar a nota fiscal pelo chave
        $nota = NotaNfe ::where('chave', $chave)->firstOrFail();
        
        // Carregar XML autorizado
        $xmlPath = $nota->caminho . $nota->nomeArquivo;
        $xmlContent = file_get_contents($xmlPath);
        
        // Gerar DANFE dinamicamente
        $danfe = $this->gerarDanfe($xmlContent, $nota->token_company, $chave);
        
        if (!$danfe->success) {
            throw new \Exception("Falha ao gerar DANFE: " . $danfe->error);
        }
        
        // Retornar o PDF diretamente
        return response($danfe->pdf_content, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="DANFE_' . $chave . '.pdf"'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'error' => 'Erro ao gerar DANFE',
            'message' => $e->getMessage()
        ], 400);
    }
}


}


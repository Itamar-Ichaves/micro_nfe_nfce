<?php

namespace App\Http\Controllers\Nfe;

use App\Http\Controllers\Controller;
use App\Repository\NfeRepository;
use app\Service\NfeService;
use App\Service\ValidaDadosNfeService;
use Faker\Provider\ar_EG\Company;
use Illuminate\Http\Request;
use NFePHP\DA\NFe\Danfe;

class NFeController extends Controller
{

    protected $nfeRepository;

    // Injeção de dependência do NfeRepository
    public function __construct(NfeRepository $nfeRepository)
    {
        $this->nfeRepository = $nfeRepository;
    }


    public function transmitir(Request $request)
{
    
    $retorno = new \stdClass();
    $retorno->tem_erro = false;

    try {
        // Receber e validar os dados iniciais
        $dados = $request->all();
        $token_company = $request->token_company;
        $token_emitente = $request->token_emitente;

        $dados_validos = ValidaDadosNfeService::validaDadosNfe($dados, $token_company, $token_emitente);
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
        $protocolo = $this->consultarReciboComRetry($envio->recibo, $xml_assinado->xml, $xml->chave, $configuracao, $token_company, $token_emitente, $pastaAmbiente);

        if ($protocolo->tem_erro) {
            throw new \Exception("Erro ao consultar recibo: " . $protocolo->erro);
        }

        // Sucesso: Retornar o protocolo final
        $retorno->mensagem = "XML transmitido com sucesso.";
        $retorno->protocolo = $protocolo;
        return $retorno;
    } catch (\Exception $e) {
        $retorno->tem_erro = true;
        $retorno->erro = $e->getMessage();
        return $retorno;
    }
}

/**
 * Consulta o recibo da NF-e com tentativas de repetição (retry).
 */
private function consultarReciboComRetry($recibo, $xml, $chave, $configuracao, $token_company, $token_emitente, $pastaAmbiente)
{
    $maxTentativas = 3;
    $intervalo = 3; // Segundos
    $tentativas = 0;

    do {
        sleep($intervalo);
        $tentativas++;

        $protocolo = NfeService::consultarPorRecibo($xml, $chave, $recibo, $configuracao, $token_company, $token_emitente, $pastaAmbiente);

        if ($protocolo->status !== "Em processamento") {
            return $protocolo; // Resultado final encontrado
        }
    } while ($tentativas < $maxTentativas);

    // Se ainda estiver "Em processamento" após todas as tentativas
    $protocolo->tem_erro = true;
    $protocolo->erro = "Recibo não processado após {$maxTentativas} tentativas.";
    return $protocolo;
}

public function danfe(Request $request)
    {
        // Recuperando os parâmetros da requisição
        $id = $request->input('id');
        $token_company = $request->input('token_company');
        $token_emitente = $request->input('token_emitente');
        $pastaAmbiente = $request->input('pastaAmbiente'); // Ambientes: produção, homologação, etc.
        $tipo = $request->input('tipo'); // Tipo do arquivo: 'entrada' ou 'saida', por exemplo

        // Consultando a NFe usando o repositório
        $nota = $this->nfeRepository->getNfe($id, $token_company, $token_emitente);
         
        // Verificar se a nota existe e contém a chave
        if (!$nota || !$nota->chave) {
            return response()->json(['error' => 'Nota fiscal não encontrada ou chave ausente.'], 404);
        }

        try {
            // Definir o caminho do XML baseado na chave da NFe
            $chave = $nota->chave;
            $path = $nota->caminho;
            $nomeArquivo = "{$chave}-nfe.xml";

            // Verificar se o arquivo XML existe
            if (!file_exists($path . $nomeArquivo)) {
                return response()->json(['error' => 'Arquivo XML não encontrado no caminho especificado.'], 404);
            }

            // Ler o conteúdo do XML
            $xml = file_get_contents($path . $nomeArquivo);

            // Inicialize o objeto Danfe com o XML
            $danfe = new Danfe($xml);

            // Gerar o PDF
            $pdf = $danfe->render();

            // Retornar o PDF como resposta
            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="danfe.pdf"');
        } catch (\Exception $e) {
            // Retornar erro em caso de falha
            return response()->json(['error' => 'Erro ao gerar o DANFE: ' . $e->getMessage()], 500);
        }
    }

}




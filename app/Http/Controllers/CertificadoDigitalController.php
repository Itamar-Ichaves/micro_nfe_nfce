<?php

namespace App\Http\Controllers;

use App\Service\CertificadoDigitalService;
use Exception;
use Illuminate\Http\Request;
use NFePHP\Common\Certificate;
use stdClass;

class CertificadoDigitalController extends Controller
{
    protected $certificadoDigital;

    public function __construct(CertificadoDigitalService $certificadoDigital)
    {
        $this->certificadoDigital = $certificadoDigital;
    }

    
    
    public function salvarCertificado(Request $request)
{
    $retorno = new \stdClass();
    $retorno->tem_erro = false;
    $retorno->erro = "";

    // Validação para garantir que o arquivo foi enviado corretamente
    if ($request->hasFile('arquivo_binario') && $request->arquivo_binario->isValid()) {
        $arquivo = $request->file('arquivo_binario');
    } else {
        $retorno->tem_erro = true;
        $retorno->erro = "Arquivo binário não enviado ou inválido.";
        return response()->json($retorno, 400, [], JSON_UNESCAPED_UNICODE);
    }

    // Criando objeto de certificado
    $certificado = new \stdClass();
    $certificado->cnpj = $request->cnpj;
    $certificado->senha = $request->senha;
    $certificado->csc = $request->csc ?? null;
    $certificado->csc_id = $request->csc_id ?? null;

    // Ler o conteúdo do arquivo antes de salvá-lo
    $conteudoArquivo = file_get_contents($arquivo->getRealPath());
    if ($conteudoArquivo === false) {
        $retorno->tem_erro = true;
        $retorno->erro = "Erro ao ler o arquivo do certificado.";
        return response()->json($retorno, 400, [], JSON_UNESCAPED_UNICODE);
    }

   
    // Gerar o caminho do diretório baseado no token_company
    $token_company = $request->token_company;
    $token_emitente = $request->token_emitente;
    $pasta = storage_path("app/{$token_company}/{$token_emitente}/certificados");

    // Verifica se a pasta existe, caso contrário, cria
    if (!file_exists($pasta)) {
        mkdir($pasta, 0777, true);
    }

     // Tentar ler e validar o certificado
     $dados_certificado = $this->certificadoDigital->lerValidarCertificadoDigital($conteudoArquivo, $request->senha);
     if ($dados_certificado->tem_erro) {
         $retorno->tem_erro = true;
         $retorno->erro = $dados_certificado->erro;
         return response()->json($retorno, 400, [], JSON_UNESCAPED_UNICODE);
     }
 

    // Definir o nome do arquivo a ser salvo
    $nome_arquivo = $arquivo->getClientOriginalName();
    $caminho_arquivo = "{$pasta}/{$nome_arquivo}";

    // Verifica se já existe um certificado na pasta
    $arquivos_existentes = glob("{$pasta}/*.pfx");
    foreach ($arquivos_existentes as $arquivo_existente) {
        if ($arquivo_existente !== $caminho_arquivo) {
            unlink($arquivo_existente); // Remove o arquivo existente
        }
    }

    // Salva o novo arquivo na pasta específica
    $arquivo->move($pasta, $nome_arquivo);

    // Atualizar objeto certificado com os dados do certificado lido
    $certificado->inicio = $dados_certificado->dados['inicio'] ?? null;
    $certificado->expiracao = $dados_certificado->dados['expiracao'] ?? null;
    $certificado->serial = $dados_certificado->dados['serial'] ?? null;
    $certificado->id = $dados_certificado->dados['id'] ?? null;
    $certificado->arquivo_binario = $caminho_arquivo;
    $certificado->caminho = $caminho_arquivo;

    // Salvar no banco de dados via serviço
    try {
        $this->certificadoDigital->salvarCertificadoService($dados_certificado->dados, $certificado, $token_company, $token_emitente);
    } catch (\Exception $e) {
        $retorno->tem_erro = true;
        $retorno->erro = "Erro ao salvar os dados no banco de dados: " . $e->getMessage();
        return response()->json($retorno, 500, [], JSON_UNESCAPED_UNICODE);
    }

    // Resposta de sucesso com os dados do certificado
    $retorno->tem_erro = false;
    $retorno->erro = "";
    $retorno->retorno = [
        "mensagem" => "Certificado salvo com sucesso.",
        "certificado" => $certificado,
    ];
    return response()->json($retorno, 200, [], JSON_UNESCAPED_UNICODE);
}



    
    
public function consultarCertificado(Request $request)
{ 
    $token_company = $request->token_company;
    $token_emitente = $request->token_emitente;

    $cert = $this->certificadoDigital->consultarCertificado($token_company, $token_emitente);

    return response()->json(['data' => $cert]);
}

    
    
}    
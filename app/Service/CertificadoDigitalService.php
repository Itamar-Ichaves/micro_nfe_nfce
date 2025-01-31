<?php

namespace App\Service;

use App\Models\Certificado_digital;
use App\Repository\CertificadoDigitalRepository;
use Exception;
use NFePHP\Common\Certificate;
use NFePHP\Common\Exception\CertificateException;
use stdClass;
use Symfony\Component\HttpFoundation\AcceptHeader;

class CertificadoDigitalService
{
    protected $certificadoDigital;

    public function __construct(CertificadoDigitalRepository $certificadoDigitalRepository)
    {
        $this->certificadoDigital = $certificadoDigitalRepository;
    }

    public function salvarCertificadoService($dados_certificado, $certificado, $token_company, $token_emitente)
    {
        try {
            // Verifica se o certificado já existe para a empresa especificada
            $certificate = $this->consultarCertificado($token_company);
    
            if ($certificate->isEmpty()) {
                // Certificado não encontrado, realiza a inserção
                return $this->certificadoDigital->createCertificado($dados_certificado, $certificado, $token_company, $token_emitente);
            } else {
                // Certificado encontrado, realiza a atualização
                return $this->certificadoDigital->updateCertificado($dados_certificado, $certificado, $token_company, $token_emitente);
            }
        } catch (\Exception $e) {
            // Tratamento de erro caso algo dê errado na operação
            throw new \Exception("Erro ao salvar o certificado: " . $e->getMessage());
        }
    }
    
    
    


    public function consultarCertificado($token_company )
    {
       // dd($token_company);
        return $this->certificadoDigital->getCertificado( $token_company);
    }

    

    public function VerificaCertificado ()
    {
       
    }

    /**
     * Função para ler as informações do certificado PFX
     */
    public function lerValidarCertificadoDigital($conteudo, $senha)
    {
        $retorno = new \stdClass();
        $retorno->tem_erro = false;
        $retorno->erro = "";
        $retorno->dados = null;
    
        try {
            // Verifica se o conteúdo é um caminho de arquivo e lê o conteúdo
            if (file_exists($conteudo)) {
                $conteudo = file_get_contents($conteudo);
                if ($conteudo === false) {
                    throw new \Exception("Erro ao ler o arquivo do certificado.");
                }
            }
    
            // Verifica se o conteúdo está disponível
            if (empty($conteudo)) {
                throw new \Exception("O conteúdo do certificado é inválido ou vazio.");
            }
    
            // Decodifica o arquivo PFX
            $certificados = [];
            if (!openssl_pkcs12_read($conteudo, $certificados, $senha)) {
                throw new \Exception("Falha ao decodificar o certificado. Verifique se a senha está correta.");
            }
    
            // Extração de informações do certificado
            $certInfo = openssl_x509_parse($certificados['cert']);
            if (!$certInfo) {
                throw new \Exception("Erro ao extrair informações do certificado.");
            }
    
            // Formatação dos dados extraídos
            $retorno->dados = [
                'inicio' => date('d/m/Y H:i:s', $certInfo['validFrom_time_t']),
                'expiracao' => date('d/m/Y H:i:s', $certInfo['validTo_time_t']),
                'serial' => $certInfo['serialNumberHex'],
                'id' => $certInfo['subject']['CN'],
            ];
        } catch (\Exception $e) {
            $retorno->tem_erro = true;
            $retorno->erro = $e->getMessage();
        }
    
        return $retorno; 
    }
    

public static function lerCertificadoDigital($token_company)
{
    // Inicializando corretamente o objeto $retorno
    $retorno = new \stdClass();
    $retorno->tem_erro = false;
    $retorno->erro = "";
    $retorno->dados = null;
    $retorno->binario = null; 

    // Consulta o certificado no banco
    $conteudo = Certificado_digital::where('token_company', $token_company)->first();

    if (!$conteudo) {
         
        $retorno->tem_erro = true;
        $retorno->erro = "Certificado não encontrado para a empresa.";
        return $retorno;
    }

    // Verifica se o arquivo existe
    if (!file_exists($conteudo->arquivo_caminho)) {
        $retorno->tem_erro = true;
        $retorno->erro = "Arquivo do certificado não encontrado no caminho especificado.";
        return $retorno;
    }

    // Ler o conteúdo do arquivo PFX (binário)
    $arquivoConteudo = file_get_contents($conteudo->arquivo_caminho);

    if ($arquivoConteudo === false) {
        $retorno->tem_erro = true;
        $retorno->erro = "Erro ao ler o arquivo do certificado.";
        return $retorno;
    }

    // Adiciona o conteúdo binário  
    $retorno->binario = $arquivoConteudo;

    // Decodificar o arquivo PFX
    $certificados = [];
    $resultado = openssl_pkcs12_read($arquivoConteudo, $certificados, $conteudo->senha);

    if (!$resultado) {
        $retorno->tem_erro = true;
        $retorno->erro = "Falha ao decodificar o certificado. Verifique se a senha está correta.";
        return $retorno;
    }

    // Extraindo informações  
    try {
        $certInfo = openssl_x509_parse($certificados['cert']);
        if (!$certInfo) {
            throw new \Exception("Erro ao extrair informações do certificado.");
        }
         
        $retorno->dados = [
        
            'inicio' => date('Y-m-d H:i:s', $certInfo['validFrom_time_t']),
            'expiracao' => date('Y-m-d H:i:s', $certInfo['validTo_time_t']),
            'serial' => $certInfo['serialNumberHex'],
            'id' => $certInfo['subject']['CN'],
            'senha' => $conteudo->senha
        ];
    } catch (\Exception $e) {
        $retorno->tem_erro = true;
        $retorno->erro = "Erro ao processar os dados do certificado: " . $e->getMessage();
    }

    return $retorno;
}




}

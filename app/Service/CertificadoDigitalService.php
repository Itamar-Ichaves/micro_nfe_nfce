<?php

namespace App\Service;

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

    public function salvarCertificado($data,  $certificado)
    {
        //dd($certificado);
        $certificado = $this->consultarCertificado( $data->token_emitente, $data->token_company);
       
        $dataArray = (array) $data;
        //$dataArray['arquivo_binario'] = base64_decode($data->arquivo_binario); // Convertendo de base64 para binário

        if (!$certificado) {
            $this->certificadoDigital->createCertificado($dataArray,  $dataArray['arquivo_binario']);
        } else {
            $this->certificadoDigital->updateCertificado($dataArray,  $dataArray['arquivo_binario']);
        }
    }

    public function consultarCertificado($token_company, $token_emitente)
    {
        return $this->certificadoDigital->getCertificado( $token_company, $token_emitente);
    }

    public static function lerCertificadoDigital($arquivo, $senha)
    {
        $retorno = new stdClass();
        
        try {
            // Certifique-se de que $arquivo é o caminho do arquivo PFX
            $detalhe = Certificate::readPfx($arquivo, $senha);

            $cert = new stdClass();
            $cert->inicio = $detalhe->publicKey->validFrom->format('d/m/y H:i:s');
            $cert->expiracao = $detalhe->publicKey->validTo->format('d/m/y H:i:s');
            $cert->serial = $detalhe->publicKey->serialNumber;
            $cert->id = $detalhe->publicKey->commonName;

            $retorno->tem_erro = false;
            $retorno->titulo = "Certificado Digital";
            $retorno->erro = "";
            $retorno->retorno = $cert;
        } catch (Exception $e) {
            $retorno->tem_erro = true;
            $retorno->titulo = "Erro ao ler Certificado";
            $retorno->erro = $e->getMessage();
        }
       
        return $retorno;
    }
}

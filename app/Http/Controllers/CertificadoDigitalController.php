<?php

namespace App\Http\Controllers;

use App\Models\Certificado_digital;
use App\Service\CertificadoDigitalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
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
        $retorno = new stdClass;
    
        // Validação dos campos
        $validator = Validator::make($request->all(), [
            'cnpj' => 'required|string|max:18',
            'senha' => 'required|string|max:255',
            'token_company' => 'required|string|max:255',
            'token_emitente' => 'required|string|max:255',
           
            //'arquivo' => 'required|file|mimes:pfx',
        ]);
    
        if ($validator->fails()) {
            $retorno->tem_erro = true;
            $retorno->erro = $validator->errors();
            return response()->json($retorno, 400, [], JSON_UNESCAPED_UNICODE);
        }
    
        try {
            $certificado = new stdClass();
            $certificado->cnpj = $request->cnpj;
            $certificado->senha = $request->senha;
            $certificado->token_company = $request->token_company;
            $certificado->token_emitente = $request->token_emitente;
            $certificado->certificado_nome_arquivo = $request->certificado_nome_arquivo;
            $certificado->arquivo_binario = $request->arquivo_binario;
    
            if ($request->hasFile('arquivo') && $request->file('arquivo')->isValid()) {
                $certificado->arquivo_binario = file_get_contents($request->file('arquivo')->getPathname());
                
                $resultado = CertificadoDigitalService::lerCertificadoDigital($certificado->arquivo_binario, $certificado->senha);
                    
                if (!$resultado->tem_erro) {
                    $certificado->inicio = $resultado->retorno->inicio ?? null;
                    $certificado->expericao = $resultado->retorno->expiracao ?? null;
                    $certificado->serial = $resultado->retorno->serial ?? null;
                    $certificado->identificado = $resultado->retorno->id ?? null;
                } else {
                    throw new \Exception($resultado->erro);
                }
            }
            
            $this->certificadoDigital->salvarCertificado($certificado);
            
            $retorno->tem_erro = false;
            $retorno->erro = "";
            $retorno->retorno = "ok";
            return response()->json($retorno, 200, [], JSON_UNESCAPED_UNICODE);
        } catch (\Throwable $th) {
            $retorno->tem_erro = true;
            $retorno->erro = $th->getMessage();
            return response()->json($retorno, 500, [], JSON_UNESCAPED_UNICODE);
        }
    }

    
}    
<?php

namespace App\Repository;

use App\Models\Certificado_digital;
use Illuminate\Support\Facades\DB;

class CertificadoDigitalRepository
{ 
    protected $table;
    protected $entity;

    public function __construct(Certificado_digital $certificado_digital)
    {
        $this->table = 'certificado_digitals'; 
        $this->entity = $certificado_digital;
    }

    public function getCertificado($token_company)
    {
        return DB::table($this->table)
            ->where('token_company', $token_company)
           // ->where('token_emitente', $token_emitente)
            ->get();
    }

    public function updateCertificado($dados_certificado, $certificado, $token_company, $token_emitente)
{
     //dd($dados_certificado, $certificado, $token_company, $token_emitente);

     // Converte as datas para o formato MySQL
    $inicioValidade = date('Y-m-d H:i:s', strtotime($dados_certificado['inicio']));
    $fimValidade = date('Y-m-d H:i:s', strtotime($dados_certificado['expiracao']));


    return DB::table($this->table)
        ->where('token_company', $token_company)
        ->where('token_emitente', $token_emitente)
        ->update([
            'senha' => $certificado->senha,
        'cnpj' => $certificado->cnpj,
        'arquivo_caminho' => $certificado->caminho,
        'inicio_validade' => $inicioValidade,
        'fim_validade' => $fimValidade,
        'serial' => $dados_certificado['serial'],
        'id_certificado' => $dados_certificado['id']
        ]);
}


public function createCertificado($dados_certificado, $certificado, $token_company, $token_emitente)
{
    // Converte as datas para o formato MySQL
    $inicioValidade = date('Y-m-d H:i:s', strtotime($dados_certificado['inicio']));
    $fimValidade = date('Y-m-d H:i:s', strtotime($dados_certificado['expiracao']));

    // Registra os dados no banco de dados
    return DB::table($this->table)->insert([
        'token_company' => $token_company,
        'token_emitente' => $token_emitente,
        'senha' => $certificado->senha,
        'cnpj' => $certificado->cnpj,
        'arquivo_caminho' => $certificado->caminho,
        'inicio_validade' => $inicioValidade,
        'fim_validade' => $fimValidade,
        'serial' => $dados_certificado['serial'],
        'id_certificado' => $dados_certificado['id']
    ]);
}


    public function deleteCertificado($id)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->delete();
    }
}

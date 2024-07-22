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

    public function getCertificado($token_company, $token_emitente)
    {
        return DB::table($this->table)
            ->where('token_company', $token_company)
            ->where('token_emitente', $token_emitente)
            ->first();
    }

    public function updateCertificado(array $data)
    {
        return DB::table($this->table)
            ->where('token_company', $data['token_company'])
            ->where('token_emitente', $data['token_emitente'])
            ->update($data);      
    }

    public function createCertificado( $dados , $certificado)
    {
    
        return DB::table($this->table)->insert([
            'token_company' => $dados['token_company'],
            'token_emitente' => $dados['token_emitente'],
            'arquivo_binario' => ($dados['arquivo_binario']),
            'certificado_nome_arquivo' => $dados['certificado_nome_arquivo'],
            'senha' => $dados['senha'],
            'cnpj' => $dados['cnpj'],
            'inicio'=> $dados['inicio'],
            'expiracao' => $dados['expiracao'],
            'serial'=> $dados['serial'],
            'identificado'=> $dados['identificado'],
          
            
        ]);
    }
    public function deleteCertificado($id)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->delete();
    }
}

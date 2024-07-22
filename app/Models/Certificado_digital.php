<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Str;

class Certificado_digital extends Model
{
    use HasFactory;

    protected $table = 'certificado_digitals';

    // Desabilita auto-increment para a chave primária
    public $incrementing = false;

    // Define o tipo da chave primária como string
    protected $keyType = 'string';

    // Campos que podem ser preenchidos em massa
    protected $fillable = [
        'id',
        'token_company',
        'token_emitente',
        'certificado_nome_arquivo', 
        'arquivo_binario',
        'senha',
        'cnpj', 
        'serial',
        'inicio',
        'expiracao',
        'identificado',
        'idctx'
    ];

    // Define um método de boot para gerar o UUID automaticamente
    protected static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
            if (empty($model->{$model->getKeyName()})) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }
}

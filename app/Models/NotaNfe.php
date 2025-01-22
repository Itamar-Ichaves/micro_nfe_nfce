<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaNfe extends Model
{
    use HasFactory;

    protected $table = 'notaNfe';

    protected $fillable = [
        'token_company',
        'token_emitente',
        'cnpj',
        'status',
        'protocolo',
        'recibo',
        'chave',
        'caminho',
        'nomeArquivo'
    ];
}

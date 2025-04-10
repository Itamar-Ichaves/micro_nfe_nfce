<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotaNfe extends Model
{
    use HasFactory;

    protected $table = 'notaNfe';

    protected $fillable = [
        'id',
        'token_company',
        'token_emitente',
        'nfe_id',
        'nNF',
        'cnpj',
        'status',
        'protocolo',
        'recibo',
        'chave',
        'caminho',
        'nomeArquivo',
        'dhEmi',
        'dhSaiEnt' 


    ];

    protected $primaryKey = 'id';
    protected $keyType = 'string';
    public $incrementing = false;
    public $timestamps = false;

    protected $casts = [
        'dhEmi' => 'datetime',
        'dhSaiEnt' => 'datetime',
    ];
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RespTecnico extends Model
{
    use HasFactory;
    public $CNPJ;
    public $xContato;
    public $email;
    public $fone;

    public function setarDados( $data){
        $this->CNPJ     = $data['CNPJ'] ?? null;
        $this->xContato = $data['xContato'] ?? null;
        $this->email    = $data['email'] ?? null;
        $this->fone     = $data['fone'] ?? null;
    }

    public static function montarXml($nfe, $infRespTec, $dadosCartao){
        $std = new \stdClass();
        $std->CNPJ  	= $infRespTec->CNPJ;
        $std->xContato  = $infRespTec->xContato;
        $std->email 	= $infRespTec->email;
        $std->fone 		= $infRespTec->fone;

        $nfe->tagdetPag($std);
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Veiculo extends Model
{
    use HasFactory;


    public $placa;
    public $UF;
    public $RNTC;
    public $vagao;
    public $balsa;


    public function setarDados( $data){
        $this->placa        = $data['placa'] ?? null;
        $this->UF           = $data['UF'] ?? null;
        $this->RNTC         = $data['RNTC'] ?? null;
        $this->vagao        = $data['vagao'] ?? null;
        $this->balsa        = $data['balsa'] ?? null;
    }

    public static function montarXml($nfe, $dados){
        $std = new \stdClass();
        $std->placa  		= $dados->placa ;
        $std->UF 			= $dados->UF;
        $std->RNTC 		    = $dados->RNTC;
        $nfe->tagveicTransp($std);
    }
}

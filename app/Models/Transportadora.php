<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transportadora extends Model
{
    use HasFactory;

    public $xNome;
    public $IE;
    public $xEnder;
    public $xMun;
    public $UF;
    public $CNPJ;


    public function setarDados( $data){
        $this->xNome       = $data['xNome'] ?? null;
        $this->IE          = $data['IE'] ?? null;
        $this->xEnder      = $data['xEnder'] ?? null;
        $this->xMun        = $data['xMun'] ?? null;
        $this->UF          = $data['UF'] ?? null;
        $this->CNPJ        = $data['CNPJ'] ?? null;
    }

    public static function montarXml($nfe, $dados){
        $std = new \stdClass();
        $std->xNome 		= $dados->xNome ? tiraAcento($dados->xNome) : null;
        $std->IE 			= $dados->IE ? tira_mascara($dados->IE) : null;
        $std->xEnder 		= $dados->xEnder ? tiraAcento($dados->xEnder) : null;
        $std->xMun 			= $dados->xMun ? tiraAcento($dados->xMun) : null;
        $std->UF 		    = $dados->UF;
        $std->CNPJ 		    = $dados->CNPJ ? tira_mascara($dados->CNPJ) : null;
        $nfe->tagtransporta($std);
    }
}

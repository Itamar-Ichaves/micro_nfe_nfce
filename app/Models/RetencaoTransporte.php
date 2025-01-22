<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RetencaoTransporte extends Model
{
    use HasFactory;


    public $vServ;
    public $vBCRet;
    public $pICMSRet;
    public $vICMSRet;
    public $CFOP;
    public $cMunFG;

    public function setarDados( $data){
        $this->vServ        = $data['vServ'] ?? null;
        $this->vBCRet       = $data['vBCRet'] ?? null;
        $this->pICMSRet     = $data['pICMSRet'] ?? null;
        $this->vICMSRet     = $data['vICMSRet'] ?? null;
        $this->CFOP         = $data['CFOP'] ?? null;
        $this->cMunFG       = $data['cMunFG'] ?? null;
    }
    public static function montarXml($nfe, $dados){
        $std                = new \stdClass();
        $std->vServ 		= $dados->vServ   ? formataNumero($dados->vServ)  : null;
        $std->vBCRet 		= $dados->vBCRet  ? formataNumero($dados->vBCRet)  : null;
        $std->pICMSRet 		= $dados->pICMSRet  ? formataNumero($dados->pICMSRet)  : null;
        $std->vICMSRet 		= $dados->vICMSRet  ? formataNumero($dados->vICMSRet)  : null;
        $std->CFOP 		    = $dados->CFOP;
        $std->cMunFG 		= $dados->cMunFG;
        $nfe->tagretTransp($std);
    }
}

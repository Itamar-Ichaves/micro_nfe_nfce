<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PisNfe extends Model
{
    use HasFactory;
    
    public $CST;
    public $vBC;
    public $pPIS;
    public $vPIS;
    public $qBCProd;
    public $vAliqProd;
    public $tipo_calculo;

    public function setarDados( $data){
        $this->CST              = $data['CST'] ?? null;
        $this->vBC              = $data['vBC'] ?? null;
        $this->pPIS             = $data['pPIS'] ?? null;
        $this->vPIS             = $data['vPIS'] ?? null;
        $this->qBCProd          = $data['qBCProd'] ?? null;
        $this->vAliqProd        = $data['vAliqProd'] ?? null;
        $this->tipo_calculo     = $data['tipo_calculo'] ?? null;
    }

    public static function montarXml($cont,$nfe, $dados){
        $std = new \stdClass();
        $std->item              = $cont; //item da NFe
        $std->CST 				= $dados->CST;
        $std->vBC 				= $dados->vBC ? formataNumero($dados->vBC) : null;
        $std->pPIS 				= $dados->pPIS ? formataNumero($dados->pPIS) : null;
        $std->vPIS 				= $dados->vPIS ? formataNumero($dados->vPIS) : null;
        $std->qBCProd 			= $dados->qBCProd ? formataNumero($dados->qBCProd) : null;
        $std->vAliqProd 		= $dados->vAliqProd ? formataNumero($dados->vAliqProd) : null;
        $nfe->tagPIS($std);
    }

    public  function calculo($pis){
        if(($pis->CST =='01') || ($pis->CST =='02')) {
            $pis->vPIS         = $pis->vBC * ($pis->pPIS/100);
        }else if($pis->CST =='03'){
            $pis->vPIS         = $pis->vAliqProd  * $pis->qBCProd ;
        }else if($pis->CST =='99'){
            if($pis->tipo_calculo == "1"){
                $pis->vPIS     = $pis->vBC  * ($pis->pPIS/100);
            }else if($pis->tipo_calculo =="2" ){
                $pis->vPIS     = $pis->vAliqProd  * $pis->qBCProd ;
            }
        }else{
            $pis->vBC  = null;
            $pis->vPIS = null;
        }
    }
}

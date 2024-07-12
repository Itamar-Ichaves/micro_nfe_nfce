<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CofinsNfe extends Model
{
    use HasFactory;

     
    public $CST;
    public $vBC;
    public $pCOFINS;
    public $vCOFINS;
    public $qBCProd;
    public $vAliqProd;
    public $tipo_calculo;

    public function setarDados( $data){
        $this->CST              = $data['CST'] ?? null;
        $this->vBC              = $data['vBC'] ?? null;
        $this->pCOFINS          = $data['pCOFINS'] ?? null;
        $this->vCOFINS          = $data['vCOFINS'] ?? null;
        $this->qBCProd          = $data['qBCProd'] ?? null;
        $this->vAliqProd        = $data['vAliqProd'] ?? null;
        $this->tipo_calculo     = $data['tipo_calculo'] ?? null;
    }

    public static function montarXml($cont,$nfe, $dados){
        $std = new \stdClass();
        $std->item              = $cont; //item da NFe
        $std->CST 				= $dados->CST;
        $std->vBC 				= $dados->vBC ? formataNumero($dados->vBC) : null;
        $std->pCOFINS 			= $dados->pCOFINS ? formataNumero($dados->pCOFINS) : null;
        $std->vCOFINS 			= $dados->vCOFINS ? formataNumero($dados->vCOFINS) : null;
        $std->qBCProd 			= $dados->qBCProd ? formataNumero($dados->qBCProd) : null;
        $std->vAliqProd 		= $dados->vAliqProd ? formataNumero($dados->vAliqProd) : null;
        $nfe->tagCOFINS($std);
    }

    public  function calculo($cofins){
        if(($cofins->CST =='01') || ($cofins->CST =='02')) {
            $cofins->vCOFINS         = $cofins->vBC * ($cofins->pCOFINS/100);
        }else if($cofins->CST =='03'){
            $cofins->vCOFINS         = $cofins->vAliqProd  * $cofins->qBCProd ;
        }else if($cofins->CST =='99'){
            if($cofins->tipo_calculo=="1"){
                $cofins->vCOFINS     = $cofins->vBC  * ($cofins->pCOFINS/100);
            }else if($cofins->tipo_calculo =="2" ){
                $cofins->vCOFINS     = $cofins->vAliqProd  * $cofins->qBCProd ;
            }
        }else{
            $cofins->vBC = null;
            $cofins->vCOFINS = null;
        }
    }
    
}

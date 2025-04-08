<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IpiNfe extends Model
{
    use HasFactory;

     
    public $CST;
    public $CNPJProd;
    public $cSelo;
    public $qSelo;
    public $cEnq;
    public $vBC;
    public $pIPI;
    public $vIPI;
    public $qUnid;
    public $vUnid;
    public $tipo_calculo;

    public function setarDados( $data){
          $this->CST          = $data['CST'] ?? null;
          $this->CNPJProd     = $data['CNPJProd'] ?? null;
          $this->cSelo        = $data['cSelo'] ?? null;
          $this->qSelo        = $data['qSelo'] ?? null;
          $this->cEnq         = $data['cEnq'] ?? null;
          $this->vBC          = $data['vBC'] ?? null;
         $this->pIPI         = $data['pIPI'] ?? null;
         $this->vIPI         = $data['vIPI'] ?? null;
         $this->qUnid        = $data['qUnid'] ?? null;
         $this->vUnid        = $data['vUnid'] ?? null;
         $this->tipo_calculo = $data['tipo_calculo'] ?? null;
    }

    public static function montarXml($cont,$nfe, $dados){
        $std                = new \stdClass();
        $std->item           = $cont; //item da NFe
        $std->CST           = $dados->CST;
        $std->CNPJProd      = $dados->CNPJProd ? tira_mascara($dados->CNPJProd) : null;
        $std->cSelo         = $dados->cSelo;
        $std->qSelo         = $dados->qSelo ?? rand(11111,99999);;
        $std->cEnq          = $dados->cEnq;
        $std->vBC           = $dados->vBC ? formataNumero($dados->vBC) : null;
        $std->pIPI          = $dados->pIPI ? formataNumero($dados->pIPI) : null;
        $std->vIPI          = $dados->vIPI ? formataNumero($dados->vIPI) : null;
        $std->qUnid         = $dados->qUnid ? formataNumero($dados->qUnid) : null;
        $std->vUnid         = $dados->vUnid ? formataNumero($dados->vUnid) : null;
        $nfe->tagIPI($std);
    }

    public  function calculo($ipi){
        if($ipi->tipo_calculo==1){
            $ipi->vIPI     = $ipi->vBC * ($ipi->pIPI/100)     ;
        }else if($ipi->tipo_calculo==2){
            $ipi->vIPI          = $ipi->vUnid * $ipi->qUnid ;
        }
        return $ipi;
    }
}

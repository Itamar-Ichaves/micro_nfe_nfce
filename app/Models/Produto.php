<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Produto extends Model
{
    use HasFactory;


    public $cProd;
    public $cEAN;
    public $xProd;
    public $NCM;
    public $NVE;
    public $CEST;
    public $indEscala;
    public $CNPJFab;
    public $cBenef;
    public $EXTIPI;
    public $CFOP;
    public $uCom;
    public $qCom;
    public $vUnCom;
    public $vProd;
    public $cEANTrib;
    public $uTrib;
    public $qTrib;
    public $vUnTrib;
    public $vFrete;
    public $vSeg;
    public $vDesc;
    public $vOutro;
    public $indTot;

    public function setarDados(array $data){
        $this->cProd        = $data['cProd'] ?? null;
        $this->cEAN         = $data['cEAN'] ?? null;
        $this->xProd        = $data['xProd'] ?? null;
        $this->NCM          = $data['NCM'] ?? null;
        $this->NVE          = $data['NVE'] ?? null;
        $this->CEST         = $data['CEST'] ?? null;
        $this->indEscala    = $data['indEscala'] ?? null;
        $this->CNPJFab      = $data['CNPJFab'] ?? null;
        $this->cBenef       = $data['cBenef'] ?? null;
        $this->EXTIPI       = $data['EXTIPI'] ?? null;
        $this->CFOP         = $data['CFOP'] ?? null;
        $this->uCom         = $data['uCom'] ?? null;
        $this->vUnCom       = $data['vUnCom'] ?? null;
        $this->qCom         = $data['qCom'] ?? null;
        $this->vProd        = $data['vProd'] ?? null;
        $this->cEANTrib     = $data['cEANTrib'] ?? null;
        $this->uTrib        = $data['uTrib'] ?? null;
        $this->qTrib        = $data['qTrib'] ?? null;
        $this->vUnTrib      = $data['vUnTrib'] ?? null;
        $this->vFrete       = $data['vFrete'] ?? null;
        $this->vSeg         = $data['vSeg'] ?? null;
        $this->vDesc        = $data['vDesc'] ?? null;
        $this->vOutro       = $data['vOutro'] ?? null;
        $this->indTot       = $data['indTot'] ?? null;

    }

     public static function montarXml($cont, $nfe, $dados){
   
        $std            = new \stdClass();
        $std->item      = $cont; //item da NFe
        $std->cProd     = $dados->cProd;
        $std->cEAN      = $dados->cEAN;
        $std->xProd     = tiraAcento($dados->xProd);
        $std->NCM       = tira_mascara($dados->NCM);
        $std->cBenef    = $dados->cBenef; //incluido no layout 4.00
        $std->EXTIPI    = $dados->EXTIPI;
        $std->CFOP      = $dados->CFOP;
        $std->uCom      = $dados->uCom;
        $std->qCom      = formataNumero($dados->qCom);
        $std->vUnCom    = formataNumero($dados->vUnCom);
        $std->vProd     = formataNumero($dados->vProd);
        $std->cEANTrib  = $dados->cEANTrib;
        $std->uTrib     = $dados->uTrib;
        $std->qTrib     = formataNumero($dados->qTrib);
        $std->vUnTrib   = formataNumero($dados->vUnTrib);        ;
        $std->vFrete    = ($dados->vOutro > 0) ? formataNumero($dados->vFrete) : null;
        $std->vSeg      = ($dados->vSeg > 0) ? formataNumero($dados->vSeg) : null  ;
        $std->vDesc     = ($dados->vDesc > 0) ? formataNumero($dados->vDesc) : null;
        $std->vOutro    = ($dados->vOutro > 0) ? formataNumero($dados->vOutro) : null ;
        $std->indTot    = $dados->indTot;
      //  $std->xPed      = $dados->xPed;
      //  $std->nItemPed  = $dados->nItemPed;
      //  $std->nFCI      = $dados->nFCI;
        $nfe->tagprod($std);

        if(isset($dados->CEST) ) {
            $std = new \stdClass();
            $std->item = $cont; //item da NFe
            $std->CEST = tira_mascara($dados->CEST);
            $nfe->tagCEST($std);
        }
    }
}

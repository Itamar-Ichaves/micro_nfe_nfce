<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TotalNfe extends Model
{
    use HasFactory;

    public	$vBC;
    public	$vICMS;
    public	$vICMSDeson;
    public	$vBCST;
    public	$vST;
    public	$vProd;
    public	$vFrete;
    public	$vSeg;
    public	$vDesc;
    public	$vII;
    public	$vIPI;
    public	$vPIS;
    public	$vCOFINS;
    public	$vOutro;
    public	$vNF;
    public	$vIPIDevol;
    public	$vTotTrib;
    public	$vFCP;
    public	$vFCPST;
    public	$vFCPSTRet;
    public	$vFCPUFDest;
    public	$vICMSUFDest;
    public	$vICMSUFRemet;
    public	$qBCMono;
    public	$vICMSMono;
    public	$qBCMonoReten;
    public	$vICMSMonoReten;
    public	$qBCMonoRet;
    public	$vICMSMonoRet;

    public function setarDados( $data){
        $this->vBC				= $data['vBC'] ?? null ;
        $this->vICMS            = $data['vICMS'] ?? null ;
        $this->vICMSDeson       = $data['vICMSDeson'] ?? null ;
        $this->vBCST            = $data['vBCST'] ?? null ;
        $this->vST              = $data['vST'] ?? null ;
        $this->vProd            = $data['vProd'] ?? null ;
        $this->vFrete           = $data['vFrete'] ?? null ;
        $this->vSeg             = $data['vSeg'] ?? null ;
        $this->vDesc            = $data['vDesc'] ?? null ;
        $this->vII              = $data['vII'] ?? null ;
        $this->vIPI             = $data['vIPI'] ?? null ;
        $this->vPIS             = $data['vPIS'] ?? null ;
        $this->vCOFINS          = $data['vCOFINS'] ?? null ;
        $this->vOutro           = $data['vOutro'] ?? null ;
        $this->vNF              = $data['vNF'] ?? null ;
        //$this->vIPIDevol        = $data['vIPIDevol'] ?? null ;
        $this->vTotTrib         = $data['vTotTrib'] ?? null ;
        $this->vFCP             = $data['vFCP'] ?? null ;
        $this->vFCPST           = $data['vFCPST'] ?? null ;
        $this->vFCPSTRet        = $data['vFCPSTRet'] ?? null ;
        $this->vFCPUFDest       = $data['vFCPUFDest'] ?? null ;
        $this->vICMSUFDest      = $data['vICMSUFDest'] ?? null ;
        $this->vICMSUFRemet     = $data['vICMSUFRemet'] ?? null ;
        $this->qBCMono          = $data['qBCMono'] ?? null ;
        $this->vICMSMono        = $data['vICMSMono'] ?? null ;
        $this->qBCMonoReten     = $data['qBCMonoReten'] ?? null ;
        $this->vICMSMonoReten   = $data['vICMSMonoReten'] ?? null ;
        $this->qBCMonoRet       = $data['qBCMonoRet'] ?? null ;
        $this->vICMSMonoRet     = $data['vICMSMonoRet'] ?? null ;
    }

    public static function montarXml($nfe, $dados){
        $std = new \stdClass();
        $std->vBC           = ($dados->vBC)		   ? formataNumero($dados->vBC)	      : 0.00;
        $std->vICMS         = ($dados->vICMS)      ? formataNumero($dados->vICMS)     : 0.00;
        $std->vICMSDeson    = ($dados->vICMSDeson) ? formataNumero($dados->vICMSDeson): 0.00;
        $std->vFCP          = ($dados->vFCP)       ? formataNumero($dados->vFCP)      : 0.00;
        $std->vBCST         = ($dados->vBCST)      ? formataNumero($dados->vBCST)     : 0.00;
        $std->vST           = ($dados->vST)        ? formataNumero($dados->vST)       : 0.00;
        $std->vFCPST        = ($dados->vFCPST)     ? formataNumero($dados->vFCPST)    : 0.00;
        $std->vFCPSTRet     = ($dados->vFCPSTRet)  ? formataNumero($dados->vFCPSTRet) : 0.00;
        $std->vProd         = ($dados->vProd )     ? formataNumero($dados->vProd)     : 0.00;
        $std->vFrete        = ($dados->vFrete )    ? formataNumero($dados->vFrete)    : 0.00;
        $std->vSeg          = ($dados->vSeg  )     ? formataNumero($dados->vSeg)      : 0.00;
        $std->vDesc         = ($dados->vDesc )     ? formataNumero($dados->vDesc)     : 0.00;
        $std->vII           = ($dados->vII)        ? formataNumero($dados->vII)       : 0.00;
        $std->vIPI          = ($dados->vIPI)       ? formataNumero($dados->vIPI)      : 0.00;
        $std->vIPIDevol     = ($dados->vIPIDevol)  ? formataNumero($dados->vIPIDevol) : 0.00;
        $std->vPIS          = ($dados->vPIS)       ? formataNumero($dados->vPIS)      : 0.00;
        $std->vCOFINS       = ($dados->vCOFINS)    ? formataNumero($dados->vCOFINS)   : 0.00;
        $std->vOutro        = ($dados->vOutro)     ? formataNumero($dados->vOutro)    : 0.00;
        $std->vNF           = ($dados->vNF)        ? formataNumero($dados->vNF)       : 0.00;
        $std->vTotTrib      = ($dados->vTotTrib)   ? formataNumero($dados->vTotTrib)  : 0.00;

        $nfe->tagICMSTot($std);
    }
}

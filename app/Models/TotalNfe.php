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

    public static function montarXml($nfe, $dados)
{
    $std = new \stdClass();

    $getValue = function ($key, $default = 0.00) use ($dados) {
        return property_exists($dados, $key) ? formataNumero($dados->$key) : formataNumero($default);
    };

    $std->vProd = $getValue('vProd');
    $std->vFrete = $getValue('vFrete');
    $std->vSeg = $getValue('vSeg');
    $std->vOutro = $getValue('vOutro');
    $std->vIPI = $getValue('vIPI');
    $std->vDesc = $getValue('vDesc');

    // Recalcular o total da NFe
    $std->vNF = formataNumero(
        ($std->vProd + $std->vFrete + $std->vSeg + $std->vOutro + $std->vIPI) - $std->vDesc
    );

    $std->vBC = $getValue('vBC');
    $std->vICMS = $getValue('vICMS');
    $std->vICMSDeson = $getValue('vICMSDeson');
    $std->vFCP = $getValue('vFCP');
    $std->vBCST = $getValue('vBCST');
    $std->vST = $getValue('vST');
    $std->vFCPST = $getValue('vFCPST');
    $std->vFCPSTRet = $getValue('vFCPSTRet');
    $std->vPIS = $getValue('vPIS');
    $std->vCOFINS = $getValue('vCOFINS');
    $std->vTotTrib = $getValue('vTotTrib');

    $nfe->tagICMSTot($std);
}



}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IcmsNfe extends Model
{
    use HasFactory;

    
    public $orig ;
    public $CST ;
    public $modBC ;
    public $ipi ;
    public $pRedBC ;
    public $vBC ;
    public $pICMS ;
    public $vICMS ;
    public $modBCST ;
    public $pMVAST ;
    public $pRedBCST ;
    public $vBCST ;
    public $pICMSST ;
    public $vICMSST ;
    public $UFST ;
    public $pBCop ;
    public $vBCSTRet ;
    public $vICMSSTRet ;
    public $motDesICMS ;
    public $vBCSTDest ;
    public $vICMSSTDest ;
    public $pCredSN ;
    public $vCredICMSSN ;
    public $vICMSDeson ;
    public $vICMSOp ;
    public $pDif ;
    public $vICMSDif ;
    public $vBCFCP ;
    public $pFCP ;
    public $vFCP ;
    public $vBCFCPST ;
    public $pFCPST ;
    public $vFCPST ;
    public $vBCFCPSTRet ;
    public $pFCPSTRet ;
    public $vFCPSTRet ;
    public $pST ;
    public $pFCPDif ;
    public $vFCPDif ;
    public $vFCPEfet ;
    public $vICMSSTDeson ;
    public $motDesICMSST ;
    public $valor_pauta ;
    public $qtde_produto_pauta ;

    public function setarDados( $data){
        $this->orig		            =$data['orig'] ?? null;
        $this->CST		            =$data['CST'] ?? null;
        $this->ipi		            =$data['ipi'] ?? null;
        $this->modBC	            =$data['modBC'] ?? null;
        $this->pRedBC		        =$data['pRedBC'] ?? null;
        $this->vBC		            =$data['vBC'] ?? null;
        $this->pICMS		        =$data['pICMS'] ?? null;
        $this->vICMS		        =$data['vICMS'] ?? null;
        $this->modBCST		        =$data['modBCST'] ?? null;
        $this->pMVAST		        =$data['pMVAST'] ?? null;
        $this->pRedBCST		        =$data['pRedBCST'] ?? null;
        $this->vBCST		        =$data['vBCST'] ?? null;
        $this->pICMSST		        =$data['pICMSST'] ?? null;
        $this->vICMSST		        =$data['vICMSST'] ?? null;
        $this->UFST		            =$data['UFST'] ?? null;
        $this->pBCop		        =$data['pBCop'] ?? null;
        $this->vBCSTRet		        =$data['vBCSTRet'] ?? null;
        $this->vICMSSTRet	        =$data['vICMSSTRet'] ?? null;
        $this->motDesICMS	        =$data['motDesICMS'] ?? null;
        $this->vBCSTDest	        =$data['vBCSTDest'] ?? null;
        $this->vICMSSTDest	        =$data['vICMSSTDest'] ?? null;
        $this->pCredSN		        =$data['pCredSN'] ?? null;
        $this->vCredICMSSN	        =$data['vCredICMSSN'] ?? null;
        $this->vICMSDeson	        =$data['vICMSDeson'] ?? null;
        $this->vICMSOp		        =$data['vICMSOp'] ?? null;
        $this->pDif		            =$data['pDif'] ?? null;
        $this->vICMSDif		        =$data['vICMSDif'] ?? null;
        $this->vBCFCP		        =$data['vBCFCP'] ?? null;
        $this->pFCP		            =$data['pFCP'] ?? null;
        $this->vFCP		            =$data['vFCP'] ?? null;
        $this->vBCFCPST		        =$data['vBCFCPST'] ?? null;
        $this->pFCPST		        =$data['pFCPST'] ?? null;
        $this->vFCPST		        =$data['vFCPST'] ?? null;
        $this->vBCFCPSTRet	        =$data['vBCFCPSTRet'] ?? null;
        $this->pFCPSTRet	        =$data['pFCPSTRet'] ?? null;
        $this->vFCPSTRet	        =$data['vFCPSTRet'] ?? null;
        $this->pST		            =$data['pST'] ?? null;
        $this->pFCPDif		        =$data['pFCPDif'] ?? null;
        $this->vFCPDif		        =$data['vFCPDif'] ?? null;
        $this->vFCPEfet		        =$data['vFCPEfet'] ?? null;
        $this->vICMSSTDeson	        =$data['vICMSSTDeson'] ?? null;
        $this->motDesICMSST	        =$data['motDesICMSST'] ?? null;
        $this->valor_pauta	        =$data['valor_pauta'] ?? null;
        $this->qtde_produto_pauta	=$data['qtde_produto_pauta'] ?? null;
    }

    public static function montarXml( $cont, $nfe, $dados){
        $std                    = new \stdClass();
        $std->item              = $cont; //item da NFe
        $std->orig              = $dados->orig;

        if(intval($dados->CST) < 99){
            $std->CST           = $dados->CST ;
            $cst                = $std->CST;
        }else{
            $std->CSOSN          = $dados->CST;
            $cst                 = $std->CSOSN;
        }

        if($cst=="00"){
            $std->modBC             = $dados->modBC;
            $std->vBC               = $dados->vBC ? formataNumero($dados->vBC) : null;
            $std->pICMS             = $dados->pICMS ? formataNumero($dados->pICMS) : null;
            $std->vICMS             = $dados->vICMS ? formataNumero($dados->vICMS) : null;

            if($dados->vFCP){
                $std->vBCFCP        = $dados->vBCFCP ? formataNumero($dados->vBCFCP) : null;
                $std->pFCP          = $dados->pFCP ? formataNumero($dados->pFCP) : null;
                $std->vFCP          = $dados->vFCP ? formataNumero($dados->vFCP) : null;
            }

        }elseif($cst=="10"){
            $std->modBC             = $dados->modBC;
            $std->vBC               = $dados->vBC ? formataNumero($dados->vBC) : null;
            $std->pICMS             = $dados->pICMS ? formataNumero($dados->pICMS) : null;
            $std->vICMS             = $dados->vICMS ? formataNumero($dados->vICMS) : null;

            if($dados->vFCP){
                $std->vBCFCP        = $dados->vBCFCP ? formataNumero($dados->vBCFCP) : null;
                $std->pFCP          = $dados->pFCP ? formataNumero($dados->pFCP) : null;
                $std->vFCP          = $dados->vFCP ? formataNumero($dados->vFCP) : null;
            }

            if($dados->vFCPST){
                $std->vBCFCPST      = $dados->vBCFCPST ? formataNumero($dados->vBCFCPST) : null;
                $std->pFCPST        = $dados->pFCPST ? formataNumero($dados->pFCPST) : null;
                $std->vFCPST        = $dados->vFCPST ? formataNumero($dados->vFCPST) : null;
            }

            $std->modBCST           = $dados->modBCST ;
            $std->pMVAST            = $dados->pMVAST ? formataNumero($dados->pMVAST) : null;
            $std->pRedBCST          = $dados->pRedBCST ? formataNumero($dados->pRedBCST) : null;
            $std->vBCST             = $dados->vBCST ? formataNumero($dados->vBCST) : null;
            $std->pICMSST           = $dados->pICMSST ? formataNumero($dados->pICMSST) : null;
            $std->vICMSST           = $dados->vICMSST ? formataNumero($dados->vICMSST) : null;

        }elseif($cst=="20"){
            $std->modBC             = $dados->modBC;
            $std->pRedBC            = $dados->pRedBC ? formataNumero($dados->pRedBC) : null;
            $std->vBC               = $dados->vBC ? formataNumero($dados->vBC) : null;
            $std->pICMS             = $dados->pICMS ? formataNumero($dados->pICMS) : null;
            $std->vICMS             = $dados->vICMS ? formataNumero($dados->vICMS) : null;

            if($dados->vFCP){
                $std->vBCFCP        = $dados->vBCFCP ? formataNumero($dados->vBCFCP) : null;
                $std->pFCP          = $dados->pFCP ? formataNumero($dados->pFCP) : null;
                $std->vFCP          = $dados->vFCP ? formataNumero($dados->vFCP) : null;
            }


            $std->vICMSDeson        = $dados->vICMSDeson ? formataNumero($dados->vICMSDeson) : null;
            $std->motDesICMS        = $dados->motDesICMS;

        }elseif($cst=="30"){
            $std->modBCST           = $dados->modBCST;
            $std->pMVAST            = $dados->pMVAST ? formataNumero($dados->pMVAST) : null;
            $std->pRedBCST          = $dados->pRedBCST ? formataNumero($dados->pRedBCST) : null;
            $std->vBCST             = $dados->vBCST ? formataNumero($dados->vBCST) : null;
            $std->pICMSST           = $dados->pICMSST ? formataNumero($dados->pICMSST) : null;
            $std->vICMSST           = $dados->vICMSST ? formataNumero($dados->vICMSST) : null;

            if($dados->vFCPST){
                $std->vBCFCPST          = $dados->vBCFCPST ? formataNumero($dados->vBCFCPST) : null;
                $std->pFCPST            = $dados->pFCPST ? formataNumero($dados->pFCPST) : null;
                $std->vFCPST            = $dados->vFCPST ? formataNumero($dados->vFCPST) : null;
            }

            $std->vICMSDeson           = $dados->vICMSDeson ? formataNumero($dados->vICMSDeson) : null;
            $std->motDesICMS           = $dados->motDesICMS;

        }elseif($cst=="40" || $cst=="41" || $cst=="50" ){
            $std->vICMSDeson             = $dados->vICMSDeson ? formataNumero($dados->vICMSDeson) : null;
            $std->motDesICMS             = $dados->motDesICMS;

        }elseif($cst=="51"){
            $std->modBC             = $dados->modBC;
            $std->pRedBC            = $dados->pRedBC ? formataNumero($dados->pRedBC) : null;
            $std->vBC               = $dados->vBC ? formataNumero($dados->vBC) : null;
            $std->pICMS             = $dados->pICMS ? formataNumero($dados->pICMS) : null;
            $std->vICMSOp           = $dados->vICMSOp ? formataNumero($dados->vICMSOp) : null;
            $std->pDif              = $dados->pDif ? formataNumero($dados->pDif) : null;
            $std->vICMSDif          = $dados->vICMSDif ? formataNumero($dados->vICMSDif) : null;
            $std->vICMS             = $dados->vICMS ? formataNumero($dados->vICMS) : null;

            if($dados->vFCP){
                $std->vBCFCP        = $dados->vBCFCP ? formataNumero($dados->vBCFCP) : null;
                $std->pFCP          = $dados->pFCP ? formataNumero($dados->pFCP) : null;
                $std->vFCP          = $dados->vFCP ? formataNumero($dados->vFCP) : null;
            }
        }elseif($cst=="60"){
            $std->vBCSTRet           = $dados->vBCSTRet ? formataNumero($dados->vBCSTRet) : null;
            $std->pST                = $dados->pST ? formataNumero($dados->pST) : null;
            $std->vICMSSubstituto    = $dados->vICMSSubstituto ? formataNumero($dados->vICMSSubstituto) : null;
            $std->vICMSSTRet         = $dados->vICMSSTRet ? formataNumero($dados->vICMSSTRet) : null;

            $std->vBCFCPSTRet        = $dados->vBCFCPSTRet ? formataNumero($dados->vBCFCPSTRet) : null;
            $std->pFCPSTRet          = $dados->pFCPSTRet ? formataNumero($dados->pFCPSTRet) : null;
            $std->vFCPSTRet          = $dados->vFCPSTRet ? formataNumero($dados->vFCPSTRet) : null;

            $std->pRedBCEfet         = $dados->pRedBCEfet ? formataNumero($dados->pRedBCEfet) : null;
            $std->vBCEfet            = $dados->vBCEfet ? formataNumero($dados->vBCEfet) : null;
            $std->pICMSEfet          = $dados->pICMSEfet ? formataNumero($dados->pICMSEfet) : null;
            $std->vICMSEfet          = $dados->vICMSEfet ? formataNumero($dados->vICMSEfet) : null;

        }elseif ($cst == "61") {
            $std->vBCSTRet           = $dados->vBCSTRet ? formataNumero($dados->vBCSTRet) : null; // Base de cálculo do ICMS-ST Retido
            $std->pST                = $dados->pST ? formataNumero($dados->pST) : null;          // Alíquota do ICMS-ST
            $std->vICMSSubstituto    = $dados->vICMSSubstituto ? formataNumero($dados->vICMSSubstituto) : null; // ICMS do Substituto Tributário
            $std->vICMSSTRet         = $dados->vICMSSTRet ? formataNumero($dados->vICMSSTRet) : null; // Valor do ICMS-ST Retido
        
            $std->vBCFCPSTRet        = $dados->vBCFCPSTRet ? formataNumero($dados->vBCFCPSTRet) : null; // Base de cálculo do FCP-ST
            $std->pFCPSTRet          = $dados->pFCPSTRet ? formataNumero($dados->pFCPSTRet) : null;     // Percentual do FCP-ST
            $std->vFCPSTRet          = $dados->vFCPSTRet ? formataNumero($dados->vFCPSTRet) : null;     // Valor do FCP-ST
        
            $std->vICMSDeson         = $dados->vICMSDeson ? formataNumero($dados->vICMSDeson) : null;   // Valor do ICMS desonerado
            $std->motDesICMS         = $dados->motDesICMS ? $dados->motDesICMS : null;                 // Motivo da desoneração
        
            $std->pRedBCEfet         = $dados->pRedBCEfet ? formataNumero($dados->pRedBCEfet) : null;  // Percentual de Redução da Base de Cálculo Efetiva
            $std->vBCEfet            = $dados->vBCEfet ? formataNumero($dados->vBCEfet) : null;       // Base de Cálculo Efetiva
            $std->pICMSEfet          = $dados->pICMSEfet ? formataNumero($dados->pICMSEfet) : null;   // Alíquota do ICMS Efetivo
            $std->vICMSEfet          = $dados->vICMSEfet ? formataNumero($dados->vICMSEfet) : null;   // Valor do ICMS Efetivo
        }
        elseif($cst=="70"){
            $std->modBC             = $dados->modBC;
            $std->pRedBC            = $dados->pRedBC ? formataNumero($dados->pRedBC) : null;
            $std->vBC               = $dados->vBC ? formataNumero($dados->vBC) : null;
            $std->pICMS             = $dados->pICMS ? formataNumero($dados->pICMS) : null;
            $std->vICMS             = $dados->vICMS ? formataNumero($dados->vICMS) : null;

            if($dados->vFCP){
                $std->vBCFCP            = $dados->vBCFCP ? formataNumero($dados->vBCFCP) : null;
                $std->pFCP              = $dados->pFCP ? formataNumero($dados->pFCP) : null;
                $std->vFCP              = $dados->vFCP ? formataNumero($dados->vFCP) : null;
            }


            $std->modBCST           = $dados->modBCST ;
            $std->pMVAST            = $dados->pMVAST ? formataNumero($dados->pMVAST) : null;
            $std->pRedBCST          = $dados->pRedBCST ? formataNumero($dados->pRedBCST) : null;
            $std->vBCST             = $dados->vBCST ? formataNumero($dados->vBCST) : null;
            $std->pICMSST           = $dados->pICMSST ? formataNumero($dados->pICMSST) : null;
            $std->vICMSST           = $dados->vICMSST ? formataNumero($dados->vICMSST) : null;

            if($dados->vFCPST){
                $std->vBCFCPST          = $dados->vBCFCPST ? formataNumero($dados->vBCFCPST) : null;
                $std->pFCPST            = $dados->pFCPST ? formataNumero($dados->pFCPST) : null;
                $std->vFCPST            = $dados->vFCPST ? formataNumero($dados->vFCPST) : null;
            }

            $std->vICMSDeson         = $dados->vICMSDeson ? formataNumero($dados->vICMSDeson) : null;
            $std->motDesICMS         = $dados->motDesICMS;

        }elseif($cst=="90"){
            $std->modBC             = $dados->modBC;
            $std->pRedBC            = $dados->pRedBC ? formataNumero($dados->pRedBC) : null;
            $std->vBC               = $dados->vBC ? formataNumero($dados->vBC) : null;
            $std->pICMS             = $dados->pICMS ? formataNumero($dados->pICMS) : null;
            $std->vICMS             = $dados->vICMS ? formataNumero($dados->vICMS) : null;

            if($dados->vFCP){
                $std->vBCFCP        = $dados->vBCFCP ? formataNumero($dados->vBCFCP) : null;
                $std->pFCP          = $dados->pFCP ? formataNumero($dados->pFCP) : null;
                $std->vFCP          = $dados->vFCP ? formataNumero($dados->vFCP) : null;
            }

            $std->modBCST           = $dados->modBCST ;
            $std->pMVAST            = $dados->pMVAST ? formataNumero($dados->pMVAST) : null;
            $std->pRedBCST          = $dados->pRedBCST ? formataNumero($dados->pRedBCST) : null;
            $std->vBCST             = $dados->vBCST ? formataNumero($dados->vBCST) : null;
            $std->pICMSST           = $dados->pICMSST ? formataNumero($dados->pICMSST) : null;
            $std->vICMSST           = $dados->vICMSST ? formataNumero($dados->vICMSST) : null;

            if($dados->vFCPST){
                $std->vBCFCPST          = $dados->vBCFCPST ? formataNumero($dados->vBCFCPST) : null;
                $std->pFCPST            = $dados->pFCPST ? formataNumero($dados->pFCPST) : null;
                $std->vFCPST            = $dados->vFCPST ? formataNumero($dados->vFCPST) : null;
            }

            $std->vICMSDeson            = $dados->vICMSDeson ? formataNumero($dados->vICMSDeson) : null;
            $std->motDesICMS            = $dados->motDesICMS;
        }elseif($cst=="101"){
            $std->pCredSN               = $dados->pCredSN ? formataNumero($dados->pCredSN) : null;
            $std->vCredICMSSN           = $dados->vCredICMSSN ? formataNumero($dados->vCredICMSSN) : null;
        }elseif($cst=="102" || $cst=="103" || $cst=="300" || $cst=="400"){
            $std->vBC               = 0;
            $std->pICMS             = 0;
            $std->vICMS             = 0;

        }elseif($cst=="201"){
            $std->modBCST           = $dados->modBCST;
            $std->pMVAST            = $dados->pMVAST ? formataNumero($dados->pMVAST) : null;
            $std->pRedBCST          = $dados->pRedBCST ? formataNumero($dados->pRedBCST) : null;
            $std->vBCST             = $dados->vBCST ? formataNumero($dados->vBCST) : null;
            $std->pICMSST           = $dados->pICMSST ? formataNumero($dados->pICMSST) : null;
            $std->vICMSST           = $dados->vICMSST ? formataNumero($dados->vICMSST) : null;

            if($dados->vFCPST){
                $std->vBCFCPST          = $dados->vBCFCPST ? formataNumero($dados->vBCFCPST) : null;
                $std->pFCPST            = $dados->pFCPST ? formataNumero($dados->pFCPST) : null;
                $std->vFCPST            = $dados->vFCPST ? formataNumero($dados->vFCPST) : null;
            }

            if($std->pCredSN){
                $std->pCredSN               = $dados->pCredSN ? formataNumero($dados->pCredSN) : null;
                $std->vCredICMSSN           = $dados->vCredICMSSN ? formataNumero($dados->vCredICMSSN) : null;
            }


        }elseif($cst=="202" || $cst=="203"){
            $std->modBCST               = $dados->modBCST;
            $std->pMVAST            = $dados->pMVAST ? formataNumero($dados->pMVAST) : null;
            $std->pRedBCST          = $dados->pRedBCST ? formataNumero($dados->pRedBCST) : null;
            $std->vBCST             = $dados->vBCST ? formataNumero($dados->vBCST) : null;
            $std->pICMSST           = $dados->pICMSST ? formataNumero($dados->pICMSST) : null;
            $std->vICMSST           = $dados->vICMSST ? formataNumero($dados->vICMSST) : null;

            if($dados->vFCPST){
                $std->vBCFCPST          = $dados->vBCFCPST ? formataNumero($dados->vBCFCPST) : null;
                $std->pFCPST            = $dados->pFCPST ? formataNumero($dados->pFCPST) : null;
                $std->vFCPST            = $dados->vFCPST ? formataNumero($dados->vFCPST) : null;
            }

        }elseif($cst=="500"){
            /*$std->vBCSTRet              = $item->vBCSTRet;
            $std->pST               	= $item->pST;
            $std->vICMSSubstituto       = $item->vICMSSubstituto;
            $std->vICMSSTRet            = $item->vICMSSTRet;

            $std->vBCFCPSTRet           = $item->vBCFCPSTRet;
            $std->pFCPSTRet             = $item->pFCPSTRet;
            $std->vFCPSTRet             = $item->vFCPSTRet;

            $std->pRedBCEfet           = $item->pRedBCEfet;
            $std->vBCEfet              = $item->vBCEfet;
            $std->pICMSEfet              = $item->pICMSEfet;
            $std->vICMSEfet              = $item->vICMSEfet;*/
        }elseif($cst=="900"){
            $std->modBC                 = $dados->modBC;
            $std->pRedBC            = $dados->pRedBC ? formataNumero($dados->pRedBC) : null;
            $std->vBC               = $dados->vBC ? formataNumero($dados->vBC) : null;
            $std->pICMS             = $dados->pICMS ? formataNumero($dados->pICMS) : null;
            $std->vICMS             = $dados->vICMS ? formataNumero($dados->vICMS) : null;


            $std->modBCST               = $dados->modBCST;
            $std->pMVAST            = $dados->pMVAST ? formataNumero($dados->pMVAST) : null;
            $std->pRedBCST          = $dados->pRedBCST ? formataNumero($dados->pRedBCST) : null;
            $std->vBCST             = $dados->vBCST ? formataNumero($dados->vBCST) : null;
            $std->pICMSST           = $dados->pICMSST ? formataNumero($dados->pICMSST) : null;
            $std->vICMSST           = $dados->vICMSST ? formataNumero($dados->vICMSST) : null;

            if($dados->vFCPST){
                $std->vBCFCPST          = $dados->vBCFCPST ? formataNumero($dados->vBCFCPST) : null;
                $std->pFCPST            = $dados->pFCPST ? formataNumero($dados->pFCPST) : null;
                $std->vFCPST            = $dados->vFCPST ? formataNumero($dados->vFCPST) : null;
            }

            if($std->pCredSN){
                $std->pCredSN               = $dados->pCredSN ? formataNumero($dados->pCredSN) : null;
                $std->vCredICMSSN           = $dados->vCredICMSSN ? formataNumero($dados->vCredICMSSN) : null;
            }

        }
        if(intval($dados->CST) < 99){
            $nfe->tagICMS($std);
        }else
            $nfe->tagICMSSN($std); 
            
    }
    public  function calculo($icms){
        if($icms->CST=="00"){
            $icms->vICMS    = $icms->vBC * ($icms->pICMS / 100);

            $this->calculoFcp($icms);
        }else if($icms->CST=="10"){
            $icms->vICMS    = $icms->vBC * ($icms->pICMS / 100);
            $this->calculoSt($icms);
            $this->calculoFcp($icms);
            $this->calculoFcpSt($icms);
        }else if($icms->CST=="20"){
            $icms->vBC          = $icms->vBC - ($icms->pRedBC/100 * $icms->vBC);
            $icms->vICMS        = $icms->vBC * ($icms->pICMS / 100);

            $icms->vICMSDeson   = $icms->vBC * ($icms->pIcms / 100);
            $this->calculoFcp($icms);
        }else if($icms->CST=="30"){
            $icms->vICMS    = $icms->vBC * ($icms->pICMS / 100);
            $this->calculoSt($icms);
            $icms->vICMSDeson   = $icms->vBC * ($icms->pIcms / 100);
            $this->calculoFcp($icms);
            $this->calculoFcpSt($icms);
        }else if($icms->CST=="40" || $icms->cstICMS=="41" || $icms->cstICMS=="50" ){

            $icms->vICMSDeson   = $icms->vBC * ($icms->pIcms / 100);
        }else if($icms->CST=="51"){
            $icms->vICMSOp   = $icms->vBC * ($icms->pICMS / 100);
            $icms->vICMSDif  = $icms->vICMSOp * ($icms->pDif / 100);
            $icms->vICMS     = $icms->vICMSOp  - $icms->vICMSDif;
        }else if($icms->CST=="60"  ){
            //pesquisar a respeito
             $icms->vICMSDeson   = $icms->vBC * ($icms->pIcms / 100);
         
        }else if($icms->CST=="61"  ){
            //pesquisar a respeito
            $icms->vICMSSTRet = $icms->vBC * ($icms->pICMSST / 100);
         }else if($icms->CST=="70" ){
             $icms->vICMSDeson   = $icms->vBC * ($icms->pIcms / 100);
             if($icms->pRedBC){
                 $icms->vBC      = $icms->vBC - ($icms->pRedBC/100 * $icms->vBC);
             }
             $icms->vICMS        = $icms->vBC * ($icms->pICMS / 100);
             $this->calculoSt($icms);
         }else if($icms->CST=="90" ){
            //montar depois
         }else if($icms->CST=="101" ){
            $icms->vCredICMSSN  = $icms->vBC * ($icms->pCredSN / 100);
         }else if($icms->CST=="201" ){
            $icms->vCredICMSSN  = $icms->vBC * ($icms->pCredSN / 100);
            $this->calculoSt($icms);
         }else if($icms->CST=="202" ){
            $icms->vICMS    = $icms->vBC * ($icms->pICMS / 100);
            $this->calculoSt($icms);
         }else if($icms->CST=="500" ){
            //montar depois
         }else if($icms->CST=="900" ){
            //montar depois
         }
    }

    public function calculoSt($icms){
        //Base do ICMS ST
        $base_calculo  = $icms->vBC + $icms->ipi;
        $icms->vBCST   = $base_calculo * (1 + ($icms->pMVAST/100));
        if($icms->modBCST==5){
            $icms->vBCST    = $icms->valor_pauta * $icms->qtde_produto_pauta;
        }

        //Se tiver redução de base
        if($icms->pRedBCST){
            $icms->vBCST    = $icms->vBCST - ($icms->pRedBCST/100 * $icms->vBCST) ;
        }

        $vICMSST            = $icms->vBCST * $icms->pICMSST * 0.01 - $icms->vICMS;
        $icms->vICMSST      = ($vICMSST<=0) ? 0 : $vICMSST;
        $icms->vST          = $icms->vICMSST;

    }
    public  function calculoFcp($icms){
        if($icms->pFCP){
            $icms->vBCFCP   = $icms->vBC;
            $icms->vFCP     = $icms->vBCFCP * ($icms->pFCP / 100);
        }
    }

    public  function calculoFcpSt($icms){
        if($icms->pFCPST){
            $icms->vBCFCPST  = $icms->vBCST;
            $icms->vFCPST      = $icms->vBCFCPST * ($icms->pFCPST / 100);
        }
    }

}

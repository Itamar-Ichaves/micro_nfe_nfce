<?php

namespace App\Service;

use App\Models\CofinsNfe;
use App\Models\IcmsNfe;
use App\Models\IpiNfe;
use App\Models\PisNfe;

class ValidarImpostoNfeService{
    public static function validarIcms($array){
       $dados = (object) $array;
        if(!isset($dados->orig) ||  is_null($dados->orig)) {
            throw new \Exception('O Campo orig do node Icms é Obrigatório');
        }

        if(!isset($dados->CST) || is_null($dados->CST)) {
            throw new \Exception('O Campo CST do node Icms é Obrigatório');
        }
        $cst                        = $dados->CST;

        if($cst=="00"){
            if(!isset($dados->modBC) ||  is_null($dados->modBC)) {
                throw new \Exception('O Campo modBC do node Icms é Obrigatório');
            }
            if(!isset($dados->vBC) ||  is_null($dados->vBC)) {
                throw new \Exception('O Campo vBC do node Icms é Obrigatório');
            }
            if(!isset($dados->pICMS) ||  is_null($dados->pICMS)) {
                throw new \Exception('O Campo pICMS do node Icms é Obrigatório');
            }

        }elseif($cst=="10"){
            if(!isset($dados->modBC) || is_null($dados->modBC)) {
                throw new \Exception('O Campo modBC do node Icms é Obrigatório');
            }
            if(!isset($dados->vBC) || is_null($dados->vBC)) {
                throw new \Exception('O Campo vBC do node Icms é Obrigatório');
            }
            if(!isset($dados->pICMS) ||  is_null($dados->pICMS)) {
                throw new \Exception('O Campo pICMS do node Icms é Obrigatório');
            }
            if(!isset($dados->modBCST) ||  is_null($dados->modBCST)) {
                throw new \Exception('O Campo modBCST do node Icms é Obrigatório');
            }

             //Cálculo pela pauta
             if($dados->modBCST ==5){
                if(!isset($dados->valor_pauta) ||  is_null($dados->valor_pauta)) {
                    throw new \Exception('O Campo valor_pauta do node ICMS é Obrigatório para este tipo de modalidade');
                }

                if(!isset($dados->qtde_produto_pauta) ||  is_null($dados->qtde_produto_pauta)) {
                    throw new \Exception('O Campo qtde_produto_pauta do node ICMS é Obrigatório para este tipo de modalidade');
                }
            }else {
                if(!isset($dados->pMVAST) ||  is_null($dados->pMVAST)) {
                    throw new \Exception('O Campo pMVAST do node Icms é Obrigatório');
                }
            }

            if(!isset($dados->pICMSST) ||  is_null($dados->pICMSST)) {
                throw new \Exception('O Campo pICMSST do node Icms é Obrigatório');
            }
        }elseif($cst=="20"){
            if(!isset($dados->modBC) || is_null($dados->modBC)) {
                throw new \Exception('O Campo modBC do node Icms é Obrigatório');
            }
            if(!isset($dados->pRedBC) ||  is_null($dados->pRedBC)) {
                throw new \Exception('O Campo pRedBC do node Icms é Obrigatório');
            }
            if(!isset($dados->vBC) ||  is_null($dados->vBC)) {
                throw new \Exception('O Campo vBC do node Icms é Obrigatório');
            }
            if(!isset($dados->pICMS) ||  is_null($dados->pICMS)) {
                throw new \Exception('O Campo pICMS do node Icms é Obrigatório');
            }

        }elseif($cst=="30"){
            if(!isset($dados->modBCST) || is_null($dados->modBCST)) {
                throw new \Exception('O Campo modBCST do node Icms é Obrigatório');
            }
            if(!isset($dados->pICMSST) ||  is_null($dados->pICMSST)) {
                throw new \Exception('O Campo pICMSST do node Icms é Obrigatório');
            }
        }elseif($cst=="40" ){
            //
        }elseif($cst=="41"  ){
          /*  if(!isset($dados->vBCSTRet) || blank($dados->vBCSTRet)  || is_null($dados->vBCSTRet)) {
                throw new \Exception('O Campo vBCSTRet do node Icms é Obrigatório');
            }
            if(!isset($dados->vBCSTDest) || blank($dados->vBCSTDest)  || is_null($dados->vBCSTDest)) {
                throw new \Exception('O Campo vBCST do node Icms é Obrigatório');
            }*/
        }elseif($cst=="50" ){
            //
        }elseif($cst=="51"){
            //
        }elseif($cst=="60"){
            // ??
            if(!isset($dados->vBCSTRet) ||  is_null($dados->vBCSTRet)) {
                throw new \Exception('O Campo vBCSTRet do node Icms é Obrigatório');
            }
        }elseif($cst=="70"){
            if(!isset($dados->modBC) ||  is_null($dados->modBC)) {
                throw new \Exception('O Campo modBC do node Icms é Obrigatório');
            }
            if(!isset($dados->pRedBC) ||  is_null($dados->pRedBC)) {
                throw new \Exception('O Campo pRedBC do node Icms é Obrigatório');
            }
            if(!isset($dados->vBC) ||  is_null($dados->vBC)) {
                throw new \Exception('O Campo vBC do node Icms é Obrigatório');
            }

            if(!isset($dados->pICMS) || is_null($dados->pICMS)) {
                throw new \Exception('O Campo pICMS do node Icms é Obrigatório');
            }
            if(!isset($dados->modBCST) ||  is_null($dados->modBCST)) {
                throw new \Exception('O Campo modBCST do node Icms é Obrigatório');
            }
            if(!isset($dados->pMVAST) || is_null($dados->pMVAST)) {
                throw new \Exception('O Campo pMVAST do node Icms é Obrigatório');
            }

            if(!isset($dados->pICMSST) ||  is_null($dados->pICMSST)) {
                throw new \Exception('O Campo pICMSST do node Icms é Obrigatório');
            }

        }elseif($cst=="90"){
            //
        }elseif($cst=="101"){
            if(!isset($dados->pCredSN) || is_null($dados->pCredSN)) {
                throw new \Exception('O Campo pCredSN do node Icms é Obrigatório');
            }
        }elseif($cst=="102"){
            //
        }elseif($cst=="103"){
            //
        }elseif($cst=="201"){
            if(!isset($dados->modBCST) ||  is_null($dados->modBCST)) {
                throw new \Exception('O Campo modBCST do node Icms é Obrigatório');
            }
            if(!isset($dados->pMVAST) ||  is_null($dados->pMVAST)) {
                throw new \Exception('O Campo pMVAST do node Icms é Obrigatório');
            }
            if(!isset($dados->pICMSST) ||  is_null($dados->pICMSST)) {
                throw new \Exception('O Campo pICMSST do node Icms é Obrigatório');
            }
            if(!isset($dados->pCredSN) ||  is_null($dados->pCredSN)) {
                throw new \Exception('O Campo pCredSN do node Icms é Obrigatório');
            }
        }elseif($cst=="202"){
            if(!isset($dados->modBCST) ||  is_null($dados->modBCST)) {
                throw new \Exception('O Campo modBCST do node Icms é Obrigatório');
            }
            if(!isset($dados->pMVAST) ||  is_null($dados->pMVAST)) {
                throw new \Exception('O Campo pMVAST do node Icms é Obrigatório');
            }
            if(!isset($dados->pICMSST) ||  is_null($dados->pICMSST)) {
                throw new \Exception('O Campo pICMSST do node Icms é Obrigatório');
            }

        }elseif($cst=="203"){
            if(!isset($dados->modBCST) ||  is_null($dados->modBCST)) {
                throw new \Exception('O Campo modBCST do node Icms é Obrigatório');
            }
            if(!isset($dados->pMVAST) ||  is_null($dados->pMVAST)) {
                throw new \Exception('O Campo pMVAST do node Icms é Obrigatório');
            }

            if(!isset($dados->pICMSST) ||  is_null($dados->pICMSST)) {
                throw new \Exception('O Campo pICMSST do node Icms é Obrigatório');
            }

        }elseif($cst=="400"){
            //
        }elseif($cst=="500"){
            if(!isset($dados->vBCSTRet) ||  is_null($dados->vBCSTRet)) {
                throw new \Exception('O Campo vBCSTRet do node Icms é Obrigatório');
            }
        }

        $icms = new IcmsNfe();
        $icms->setarDados($array);
        $icms->calculo($icms);
        return $icms;
    }
    public static function validarIpi($array){
        $dados = (object) $array;

        if(!isset($dados->CST) ||  is_null($dados->CST)) {
            throw new \Exception('O Campo CST do node IPI é Obrigatório');
        }

        if(!isset($dados->cEnq) ||  is_null($dados->cEnq)) {
            throw new \Exception('O Campo cEnq do node IPI é Obrigatório');
        }

        if($dados->CST=="00" || $dados->CST=="49" || $dados->CST=="50" || $dados->CST=="99" ){
            if(!isset($dados->tipo_calculo) ||  is_null($dados->tipo_calculo)) {
                throw new \Exception('Você precisa definir um valor para o campo tipo_calculo, podendo ser: 1 - para Cálculo por Alíquota e 2 - para Cálculo por Unidade');
            }
            if($dados->tipo_calculo==1){
                if(!isset($dados->vBC) ||  is_null($dados->vBC)) {
                    throw new \Exception('O campo vBC do Node IPI é Obrigatório');
                }
                if(!isset($dados->pIPI) ||  is_null($dados->pIPI)) {
                    throw new \Exception('O campo pIPI do Node IPI é Obrigatório');
                }
            }else if($dados->tipo_calculo==2){
                if(!isset($dados->qUnid) ||  is_null($dados->qUnid)) {
                    throw new \Exception('O campo qUnid do Node IPI é Obrigatório');
                }
                if(!isset($dados->vUnid) ||  is_null($dados->vUnid)) {
                    throw new \Exception('O campo vUnid do Node IPI é Obrigatório');
                }
            }else{
                throw new \Exception('O campo tipo_calculo do Node IPI só pode receber os valores 1 ou 2');
            }
        }

        $ipi = new IpiNfe();
        $ipi->setarDados($array);
        $ipi->calculo($ipi);
        return $ipi;
    }

    public static function validarPis($array){
        $dados = (object) $array;
        if(!isset($dados->CST) ||  is_null($dados->CST)) {
            throw new \Exception('O Campo CST do node PIS é Obrigatório');
        }
        if($dados->CST=="01" || $dados->CST=="02" ){
            if(!isset($dados->vBC) ||  is_null($dados->vBC)) {
                throw new \Exception('O campo vBC do Node PIS é Obrigatório');
            }
            if(!isset($dados->pPIS) ||  is_null($dados->pPIS)) {
                throw new \Exception('O campo pPIS do Node PIS é Obrigatório');
            }
        }else if($dados->CST=="03"  ){
            if(!isset($dados->qBCProd) ||  is_null($dados->qBCProd)) {
                throw new \Exception('O campo qBCProd do Node PIS é Obrigatório');
            }
            if(!isset($dados->vAliqProd) ||  is_null($dados->vAliqProd)) {
                throw new \Exception('O campo vAliqProd do Node PIS é Obrigatório');
            }
        }elseif($dados->CST=="99"  ){
            if(!isset($dados->tipo_calculo) ||  is_null($dados->tipo_calculo)) {
                throw new \Exception('Você precisa definir um valor para o campo tipo_calculo, podendo ser: 1 - para Cálculo por Alíquota e 2 - para Cálculo por Unidade');
            }
            if($dados->tipo_calculo==1){
                if(!isset($dados->vBC) ||  is_null($dados->vBC)) {
                    throw new \Exception('O campo vBC do Node PIS é Obrigatório');
                }
                if(!isset($dados->pPIS) ||  is_null($dados->pPIS)) {
                    throw new \Exception('O campo pPIS do Node PIS é Obrigatório');
                }
            }else if($dados->tipo_calculo==2){
                if(!isset($dados->qBCProd) ||  is_null($dados->qBCProd)) {
                    throw new \Exception('O campo qBCProd do Node PIS é Obrigatório');
                }
                if(!isset($dados->vAliqProd) ||  is_null($dados->vAliqProd)) {
                    throw new \Exception('O campo vAliqProd do Node PIS é Obrigatório');
                }
            }else{
                throw new \Exception('O campo tipo_calculo do Node PIS só pode receber os valores 1 ou 2');
            }
        }

        $pis = new PisNfe();
        $pis->setarDados($array);
        $pis->calculo($pis);
        return $pis;
    }

    public static function validarCofins($array){
        $dados = (object) $array;
        if(!isset($dados->CST) ||  is_null($dados->CST)) {
            throw new \Exception('O Campo CST do node COFINS é Obrigatório');
        }
        if($dados->CST=="01" || $dados->CST=="02" ){
            if(!isset($dados->vBC) ||  is_null($dados->vBC)) {
                throw new \Exception('O campo vBC do Node COFINS é Obrigatório');
            }
            if(!isset($dados->pCOFINS) ||  is_null($dados->pCOFINS)) {
                throw new \Exception('O campo pCOFINS do Node COFINS é Obrigatório');
            }
        }else if($dados->CST=="03"  ){
            if(!isset($dados->qBCProd) ||  is_null($dados->qBCProd)) {
                throw new \Exception('O campo qBCProd do Node COFINS é Obrigatório');
            }
            if(!isset($dados->vAliqProd) ||  is_null($dados->vAliqProd)) {
                throw new \Exception('O campo vAliqProd do Node COFINS é Obrigatório');
            }
        }elseif($dados->CST=="99"  ){
            if(!isset($dados->tipo_calculo) || is_null($dados->tipo_calculo)) {
                throw new \Exception('Você precisa definir um valor para o campo tipo_calculo, podendo ser: 1 - para Cálculo por Alíquota e 2 - para Cálculo por Unidade');
            }
            if($dados->tipo_calculo==1){
                if(!isset($dados->vBC) || is_null($dados->vBC)) {
                    throw new \Exception('O campo vBC do Node COFINS é Obrigatório');
                }
                if(!isset($dados->pCOFINS) ||  is_null($dados->pCOFINS)) {
                    throw new \Exception('O campo pCOFINS do Node COFINS é Obrigatório');
                }
            }else if($dados->tipo_calculo==2){
                if(!isset($dados->qBCProd) ||  is_null($dados->qBCProd)) {
                    throw new \Exception('O campo qBCProd do Node COFINS é Obrigatório');
                }
                if(!isset($dados->vAliqProd) ||  is_null($dados->vAliqProd)) {
                    throw new \Exception('O campo vAliqProd do Node COFINS é Obrigatório');
                }
            }else{
                throw new \Exception('O campo tipo_calculo do Node COFINS só pode receber os valores 1 ou 2');
            }
        }

        $cofins = new CofinsNfe();
        $cofins->setarDados($array);
        $cofins->calculo($cofins);
        return $cofins;
    }



}

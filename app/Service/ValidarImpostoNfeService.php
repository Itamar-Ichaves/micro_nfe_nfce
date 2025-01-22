<?php

namespace App\Service;

use App\Models\CofinsNfe;
use App\Models\IcmsNfe;
use App\Models\IpiNfe;
use App\Models\PisNfe;

class ValidarImpostoNfeService
{
    public static function validarIcms($array)
    {
        $dados = (object) $array;

        // Validações gerais
        self::validarCampoObrigatorio($dados, 'orig', 'O campo orig do node Icms é obrigatório');
        self::validarCampoObrigatorio($dados, 'CST', 'O campo CST do node Icms é obrigatório');

        // Verificação específica por CST
        $cst = $dados->CST;

        switch ($cst) {
            case "00":
                self::validarCamposObrigatorios($dados, [
                    'modBC' => 'O campo modBC do node Icms é obrigatório',
                    'vBC' => 'O campo vBC do node Icms é obrigatório',
                    'pICMS' => 'O campo pICMS do node Icms é obrigatório',
                ]);
                break;

            case "10":
                self::validarCamposObrigatorios($dados, [
                    'modBC' => 'O campo modBC do node Icms é obrigatório',
                    'vBC' => 'O campo vBC do node Icms é obrigatório',
                    'pICMS' => 'O campo pICMS do node Icms é obrigatório',
                    'modBCST' => 'O campo modBCST do node Icms é obrigatório',
                ]);
                if ($dados->modBCST == 5) {
                    self::validarCamposObrigatorios($dados, [
                        'valor_pauta' => 'O campo valor_pauta do node ICMS é obrigatório para este tipo de modalidade',
                        'qtde_produto_pauta' => 'O campo qtde_produto_pauta do node ICMS é obrigatório para este tipo de modalidade',
                    ]);
                } else {
                    self::validarCampoObrigatorio($dados, 'pMVAST', 'O campo pMVAST do node Icms é obrigatório');
                }
                self::validarCampoObrigatorio($dados, 'pICMSST', 'O campo pICMSST do node Icms é obrigatório');
                break;

            case "20":
                self::validarCamposObrigatorios($dados, [
                    'modBC' => 'O campo modBC do node Icms é obrigatório',
                    'pRedBC' => 'O campo pRedBC do node Icms é obrigatório',
                    'vBC' => 'O campo vBC do node Icms é obrigatório',
                    'pICMS' => 'O campo pICMS do node Icms é obrigatório',
                ]);
                break;

            case "60":
                self::validarCamposObrigatorios($dados, [
                    'vBCSTRet' => 'O campo vBCSTRet do node Icms é obrigatório',
                ]);
                break;

            case "70":
                self::validarCamposObrigatorios($dados, [
                    'modBC' => 'O campo modBC do node Icms é obrigatório',
                    'pRedBC' => 'O campo pRedBC do node Icms é obrigatório',
                    'vBC' => 'O campo vBC do node Icms é obrigatório',
                    'pICMS' => 'O campo pICMS do node Icms é obrigatório',
                    'modBCST' => 'O campo modBCST do node Icms é obrigatório',
                    'pMVAST' => 'O campo pMVAST do node Icms é obrigatório',
                    'pICMSST' => 'O campo pICMSST do node Icms é obrigatório',
                ]);
                break;

            case "61":
                self::validarCamposObrigatorios($dados, [
                    'vBCSTRet' => 'O campo vBCSTRet do node Icms é obrigatório para CST 61',
                    'pST' => 'O campo pST do node Icms é obrigatório para CST 61',
                    'vICMSSTRet' => 'O campo vICMSSTRet do node Icms é obrigatório para CST 61',
                    'vBCFCPSTRet' => 'O campo vBCFCPSTRet do node Icms é obrigatório para CST 61',
                    'pFCPSTRet' => 'O campo pFCPSTRet do node Icms é obrigatório para CST 61',
                    'vFCPSTRet' => 'O campo vFCPSTRet do node Icms é obrigatório para CST 61',
                ]);
                break;

            case "500":
                self::validarCampoObrigatorio($dados, 'vBCSTRet', 'O campo vBCSTRet do node Icms é obrigatório');
                break;

            case "101":
                self::validarCampoObrigatorio($dados, 'pCredSN', 'O campo pCredSN do node Icms é obrigatório');
                break;

            case "102":
                // Não exige validações adicionais, mas pode garantir que o "orig" esteja presente
                self::validarCampoObrigatorio($dados, 'orig', 'O campo orig do node Icms é obrigatório para CST 102');
                break;
                
            case "500":
                self::validarCamposObrigatorios($dados, [
                'vBCSTRet' => 'O campo vBCSTRet do node Icms é obrigatório para CST 500',
                 'vICMSSTRet' => 'O campo vICMSSTRet do node Icms é obrigatório para CST 500',
                ]);

                break;
                
            default:
                // CSTs que não precisam de validação específica
                break;
        }

        // Calcular e retornar o ICMS
        $icms = new IcmsNfe();
        $icms->setarDados($array);
        $icms->calculo($icms);
        return $icms;
    }

    private static function validarCampoObrigatorio($dados, $campo, $mensagem)
    {
        if (!isset($dados->$campo) || is_null($dados->$campo)) {
            throw new \Exception($mensagem);
        }
    }

    private static function validarCamposObrigatorios($dados, $campos)
    {
        foreach ($campos as $campo => $mensagem) {
            self::validarCampoObrigatorio($dados, $campo, $mensagem);
        }
    }


    public static function validarIpi(array $array): IpiNfe
{
    $dados = (object) $array;

    if (empty($dados->CST)) {
        throw new \Exception('O Campo CST do node IPI é obrigatório.');
    }

    if (empty($dados->cEnq)) {
        throw new \Exception('O Campo cEnq do node IPI é obrigatório.');
    }

    if (in_array($dados->CST, ['00', '49', '50', '99'], true)) {
        if (empty($dados->tipo_calculo)) {
            throw new \Exception('Você precisa definir um valor para o campo tipo_calculo: 1 (Cálculo por Alíquota) ou 2 (Cálculo por Unidade).');
        }

        if ($dados->tipo_calculo == 1) {
            if (empty($dados->vBC)) {
                throw new \Exception('O campo vBC do Node IPI é obrigatório.');
            }
            if (empty($dados->pIPI)) {
                throw new \Exception('O campo pIPI do Node IPI é obrigatório.');
            }
        } elseif ($dados->tipo_calculo == 2) {
            if (empty($dados->qUnid)) {
                throw new \Exception('O campo qUnid do Node IPI é obrigatório.');
            }
            if (empty($dados->vUnid)) {
                throw new \Exception('O campo vUnid do Node IPI é obrigatório.');
            }
        } else {
            throw new \Exception('O campo tipo_calculo do Node IPI deve ser 1 (Cálculo por Alíquota) ou 2 (Cálculo por Unidade).');
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

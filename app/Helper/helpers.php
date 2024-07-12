<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

function i($array){
    echo "<pre>";
    print_r($array);
    echo "</pre>";
    exit;
}
function objToArray($objeto){
    return is_array($objeto) ? $objeto : (array) $objeto;
}
function hoje(){
    return date("Y-m-d");
}


function formataNumeroBr($numero, $dec = 2)
{
    return number_format( $numero, $dec, ',', '.' );
}

function enviarPostJsonCurl($url, $dados){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_HTTPHEADER,
        array(
            'Content-Type:application/json',
            'Content-Length: ' . strlen($dados)
        )
        );

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function enviarPostCurl($url, $dados){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}
function enviarPutCurl($url, $dados){
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_PUT, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $dados);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

    $result = curl_exec($ch);
    curl_close($ch);

    return $result;
}

function enviarGetCurlSDecode($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $json = curl_exec($ch);

    curl_close($ch);

    return $json;
}



function validarCpfCnpj($valor){
    $validator = Validator::make(
        ['cnpj' => $valor],
        ['cnpj' => 'required|cnpj']
        );
    if ($validator->fails()) {
        return false;
    }
    return true;
}
function somarData($data, $dias=0, $meses=0, $ano=0, $opcao=1 ){
    $data = extrair_data($data, $opcao);
    $resData2 = date("Y-m-d", mktime(0, 0, 0,$data[1] + $meses,   $data[0] + $dias, $data[2] + $ano));
    return $resData2;
}


function zeroEsquerda($str, $qtde){
    return str_pad($str, $qtde,'0',STR_PAD_LEFT);
}
function agora(){
    return date("H:i:s");
}

function databr($value, $format = 'd/m/Y'){
    return Carbon::parse($value)->format($format);
}

function getFloat($valor, $simbolo = null, $casasDecimais = 2){
    if($valor){
        $valor = verificaValor($valor);
    if (isset($casasDecimais))
        return (float) number_format($valor, $casasDecimais,'.','');
    else
        return (float) $valor;
    }

}

function verificaValor($val, $excep = false){
    $val = preg_replace('/[^\d\.\,]+/', '', $val);
    // Inteiro
    if (preg_match('/^\d+$/', $val)) {
        $valor = (float) $val;
    } else
        // Float
        if (preg_match('/^\d+\.{1}\d+$/', $val)) {
            $valor = (float) $val;
        } else{
            // Vírgula como separador decimal
            if (preg_match('/^[\d\.]+\,{1}\d+$/', $val)) {
                $valor = (float) str_replace(',','.', str_replace('.', '', $val));
            } else {        // Formato inválido ou em branco
                if($excep)
                   // throw new \Exception('Moeda em formato inválido ou desconhecido.');
                    $valor = 0;
            }
        }
     return $valor;
}
function dataNfe($data){
    return substr($data,0,10);;
}

function enviarGetCurl($url){
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
    $json = curl_exec($ch);

    $resultado = json_decode($json);
    curl_close($ch);

    return $resultado;
}

function horaNfe($data){
    return substr($data,11,8);;
}

function tiraAcento($str){
    $comAcentos = array('à', 'á', 'â', 'ã', 'ä', 'å', 'ç', 'è', 'é', 'ê', 'ë', 'ì', 'í', 'î', 'ï', 'ñ', 'ò', 'ó', 'ô', 'õ', 'ö', 'ù', 'ü', 'ú', 'ÿ', 'À', 'Á', 'Â', 'Ã', 'Ä', 'Å', 'Ç', 'È', 'É', 'Ê', 'Ë', 'Ì', 'Í', 'Î', 'Ï', 'Ñ', 'Ò', 'Ó', 'Ô', 'Õ', 'Ö', 'O', 'Ù', 'Ü', 'Ú');
    $semAcentos = array('a', 'a', 'a', 'a', 'a', 'a', 'c', 'e', 'e', 'e', 'e', 'i', 'i', 'i', 'i', 'n', 'o', 'o', 'o', 'o', 'o', 'u', 'u', 'u', 'y', 'A', 'A', 'A', 'A', 'A', 'A', 'C', 'E', 'E', 'E', 'E', 'I', 'I', 'I', 'I', 'N', 'O', 'O', 'O', 'O', 'O', '0', 'U', 'U', 'U');
    return str_replace($comAcentos, $semAcentos, $str);
}

function formataNumero($number, $dec = 2)
{
    return number_format((float) $number, $dec, ".", "");
}

//função limata caracteres
function limita_caracteres($texto, $limite, $quebra = true){
    $tamanho = strlen($texto);
    if($tamanho <= $limite){ //Verifica se o tamanho do texto é menor ou igual ao limite
        $novo_texto = $texto;
    }else{ // Se o tamanho do texto for maior que o limite
        if($quebra == true){ // Verifica a opção de quebrar o texto
            $novo_texto = trim(substr($texto, 0, $limite))."...";
        }else{ // Se não, corta $texto na última palavra antes do limite
            $ultimo_espaco = strrpos(substr($texto, 0, $limite), " "); // Localiza o útlimo espaço antes de $limite
            $novo_texto = trim(substr($texto, 0, $ultimo_espaco))."..."; // Corta o $texto até a posição localizada
        }
    }
    return $novo_texto; // Retorna o valor formatado
}

function extrair_data($data, $opcao=1){
    //Opção 1-EN 2-BR
    if ($opcao==1){
        $dia = substr($data,8,2);
        $mes = substr($data,5,2);
        $ano = substr($data,0,4);
    }
    else{
        $dia = substr($data,0,2);
        $mes = substr($data,3,2);
        $ano = substr($data,6,4);
    }
    return array($dia,$mes,$ano);
}

//Transforma data do formato Brasileiro para o Inglês
function dataEn($data){
    $data = extrair_data($data, 0);
    return $data[2] . "-" .$data[1] ."-" .$data[0];
}

function tira_mascara($valor){
    return  preg_replace("/\D+/", "", $valor);
}

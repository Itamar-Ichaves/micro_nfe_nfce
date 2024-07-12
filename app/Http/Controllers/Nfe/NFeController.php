<?php

namespace App\Http\Controllers\Nfe;

use App\Http\Controllers\Controller;
use app\Service\NfeService;
use App\Service\ValidaDadosNfeService;
use Illuminate\Http\Request;

class NFeController extends Controller
{
    public function transmitir(Request $request){
        //dd($request);
        $dados = ($request->all());
        $dados_validos = ValidaDadosNfeService::validaDadosNfe($dados);
     
        if($dados_validos->tem_erro){
            i($dados_validos);
        }
    //   i($dados_validos);
        $xml = NfeService::gerarNfe($dados_validos->notafiscal);
        return response()->json($xml);
}
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pagamento extends Model
{
    use HasFactory;

    public $tPag;
    public $vPag;
    public $indPag;

    public function setarDados( $data){
        $this->tPag      = $data['tPag'] ?? null;
        $this->vPag      = $data['vPag'] ?? null;
        $this->indPag    = $data['indPag'] ?? null;
    }

    public static function montarXml($nfe, $dadosPagamento, $dadosCartao){
        $std = new \stdClass();
        $std->tPag 			= zeroEsquerda($dadosPagamento->tPag,2);
        $std->vPag 		    = $dadosPagamento->vPag  ? formataNumero($dadosPagamento->vPag)  : null;
        $std->indPag 		= $dadosPagamento->indPag;
        
        $std->CNPJ 			= $dadosCartao->CNPJ  ? tira_mascara($dadosCartao->CNPJ)  : null;
        $std->tBand 		= $dadosCartao->tBand;
        $std->cAut 		    = $dadosCartao->cAut;
        $std->tpIntegra 	= $dadosCartao->tpIntegra;
        $std->vTroco 		= $dadosCartao->vTroco  ? formataNumero($dadosCartao->vTroco)  : null;
       
        $nfe->tagdetPag($std);
    }
}

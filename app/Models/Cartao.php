<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cartao extends Model
{
    
    public $CNPJ;
    public $tBand;
    public $cAut;
    public $tpIntegra;
    public $vTroco;


    public function setarDados( $data){
        $this->CNPJ      = $data['CNPJ'] ?? null;
        $this->tBand     = $data['tBand'] ?? null;
        $this->cAut      = $data['cAut'] ?? null;
        $this->tpIntegra = $data['tpIntegra'] ?? null;
        $this->vTroco    = $data['vTroco'] ?? null;
    }
}
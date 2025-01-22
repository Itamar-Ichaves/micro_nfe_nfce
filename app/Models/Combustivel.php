<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Combustivel extends Model
{
    
    public $cProdANP;
    public $descANP;
    public $pGLP;
    public $pGNn;
    public $pGNi;

    public $vPart;

    public $UFCons;


    public function setarDados( $data){
        $this->cProdANP      = $data['cProdANP'] ?? null;
        $this->descANP     = $data['descANP'] ?? null;
        $this->pGLP      = $data['pGLP'] ?? null;
        $this->pGNi      =   $data['pGNi'] ?? null;
        $this->pGNn    = $data['pGNn'] ?? null;
        $this->vPart    = $data['vPart'] ?? null;
        $this->UFCons    = $data['UFCons'] ?? null;
    }
}
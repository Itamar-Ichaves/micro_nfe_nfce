<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Volume extends Model
{
    use HasFactory;


    public $qVol;
    public $esp;
    public $marca;
    public $nVol;
    public $pesoL;
    public $pesoB;
    public $nLacre;


    public function setarDados( $data){
        $this->qVol       = $data['qVol'] ?? null;
        $this->esp        = $data['esp'] ?? null;
        $this->marca      = $data['marca'] ?? null;
        $this->nVol       = $data['nVol'] ?? null;
        $this->pesoL      = $data['pesoL'] ?? null;
        $this->pesoB      = $data['pesoB'] ?? null;
        $this->nLacre     = $data['nLacre'] ?? null;
    }

    public static function montarXml($nfe, $dados){
        $std                = new \stdClass();
        $std->item          = 1; //indicativo do numero do volume
        $std->qVol  		= $dados->qVol ;
        $std->esp 			= $dados->esp  ? tira_mascara($dados->esp)  : null;
        $std->marca		    = $dados->marca;
        $std->nVol 		    = $dados->nVol;
        $std->pesoL		    = $dados->pesoL  ? formataNumero($dados->pesoL)  : null;
        $std->pesoB		    = $dados->pesoB  ? formataNumero($dados->pesoB)  : null;
        $nfe->tagvol($std);

        if($dados->nLacre){
            $std = new \stdClass();
            $std->item = 1; //indicativo do numero do volume
            $std->nLacre = $dados->nLacre;
            $nfe->taglacres($std);
        }
    }
}

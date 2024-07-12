<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Destinatario extends Model
{
    use HasFactory;


    public $xNome;
    public $xFant;
    public $IE;
    public $IM;
    public $CNAE;
    public $CRT;
    public $CNPJ;
    public $CPF;
    public $xLgr;
    public $nro;
    public $xCpl;
    public $xBairro;
    public $cMun;
    public $xMun;
    public $UF;
    public $CEP;
    public $cPais;
    public $xPais;
    public $fone;

    public $indIEDest;
    public $ISUF;
    public $email;
    public $idEstrangeiro;
    public function setarDados( $data){
        $this->xNome      = $data['xNome'] ?? null;
        $this->xFant      = $data['xFant'] ?? null;
        $this->IE         = $data['IE'] ?? null;
        $this->IM         = $data['IM'] ?? null;
        $this->CNPJ       = $data['CNPJ'] ?? null;
        $this->CPF        = $data['CPF'] ?? null;
        $this->xLgr       = $data['xLgr'] ?? null;
        $this->nro        = $data['nro'] ?? null;
        $this->xCpl       = $data['xCpl'] ?? null;
        $this->xBairro    = $data['xBairro'] ?? null;
        $this->cMun       = $data['cMun'] ?? null;
        $this->xMun       = $data['xMun'] ?? null;
        $this->UF         = $data['UF'] ?? null;
        $this->CEP        = $data['CEP'] ?? null;
        $this->cPais      = $data['cPais'] ?? null;
        $this->xPais      = $data['xPais'] ?? null;
        $this->fone       = $data['fone'] ?? null;
        $this->indIEDest  = $data['indIEDest'] ?? null;
        $this->ISUF       = $data['ISUF'] ?? null;
        $this->email      = $data['email'] ?? null;
        $this->idEstrangeiro = $data['idEstrangeiro'] ?? null;
    }

    public static function montarXml($nfe, $dados){
        $std = new \stdClass();
        $std->xNome = tiraAcento(limita_caracteres($dados->xNome,56 )) 	;
        $std->indIEDest	= $dados->indIEDest	;
        $std->IE	= $dados->IE ? tira_mascara($dados->IE) : null		;
        $std->ISUF	= $dados->ISUF		;
        $std->IM	= $dados->IM		;
        $std->email	= $dados->email		;
        $std->idEstrangeiro= $dados->idEstrangeiro;

        if($dados->CNPJ):
            $std->CNPJ = tira_mascara($dados->CNPJ);
            $std->CPF = null;
        elseif($dados->CPF):
            $std->CNPJ = NULL;
            $std->CPF  = tira_mascara($dados->CPF);
        else:
            $std->CNPJ = null;
            $std->CPF = null;
        endif;

        $nfe->tagdest($std);

        //Endereço do destinatário
        $std = new \stdClass();
        $std->xLgr	= tiraAcento($dados->xLgr)		;
        $std->nro	= $dados->nro		;
        $std->xCpl	= tiraAcento($dados->xCpl)		;
        $std->xBairro= tiraAcento($dados->xBairro)	;
        $std->cMun	= $dados->cMun		;
        $std->xMun	= tiraAcento($dados->xMun)		;
        $std->UF	= $dados->UF		;
        $std->CEP	= tira_mascara($dados->CEP)		;
        $std->cPais	= $dados->cPais		;
        $std->xPais	= tiraAcento($dados->xPais)		;
        $std->fone	= $dados->fone		;

        $nfe->tagenderDest($std);

    }
}

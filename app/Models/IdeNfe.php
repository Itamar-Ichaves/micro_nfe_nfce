<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IdeNfe extends Model {
    public $cUF;
    public $nNF;
    public $cNF;
    public $natOp;
    public $indPag;
    public $mod;
    public $serie;
    public $dhEmi;
    public $dhSaiEnt;
    public $tpNF;
    public $idDest;
    public $cMunFG;
    public $tpImp;
    public $tpEmis;
    public $tpAmb;
    public $finNFe;
    public $indFinal;
    public $indPres;
    public $procEmi;
    public $verProc;
    public $dhCont;
    public $xJust;
    public $modFrete;

    public $dSaiEnt;

    public function setarDados($data) {
        $this->cUF      = $data->cUF ?? null;
        $this->nNF      = $data->nNF ?? null;
        $this->cNF      = $data->cNF ?? null;
        $this->natOp    = $data->natOp ?? null;
        $this->indPag   = $data->indPag ?? null;
        $this->mod      = $data->mod ?? null;
        $this->serie    = $data->serie ?? null;
        $this->dhEmi    = $data->dhEmi ?? null;
        $this->dhSaiEnt  = $data->dhSaiEnt ?? null;
        $this->tpNF     = $data->tpNF ?? null;
        $this->idDest   = $data->idDest ?? null;
        $this->cMunFG   = $data->cMunFG ?? null;
        $this->tpImp    = $data->tpImp ?? null;
        $this->tpEmis   = $data->tpEmis ?? null;
        $this->tpAmb    = $data->tpAmb ?? null;
        $this->finNFe   = $data->finNFe ?? null;
        $this->indFinal = $data->indFinal ?? null;
        $this->indPres  = $data->indPres ?? null;
        $this->procEmi  = $data->procEmi ?? null;
        $this->verProc  = $data->verProc ?? null;
        $this->dhCont   = $data->dhCont ?? null;
        $this->xJust    = $data->xJust ?? null;
        $this->modFrete = $data->modFrete ?? "9";
    }

    public static function montarXml($nfe, $dados) {
        $std = new \stdClass();
        $std->cUF = $dados->cUF;
        $std->nNF = $dados->nNF;
        $std->cNF = $dados->cNF ?? rand(11111, 99999);
        $std->natOp = tiraAcento($dados->natOp);
        $std->mod = $dados->mod;
        $std->serie = $dados->serie;
        $std->dhEmi = $dados->dhEmi ?? date("Y-m-d\TH:i:sP");
        $std->dhSaiEnt = $dados->dhSaiEnt ?? null;
        $std->tpNF = $dados->tpNF;
        $std->idDest = $dados->idDest;
        $std->cMunFG = $dados->cMunFG;
        $std->tpImp = $dados->tpImp;
        $std->tpEmis = $dados->tpEmis;
        $std->cDV = null;
        $std->tpAmb = $dados->tpAmb;
        $std->finNFe = $dados->finNFe;
        $std->indFinal = $dados->indFinal;
        $std->indPres = $dados->indPres;
        $std->indIntermed = $dados->indIntermed ?? null;
        $std->procEmi = $dados->procEmi;
        $std->verProc = $dados->verProc;
        $std->dhCont = $dados->dhCont ?? null;
        $std->xJust = $dados->xJust ?? null;
        $nfe->tagide($std);
    }
}

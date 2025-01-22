<?php

namespace App\Repository;

use App\Models\NotaNfe;
use DB;

class NfeRepository
{
    protected $table;
    public function __construct() 
    {
        $this->table = "notaNfe";
     }

    public function getNfe($id, $token_company, $token_emitente)
    {
        return DB::table($this->table)
            ->where('id', $id)
            ->where('token_company', $token_company)
            ->where('token_emitente', $token_emitente)
            ->first();
    }
}

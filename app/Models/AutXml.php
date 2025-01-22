<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutXml extends Model
{
    use HasFactory;

    public $CNPJ;

    public function setarDados($data)
    {  
        $this->CNPJ     = $data['CNPJ'] ?? null;
         
    }

   
    public static function montarXml($nfe, $autXML)
    {
        
        $std = new \stdClass();
        $std->CNPJ    = $autXML->CNPJ;
        
        $nfe->tagautXML($std);
    }

}

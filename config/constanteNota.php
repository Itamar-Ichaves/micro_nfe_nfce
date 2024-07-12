<?php
return [   
    'tpNf' => [
        'ENTRADA'       => 0, 
        'SAIDA'         => 1, 
    ],
    
    'idDest' => [
        'INTERNA'       => 1, 
        'INTERESTADUAL' => 2,
        'EXTERIOR'      => 3,
    ],
    
    'mod' => [
        'NFE'           => 55,
        'NFCE'          => 65,
    ],
    
    'tpAmb' => [
        'PRODUCAO'      => 1,
        'HOMOLOGACAO'   => 2,
    ],
    
    'tpImp' => [
        'SEM_DANFE'     => 0,
        'RETRATO'       => 1,
        'PAISAGEM'      => 2,
        'SIMPLIFICADO'  => 3,
        'DANFCE'        => 4
    ],
    
    'tpEmis' => [
        'NORMAL'                => 1,
        'CONTINGENCIA_FS'       => 2,
        'CONTINGENCIA_SCAN'     => 3,
        'CONTINGENCIA_EPEC'     => 4,
        'CONTINGENCIA_DA'       => 5,
        'CONTINGENCIA_SVC_AN'   => 6,
        'CONTINGENCIA_SVC_RS'   => 7,
        'CONTIGENCIA_NFCE'      => 9,
    ],
    
    'finNFe'   => [
        'NORMAL'        => 1,
        'COMPLEMENTAR'  => 2,
        'AJUSTE'        => 3,
        'DEVOLUCAO'     => 4,
    ],
    
    'indFinal' => [
        'NORMAL'            => 0,
        'CONSUMIDOR_FINAL'  => 1,
    ],
    
    'indPres'   => [
        'NAO_APLICA'            => 0,
        'PRESENCIAL'            => 1,
        'INTERNET'              => 2,
        'TELEATENDIMENTO'       => 3,
        'NFCE'                  => 4,
        'FORA_ESTABELECIMENTO'  => 5,
        'NAO_PRESENCIAL_OUTRO'  => 9
    ],

    
    'procEmi' => [
        'APLICATIVO_CONTRIBUINTE'   => 0,
        'AVULSA_FISCO'              => 1,
        'NFE_AVULSA'                => 2,
        'APLICATIVO_FISCO'          => 3,
    ],
    
    'indIEDest' => [
        'CONTRIBUINTE'      => 1,
        'ISENTO'            => 2,
        'NÃO_CONTRIBUINTE'  => 9 
    ],  
    
   
    

];

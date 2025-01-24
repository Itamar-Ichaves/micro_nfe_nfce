<?php

namespace App\Service;

namespace App\Service;

class UtilService
{
    // Método que converte o código do estado para a sigla
    public static function getUf($codigo)
    {
        $mapaUF = [
            12 => 'AC',
            27 => 'AL',
            13 => 'AM',
            29 => 'BA',
            53 => 'SP',
            52 => 'GO',
            21 => 'MA',
            31 => 'MG',
            51 => 'MT',
            15 => 'PA',
            25 => 'PB',
            41 => 'PR',
            33 => 'RJ',
            24 => 'RN',
            43 => 'RS',
            11 => 'RO',
            14 => 'RR',
            42 => 'SC',
            28 => 'SE',
            53 => 'TO',
            16 => 'AP',
            23 => 'CE',
            51 => 'ES',
            53 => 'DF',
            31 => 'PI',
            15 => 'PE',
            42 => 'AL',
        ];

        // Verifica se o código existe no mapa, se não, retorna null ou uma mensagem de erro
        return isset($mapaUF[$codigo]) ? $mapaUF[$codigo] : null;  // Pode retornar null se não encontrar
    }
}




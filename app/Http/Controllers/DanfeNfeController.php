<?php

namespace App\Http\Controllers;

use App\Repository\NfeRepository;
use Illuminate\Http\Request;
use NFePHP\DA\NFe\Danfe; 

class DanfeNfeController extends Controller
{
    protected $nfeRepository;

    public function __construct(NfeRepository $nfeRepository)
    {
        $this->nfeRepository = $nfeRepository;
    }

    public function danfe(Request $request)
    {
        $id = $request->input('id');
        $token_company = $request->input('token_company');
        $token_emitente = $request->input('token_emitente');
        $pastaAmbiente = $request->input('pastaAmbiente');
        $tipo = $request->input('tipo');

        $nota = $this->nfeRepository->getNfeId($id, $token_company, $token_emitente);

        if (!$nota || !$nota->chave) {
            return response()->json(['error' => 'Nota fiscal não encontrada ou chave ausente.'], 404);
        }

        try {
            $chave = $nota->chave;
            $path = $nota->caminho;
            $nomeArquivo = "{$chave}-nfe.xml";

            if (!file_exists($path . $nomeArquivo)) {
                return response()->json(['error' => 'Arquivo XML não encontrado no caminho especificado.'], 404);
            }

            $xml = file_get_contents($path . $nomeArquivo);

            // Valida o XML antes de passar para o Danfe
            if (simplexml_load_string($xml) === false) {
                return response()->json(['error' => 'XML inválido.'], 400);
            }

            // Cria o DANFE
            $danfe = new Danfe($xml);
            $pdf = $danfe->render();

            return response($pdf, 200)
                ->header('Content-Type', 'application/pdf')
                ->header('Content-Disposition', 'inline; filename="danfe.pdf"');
        } catch (\Exception $e) {
            return response()->json(['error' => 'Erro ao gerar o DANFE: ' . $e->getMessage()], 500);
        }
    }
}

    


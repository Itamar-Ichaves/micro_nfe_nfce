<?php

namespace App\Http\Controllers;

use App\Repository\NfeRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use NFePHP\DA\NFe\Danfe; 

class DanfeNfeController extends Controller
{
    protected $nfeRepository;
 
    public function __construct(NfeRepository $nfeRepository)
    {
        $this->nfeRepository = $nfeRepository;
    }

/*************  ✨ Windsurf Command ⭐  *************/
    /**
     * Gerar o DANFE da NFe informada, com base na chave da NF-e.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    
/*******  86fadfee-2b3f-4e31-a648-8f56c142d707  *******/   
public function danfe(Request $request, $token_company, $token_emitente, $chave)
{  
;

    try {
        // 1. Obter a nota fiscal do repositório
        $nota = $this->nfeRepository->getNfeChave($chave, $token_company, $token_emitente);
         
        if (!$nota) {
            return response()->json(['error' => 'Nota fiscal não encontrada.'], 404);
        }

        // 2. Verificar se a nota está autorizada
        if ($nota->status !== 'AUTORIZADO') {
            return response()->json(['error' => 'Nota fiscal não está autorizada. Status atual: ' . $nota->status], 400);
        }

        // 3. Construir o caminho completo do XML
        $caminhoCompleto = $nota->caminho . $nota->nomeArquivo;
        
        // Verificação adicional do caminho
        if (!file_exists($caminhoCompleto)) {
            return response()->json([
                'error' => 'Arquivo XML não encontrado.',
                'caminho' => $caminhoCompleto,
                'arquivo' => $nota->nomeArquivo
            ], 404);
        }

        // 4. Carregar e validar o XML
        $xml = file_get_contents($caminhoCompleto);
         
        $xml = file_get_contents($caminhoCompleto);
         
        Log::debug('Conteúdo do XML (início):', [substr($xml, 0, 500)]); // Loga início do XML
        Log::debug('Tamanho do XML:', [strlen($xml)]);

        // Validação do XML
        $dom = new \DOMDocument();
        if (!$dom->loadXML($xml)) {
            Log::error('Erro na validação do XML.');
            return response()->json(['error' => 'XML inválido.'], 400);
        }

        // 2. Criação do DANFE com tratamento especial
        try {
            $danfe = new \NFePHP\DA\NFe\Danfe($xml);
            
            // Configurações CRÍTICAS para evitar problemas:
            $danfe->setDefaultFont('helvetica');
            $danfe->debugMode(false);
            $danfe->setOcultarUnidadeTributavel(false);
            
            // 3. DEBUG: Verificar se o objeto Danfe foi criado corretamente
            if (!$danfe) {
                throw new \Exception("Falha na criação do objeto Danfe");
            }

            // 4. Geração do PDF com buffer de saída
            ob_start(); // Inicia captura de output
            $pdf = $danfe->render();
            $output = ob_get_clean(); // Captura qualquer erro de renderização
            
            if (!empty($output)) {
                Log::error('Erros durante renderização:', [$output]);
            }

            // 5. Verificação EXTRA do PDF gerado
            if (empty($pdf)) {
                throw new \Exception("O PDF gerado está vazio");
            }

            // 6. DEBUG: Salvar temporariamente o PDF para análise
            $tempPath = storage_path("temp/danfe_{$chave}.pdf");
            file_put_contents($tempPath, $pdf);
            Log::debug("PDF temporário salvo em: {$tempPath}");

            // 7. Retorno com headers ESPECÍFICOS
            return response($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="DANFE-' . $nota->chave . '.pdf"',
                'Content-Length' => strlen($pdf),
                'Cache-Control' => 'no-cache, no-store, must-revalidate',
                'Pragma' => 'no-cache',
                'Expires' => '0'
            ]);

        } catch (\Throwable $th) {
            // Captura específica de erros do NFePHP
            Log::error('ERRO NO NFePHP:', [
                'message' => $th->getMessage(),
                'code' => $th->getCode(),
                'file' => $th->getFile(),
                'line' => $th->getLine(),
                'trace' => $th->getTraceAsString()
            ]);
            
            throw new \Exception("Erro na geração do DANFE: " . $th->getMessage());
        }

    } catch (\Exception $e) {
        // ... (tratamento de erros)
    }
}

}

    


<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class OllamaController extends Controller
{
    public function upload(Request $request)
    {
        // Validácia vstupov
        $request->validate([
            'pdf' => 'required|mimes:pdf|max:2048',
            'prompt' => 'required|string'
        ]);

        // Uloženie PDF súboru
        $pdfPath = $request->file('pdf')->store('uploads', 'public');

        // Cesty k súborom
        $pdfFilePath = storage_path("app/public/" . $pdfPath);
        $txtFilePath = storage_path("app/public/texts/" . pathinfo($pdfFilePath, PATHINFO_FILENAME) . ".txt");
        Log::info("Mám všetko posielam do pythonu");

        // Spustenie Python skriptu na konverziu PDF -> TXT
        $process = new Process(["/Users/solecky/DEV_dip/copenv/bin/python3", base_path("python/pdf_to_text.py"), $pdfFilePath, $txtFilePath]);
        $process->run();

        if (!$process->isSuccessful()) {
            Log::error("Process Error Output: " . $process->getErrorOutput());
            return response()->json(['error' => 'Konverzia PDF na TXT zlyhala'], 500);
        }

        Log::info("Process Complete");

        // Načítanie textu zo súboru
        $text = file_get_contents($txtFilePath);
        $prompt = $request->input('prompt');
        Log::info("Sending to Ollama");
        $ollamaResponse = Http::timeout(300)
        ->post('http://localhost:11434/api/generate', [
            'model' => 'llama3.2:3b',
            'prompt' => $prompt . "\n\n" . $text,
            'stream' => false
        ]);

        if ($ollamaResponse->failed()) {
            return response()->json([
                'error' => 'Ollama request failed',
                'details' => $ollamaResponse->body(),
            ], 500);
        }

        // Výstup od Ollama
        $result = $ollamaResponse;

        return view('upload', [
            'response' => $result['response'] ?? 'Chyba pri spracovaní.',
            'text' => $text,
            'prompt' => $prompt
        ]);
    }
}

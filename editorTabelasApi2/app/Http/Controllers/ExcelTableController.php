<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\DB;
use App\Models\ExcelTable; // Certifique-se de importar o model
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log; // Para logs de erro (opcional)

class ExcelTableController extends Controller // Corrigi o nome para "Excel" (padrão)
{
     // ===== MÉTODOS PARA A API (JSON) =====
    public function index()
    {
        // Retornar apenas dados essenciais para o dropdown
     $tables = ExcelTable::all();
    
    // Adiciona fallback para preview_data se não existir
    $tables->each(function ($table) {
        if (empty($table->preview_data) && !empty($table->headers)) {
            $table->preview_data = [
                'headers' => $table->headers,
                'type' => 'legacy'
            ];
        }
    });
    
    return $tables;
    }

public function store(Request $request)
{
    \DB::beginTransaction();
    try {
        // Validação para ambos os formatos
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'file' => 'nullable|file|mimes:xlsx,xml,csv|max:102400',
            'headers' => 'nullable|array',
            'data' => 'nullable|array'
        ]);

        if ($request->hasFile('file')) {
            // Nova implementação (com arquivo)
            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = strtolower($file->getClientOriginalExtension());
            
            $fileName = time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('excel_files', $fileName, 'public');
            
            $previewData = $this->extractPreviewData($file, $extension);
            
            $table = ExcelTable::create([
                'name' => $validated['name'],
                'file_path' => $path,
                'original_name' => $originalName,
                'file_size' => $file->getSize(),
                'preview_data' => $previewData,
                'headers' => [], // mantém compatibilidade
                'data' => [] // mantém compatibilidade
            ]);
        } else {
            // Implementação antiga (apenas dados)
            $table = ExcelTable::create([
                'name' => $validated['name'],
                'headers' => $validated['headers'],
                'data' => $validated['data'],
                'file_path' => null, // novos campos como null
                'original_name' => null,
                'file_size' => null,
                'preview_data' => null
            ]);
        }

        \DB::commit();
        return response()->json($table, 201);

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error("Erro ao salvar tabela: " . $e->getMessage());
        return response()->json([
            'message' => 'Erro ao salvar tabela',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null
        ], 500);
    }
}

 private function extractPreviewData($file, $extension)
    {
        try {
            $filePath = $file->getRealPath();
            
            if ($extension === 'xlsx' || $extension === 'csv') {
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                
                return json_encode([
                    'headers' => $sheet->rangeToArray('A1:Z1')[0],
                    'sample_data' => $sheet->rangeToArray('A2:Z3'),
                    'type' => 'spreadsheet'
                ]);
            }
            elseif ($extension === 'xml') {
                // Processamento específico para XML
                $xml = simplexml_load_file($filePath);
                $json = json_encode($xml);
                $array = json_decode($json, true);
                
                // Extrai os primeiros elementos para preview
                $sample = array_slice($array, 0, 3);
                
                return json_encode([
                    'headers' => array_keys($sample[0] ?? []),
                    'sample_data' => $sample,
                    'type' => 'xml'
                ]);
            }
            
            return null;
            
        } catch (\Exception $e) {
            Log::error("Erro ao extrair preview: " . $e->getMessage());
            return null;
        }
    }

public function loadFullFile(ExcelTable $excelTable)
    {
        try {
            $filePath = storage_path('app/public/' . $excelTable->file_path);
            
            if (!file_exists($filePath)) {
                return response()->json(['error' => 'Arquivo não encontrado'], 404);
            }
            
            $extension = pathinfo($filePath, PATHINFO_EXTENSION);
            $preview = json_decode($excelTable->preview_data, true);
            
            if (($preview['type'] ?? null) === 'spreadsheet') {
                $spreadsheet = IOFactory::load($filePath);
                $sheet = $spreadsheet->getActiveSheet();
                
                return response()->json([
                    'headers' => $sheet->rangeToArray('A1:Z1')[0],
                    'data' => $sheet->rangeToArray('A2:Z' . $sheet->getHighestRow()),
                    'type' => 'spreadsheet'
                ]);
            }
            elseif (($preview['type'] ?? null) === 'xml') {
                $xml = simplexml_load_file($filePath);
                $json = json_encode($xml);
                $array = json_decode($json, true);
                
                return response()->json([
                    'headers' => array_keys($array[0] ?? []),
                    'data' => $array,
                    'type' => 'xml'
                ]);
            }
            
            return response()->json(['error' => 'Formato não suportado'], 400);
            
        } catch (\Exception $e) {
            Log::error("Erro ao carregar arquivo: " . $e->getMessage());
            return response()->json([
                'error' => 'Erro ao processar arquivo',
                'message' => env('APP_DEBUG') ? $e->getMessage() : null
            ], 500);
        }
    }


    public function show(ExcelTable $excelTable)
    {
        return response()->json($excelTable); // Padronize sempre com response()->json()
    }

    public function update(Request $request, ExcelTable $excelTable)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'headers' => 'sometimes|array',
                'data' => 'sometimes|array'
            ]);

            $excelTable->update($validated);

            return response()->json([
                'success' => true,
                'data' => $excelTable
            ]);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            Log::error("Erro ao atualizar tabela: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar tabela.'
            ], 500);
        }
    }

    public function destroy(ExcelTable $excelTable)
    {
        try {
            $excelTable->delete();
            return response()->json(null, 204);

        } catch (\Exception $e) {
            Log::error("Erro ao deletar tabela: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir tabela.'
            ], 500);
        }
    }

        // ===== MÉTODOS PARA O FRONTEND (BLADE) =====
public function indexView()
{
    $tabelas = ExcelTable::all(); // Busca todas as tabelas
    return view('tabelas.index', compact('tabelas'));
}

    public function createView()
    {
        return view('tabelas.create');
    }

    public function excelTables()
    {
        $tables = DB::select("SELECT name FROM sys.tables");
        return response()->json($tables);
    }
}

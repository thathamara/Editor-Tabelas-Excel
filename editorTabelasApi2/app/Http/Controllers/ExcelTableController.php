<?php

namespace App\Http\Controllers;

use PhpOffice\PhpSpreadsheet\IOFactory;
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
        $validated = $request->validate([
            'name' => 'required|string|min:2|max:255', // Mudei para min:2
            'file' => 'nullable|file|mimes:xlsx,xml,csv,xls|max:102400',
            'headers' => 'nullable|array',
            'data' => 'nullable|array'
        ]);

        // Crie o array de dados básico
        $data = [
            'name' => $validated['name'],
            'headers' => $validated['headers'] ?? [],
            'data' => $validated['data'] ?? []
        ];

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $data = array_merge($data, [
                'file_path' => $file->storeAs('excel_files', time().'_'.uniqid().'.'.$file->getClientOriginalExtension(), 'public'),
                'original_name' => $file->getClientOriginalName(),
                'file_size' => $file->getSize(),
                'preview_data' => $this->extractPreviewData($file, $file->getClientOriginalExtension())
            ]);
        }

        // Deixe o Laravel cuidar dos timestamps
        $table = ExcelTable::create($data);

        \DB::commit();
        return response()->json($table, 201);

    } catch (\Exception $e) {
        \DB::rollBack();
        \Log::error("Erro detalhado: " . $e->getMessage());
        return response()->json([
            'message' => 'Erro ao salvar tabela',
            'error' => env('APP_DEBUG') ? $e->getMessage() : null,
            'trace' => env('APP_DEBUG') ? $e->getTraceAsString() : null
        ], 500);
    }
}

 private function extractPreviewData($file, $extension)
    {
        try {
            $filePath = $file->getRealPath();
            
            if ($extension === 'xlsx' || $extension === 'csv' || $extension === 'xls') {
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

public function loadFullFile($id) // O parâmetro $id já está definido aqui
{
    try {
        \Log::info("Carregando arquivo", [
            'id' => $id, // Usando o parâmetro $id recebido
            'action' => 'loadFullFile'
        ]);

        $excelTable = ExcelTable::findOrFail($id);
        
        if (!$excelTable->file_path) {
            return response()->json([
                'headers' => $excelTable->headers ?? [],
                'data' => $excelTable->data ?? [],
                'type' => 'database'
            ]);
        }

        $filePath = storage_path('app/public/' . $excelTable->file_path);
        
        if (!file_exists($filePath)) {
            \Log::error("Arquivo não encontrado", ['path' => $filePath]);
            return response()->json([
                'error' => 'Arquivo não encontrado',
                'path' => $excelTable->file_path
            ], 404);
        }

        $extension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
        
        if (in_array($extension, ['xlsx', 'xls', 'csv'])) {
            $spreadsheet = IOFactory::load($filePath);
            $sheet = $spreadsheet->getActiveSheet();
            
            // Método mais seguro para extrair dados
            $data = $sheet->toArray();
            
            // Verifica se há dados
            if (empty($data)) {
                \Log::warning("Arquivo Excel vazio", ['id' => $id]);
                return response()->json([
                    'headers' => [],
                    'data' => [],
                    'type' => 'spreadsheet'
                ]);
            }
            
            // Extrai headers (primeira linha)
            $headers = isset($data[0]) ? $data[0] : [];
            
            // Extrai dados (linhas restantes)
            $tableData = count($data) > 1 ? array_slice($data, 1) : [];
            
            return response()->json([
                'headers' => $headers,
                'data' => $tableData,
                'type' => 'spreadsheet'
            ]);
        }
        
        return response()->json(['error' => 'Formato não suportado'], 400);
        
    } catch (\Exception $e) {
        \Log::error("Erro ao carregar arquivo", [
            'id' => $id,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
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

// \Log::info("Carregando arquivo", [
//     'id' => $id,
//     'path' => $filePath,
//     'exists' => file_exists($filePath) ? 'sim' : 'não'
// ]);

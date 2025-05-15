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
        return response()->json(ExcelTable::all());
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'headers' => 'required|array',
                'data' => 'required|array'
            ]);

            $table = ExcelTable::create($validated);

            return response()->json([
                'success' => true,
                'data' => $table
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors() // Retorna erros de validação detalhados
            ], 422);

        } catch (\Exception $e) {
            Log::error("Erro ao criar tabela: " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Erro interno ao salvar tabela.'
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

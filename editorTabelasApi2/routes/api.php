<?php
use App\Http\Controllers\ExcelTableController;
use Illuminate\Support\Facades\Route;

// Route::prefix('api')->group(function () {
//     Route::apiResource('tabelas', ExcelTableController::class);
    Route::get('/excel-tables', [ExcelTableController::class, 'excelTables']);
    // Ou rotas manuais (como você já tinha):
    // Route::get('/tabelas', [ExcelTableController::class, 'index']);
    // Route::post('/tabelas', [ExcelTableController::class, 'store']);
// });
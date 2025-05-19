<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ExcelTableController;

    Route::get('/excel-tables', [ExcelTableController::class, 'index']);
    Route::get('/excel-tables/{excelTable}', [ExcelTableController::class, 'show']);
    Route::get('/excel-tables/{id}/load', [ExcelTableController::class, 'loadFullFile']);

    Route::post('/excel-tables', [ExcelTableController::class, 'store']);
    Route::put('/excel-tables/{excelTable}', [ExcelTableController::class, 'update']);
    Route::delete('/excel-tables/{excelTable}', [ExcelTableController::class, 'destroy']);
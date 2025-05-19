<?php

use App\Http\Controllers\ExcelTableController;
use Illuminate\Support\Facades\Route;

// Rota inicial (página principal)
Route::get('/', function () {
    return view('welcome'); // Exemplo: Pode ser sua dashboard
});

Route::get('/tabelas', [ExcelTableController::class, 'indexView']);
Route::get('/tabelas/criar', [ExcelTableController::class, 'createView']);

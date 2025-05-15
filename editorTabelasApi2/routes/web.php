<?php

use App\Http\Controllers\ExcelTableController;
use Illuminate\Support\Facades\Route;

// Rota inicial (página principal)
Route::get('/', function () {
    return view('welcome'); // Exemplo: Pode ser sua dashboard
});

// Rotas para o CRUD de tabelas (frontend)
Route::get('/tabelas', [ExcelTableController::class, 'indexView'])->name('tabelas.index');
Route::get('/tabelas/criar', [ExcelTableController::class, 'createView'])->name('tabelas.create');

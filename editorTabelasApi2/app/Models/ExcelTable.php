<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExcelTable extends Model
{
    protected $fillable = [
        'name',
        'file_path',
        'original_name',
        'file_size',
        'preview_data',
        'headers',
        'data'
    ];
    
    protected $casts = [
        'preview_data' => 'array',
        'headers' => 'array',
        'data' => 'array',
        'file_size' => 'integer'
    ];
    
    // Adicione esta propriedade
    protected $dateFormat = 'Y-m-d H:i:s.v';
    
    // E esta para garantir o formato correto
    protected $dates = [
        'created_at',
        'updated_at'
    ];
}
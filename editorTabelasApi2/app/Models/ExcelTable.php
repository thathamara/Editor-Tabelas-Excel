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
        'headers', // mantenha se ainda for usado
        'data' // mantenha se ainda for usado
    ];
    
protected $casts = [
    'preview_data' => 'array',
    'headers' => 'array',
    'data' => 'array',
    'file_size' => 'integer'
];
}
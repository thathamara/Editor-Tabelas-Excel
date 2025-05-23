<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
         Schema::create('excel_tables', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->json('headers');
        $table->json('data');
        $table->datetime('created_at')->precision(3); // Precisão de milissegundos
        $table->datetime('updated_at')->precision(3);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('excel_tables');
    }
};


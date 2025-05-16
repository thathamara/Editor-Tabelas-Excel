<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
{
    Schema::table('excel_tables', function (Blueprint $table) {
        $table->string('file_path')->nullable()->after('data');
        $table->string('original_name')->nullable()->after('file_path');
        $table->integer('file_size')->nullable()->after('original_name');
        $table->json('preview_data')->nullable()->after('file_size');
    });
}

public function down()
{
    Schema::table('excel_tables', function (Blueprint $table) {
        $table->dropColumn(['file_path', 'original_name', 'file_size', 'preview_data']);
    });
}
};

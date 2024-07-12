<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Habilita a extensão pgcrypto para geração de UUIDs no PostgreSQL
        DB::statement('CREATE EXTENSION IF NOT EXISTS "uuid-ossp"');
    
        Schema::create('certificado_digitals', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('uuid_generate_v4()'));
            $table->uuid('token_company')->nullable();
            $table->uuid('token_emitente')->nullable();
            $table->string('certificado_nome_arquivo', 60)->nullable();
            $table->binary('arquivo_binario');
            $table->string('senha', 60);
    
            $table->string('cnpj', 80);
            $table->string('serial', 80)->nullable();
            $table->string('inicio', 80)->nullable();
            $table->string('expiracao', 100)->nullable(); // Alterado para evitar caracteres especiais
            $table->string('identificador', 150)->nullable();
            $table->string('idctx', 200)->nullable();
    
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('certificado_digitals');
    }
};

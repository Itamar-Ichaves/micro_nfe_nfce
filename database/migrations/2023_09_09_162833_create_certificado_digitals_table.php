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
        Schema::create('certificado_digitals', function (Blueprint $table) {
            $table->id();
            $table->uuid('token_company');
            $table->uuid('token_emitente');
            $table->string('cnpj');
            $table->string('senha');
            $table->string('arquivo_caminho');
            $table->timestamp('inicio_validade')->nullable();
            $table->timestamp('fim_validade')->nullable();
            $table->string('serial');
            $table->string('id_certificado');
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

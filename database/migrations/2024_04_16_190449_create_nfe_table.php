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
        Schema::create('notaNfe', function (Blueprint $table) {
            $table->uuid('id');
            $table->uuid('token_company');
            $table->uuid('token_emitente');
            $table->string('nfe_id');
            $table->string('cnpj');
            $table->string('status', 60)->nullable();
            $table->string('protocolo')->nullable();
            $table->string('recibo', 60)->nullable();
            $table->string('chave')->nullable();
            $table->string('caminho')->nullable();
            $table->string('nomeArquivo')->nullable();
            $table->string("dhEmi")->nullable();
            $table->string("dhSaiEnt")->nullable();
            $table->date('created_at');
            $table->date('updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notaNfe');
    }
};

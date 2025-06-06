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
        Schema::create('participantes', function (Blueprint $table) {
            $table->id('codigo');
            $table->string('nome');
            $table->string('cpf', 14)->unique();
            $table->string('email')->nullable();
            $table->date('data_nascimento');
            $table->string('nome_contato_emergencia');
            $table->string('numero_contato_emergencia', 15);
            $table->string('telefone', 15);
            $table->char('sexo', 1);
            $table->string('cidade');
            $table->boolean('participante_igreja')->default(false);
            $table->string('qual_igreja')->nullable();
            $table->boolean('usa_medicamento')->default(false);
            $table->string('qual_medicamento')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('participantes');
    }
};

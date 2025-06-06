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
        Schema::create('inscricoes', function (Blueprint $table) {
            $table->id('codigo'); // Chave primária como 'codigo'
            $table->foreignId('evento_codigo')->constrained('eventos', 'codigo')->onDelete('cascade');
            $table->foreignId('participante_codigo')->constrained('participantes', 'codigo')->onDelete('cascade');
            $table->dateTime('data');
            $table->string('forma_pagamento');
            $table->boolean('cortesia')->nullable();
            $table->char('status', 1); // P: Pago, C: Cancelado (ou similar)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscricoes');
    }
}; 
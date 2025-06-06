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
        Schema::create('esperas', function (Blueprint $table) {
            $table->id('codigo');
            $table->foreignId('participante_codigo')->constrained('participantes', 'codigo')->onDelete('cascade');
            $table->foreignId('evento_codigo')->constrained('eventos', 'codigo')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('esperas');
    }
};

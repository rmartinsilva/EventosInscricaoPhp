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
        Schema::create('eventos', function (Blueprint $table) {           
            $table->id('codigo');
            $table->string('descricao');
            $table->date('data')->comment('Data principal do evento');
            $table->dateTime('data_inicio_inscricoes')->comment('Data e hora de início das inscrições');
            $table->dateTime('data_final_inscricoes')->comment('Data e hora final das inscrições');
            $table->integer('numero_inscricoes')->default(0);
            $table->boolean('cortesias')->default(false)->comment('Indica se o evento oferece cortesias');
            $table->integer('numero_cortesia')->nullable()->comment('Número de cortesias disponíveis');
            $table->text('link_obrigado')->nullable()->comment('URL de agradecimento após inscrição');
            $table->string('url')->unique()->comment('URL amigável do evento');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eventos');
    }
};

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
        Schema::create('pagamentos_pendentes', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique(); // Para external_reference
            
            // Dados para a futura inscrição
            $table->foreignId('evento_codigo')->constrained('eventos', 'codigo')->onDelete('restrict'); // Não cascatear se o evento for deletado enquanto o pagamento está pendente
            $table->foreignId('participante_codigo')->constrained('participantes', 'codigo')->onDelete('restrict');
            $table->string('forma_pagamento_solicitada'); // Ex: 'credit_card', 'pix'
            $table->decimal('valor', 10, 2);

            // Dados do Mercado Pago
            $table->string('id_pagamento_mp')->nullable()->index(); // ID do pagamento no Mercado Pago
            $table->string('status_pagamento_mp')->nullable()->index(); // Status do pagamento no MP (pending, approved, rejected, etc.)
            $table->json('dados_criacao_mp_json')->nullable(); // Resposta da API do MP ao criar o pagamento
            $table->json('dados_webhook_mp_json')->nullable(); // Último payload do webhook recebido

            $table->boolean('inscricao_efetivada')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagamentos_pendentes');
    }
};

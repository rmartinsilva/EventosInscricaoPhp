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
        Schema::create('configuracoes', function (Blueprint $table) {
            $table->id();
            $table->string('descricao_api')->unique()->comment('Descrição da API para identificação');
            $table->text('chave_api')->comment('Chave ou token da API'); // Usar text para chaves potencialmente longas           
            $table->text('token_api')->nullable()->comment('Token da API');
            $table->text('webhooksecret')->nullable()->comment('Webhook Secret');
            $table->string('notificationurl')->nullable()->comment('URL de Notificação');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('configuracoes');
    }
};

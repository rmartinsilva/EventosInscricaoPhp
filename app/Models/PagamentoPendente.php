<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PagamentoPendente extends Model
{
    use HasFactory;

    protected $table = 'pagamentos_pendentes';

    protected $fillable = [
        'uuid',
        'evento_codigo',
        'participante_codigo',
        'forma_pagamento_solicitada',
        'valor',
        'id_pagamento_mp',
        'status_pagamento_mp',
        'dados_criacao_mp_json',
        'dados_webhook_mp_json',
        'inscricao_efetivada',
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'dados_criacao_mp_json' => 'array',
        'dados_webhook_mp_json' => 'array',
        'inscricao_efetivada' => 'boolean',
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->uuid)) {
                $model->uuid = (string) Str::uuid();
            }
        });
    }

    // Relacionamentos (opcional, mas pode ser útil)
    public function evento()
    {
        return $this->belongsTo(\App\Models\Evento::class, 'evento_codigo', 'codigo');
    }

    public function participante()
    {
        return $this->belongsTo(\App\Models\Participante::class, 'participante_codigo', 'codigo');
    }
} 
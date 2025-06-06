<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Evento extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'eventos';

    /**
     * The primary key associated with the table.
     *
     * @var string
     */
    protected $primaryKey = 'codigo';

    /**
     * Indicates if the model's ID is auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = true;

    /**
     * The data type of the auto-incrementing ID.
     *
     * @var string
     */
    protected $keyType = 'int';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'descricao',
        'data',
        'data_inicio_inscricoes',
        'data_final_inscricoes',
        'numero_inscricoes',
        'cortesias',
        'numero_cortesia',
        'link_obrigado',
        'url',
        'valor',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'data' => 'date:Y-m-d',
        'data_inicio_inscricoes' => 'datetime',
        'data_final_inscricoes' => 'datetime',
        'cortesias' => 'boolean',
        'numero_inscricoes' => 'integer',
        'numero_cortesia' => 'integer',
        //'valor' => \Cknow\Money\Casts\MoneyDecimalCast::class,
        'valor' => 'decimal:2',
    ];

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = true;
}

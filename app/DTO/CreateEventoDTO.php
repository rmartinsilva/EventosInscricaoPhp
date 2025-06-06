<?php

namespace App\DTO;

use App\Http\Requests\StoreEventoRequest;

class CreateEventoDTO
{
    public function __construct(
        // public string $codigo, // Removido - auto-incremental
        public string $descricao,
        public string $data, // Datas serão strings no formato Y-m-d
        public string $data_inicio_inscricoes,
        public string $data_final_inscricoes,
        public int $numero_inscricoes,
        public bool $cortesias,
        public ?int $numero_cortesia,
        public ?string $link_obrigado,
        public string $url,
        public string $valor
    ) {}

    public static function makeFromRequest(StoreEventoRequest $request): self
    {
        return new self(
            // $request->codigo, // Removido
            $request->descricao,
            $request->data,
            $request->data_inicio_inscricoes,
            $request->data_final_inscricoes,
            (int) $request->validated('numero_inscricoes', 0), // Cast para int
            $request->validated('cortesias', false),
            $request->numero_cortesia ? (int) $request->numero_cortesia : null, // Cast para int se não for null
            $request->link_obrigado,
            $request->url,
            //Money::USD($request->validated('valor'),true),
            $request->validated('valor')
        );
    }
} 
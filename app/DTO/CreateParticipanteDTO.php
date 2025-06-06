<?php

namespace App\DTO;

use App\Http\Requests\StoreParticipanteRequest; // Será criado depois

class CreateParticipanteDTO
{
    public function __construct(
        public string $nome,
        public string $cpf,
        public ?string $email,
        public string $data_nascimento, // Espera string no formato Y-m-d
        public string $nome_contato_emergencia,
        public string $numero_contato_emergencia,
        public string $telefone,
        public string $sexo,
        public string $cidade,
        public bool $participante_igreja,
        public ?string $qual_igreja,
        public bool $usa_medicamento,
        public ?string $qual_medicamento
    ) {}

    public static function makeFromRequest(StoreParticipanteRequest $request): self
    {
        return new self(
            $request->nome,
            $request->cpf,
            $request->email,
            $request->data_nascimento,
            $request->nome_contato_emergencia,
            $request->numero_contato_emergencia,
            $request->telefone,
            $request->sexo,
            $request->cidade,
            (bool) $request->validated('participante_igreja', false),
            $request->qual_igreja,
            (bool) $request->validated('usa_medicamento', false),
            $request->qual_medicamento
        );
    }
} 
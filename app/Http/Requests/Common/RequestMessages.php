<?php

namespace App\Http\Requests\Common;

class RequestMessages
{
    /**
     * Retorna um array de mensagens de validação comuns em português.
     *
     * @return array<string, string>
     */
    public static function common(): array
    {
        return [
            'required' => 'O campo :attribute é obrigatório.',
            'string'   => 'O campo :attribute deve ser um texto.',
            'max'      => 'O campo :attribute não pode ter mais de :max caracteres.',
            'min'      => [
                'string' => 'O campo :attribute deve ter pelo menos :min caracteres.',
                // Adicionar 'numeric', 'file', 'array' se necessário
            ],
            'unique'   => 'O valor informado para o campo :attribute já está em uso.',
            'email'    => 'O campo :attribute deve ser um endereço de e-mail válido.',
            'numeric'  => 'O campo :attribute deve ser um número.',
            'integer'  => 'O campo :attribute deve ser um número inteiro.',
            'boolean'  => 'O campo :attribute deve ser verdadeiro ou falso.',
            'date'     => 'O campo :attribute não é uma data válida.',
            'array'    => 'O campo :attribute deve ser um array.',
            'exists'   => 'O valor selecionado para :attribute é inválido.',
            'sometimes' => '', // 'sometimes' geralmente não precisa de mensagem visível
        ];
    }

    // Você pode adicionar outros métodos para grupos específicos de mensagens se desejar
    // public static function userMessages(): array
    // {
    //     return array_merge(self::common(), [
    //         'login.unique' => 'Este login já foi cadastrado.',
    //     ]);
    // }
}

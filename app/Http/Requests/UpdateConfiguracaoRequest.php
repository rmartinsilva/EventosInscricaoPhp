<?php

namespace App\Http\Requests;

// use Illuminate\Foundation\Http\FormRequest; // Remover ou comentar
use Illuminate\Validation\Rule;

class UpdateConfiguracaoRequest extends BaseRequest // Alterar para BaseRequest
{
    // Remover o método authorize(), pois assume-se que BaseRequest ou middleware cuidam disso
    // /**
    //  * Determine if the user is authorized to make this request.
    //  */
    // public function authorize(): bool
    // {
    //     // A autorização será feita pelo middleware 'can:update_configuracoes' na rota
    //     return true;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $id = $this->route('configuracao'); // Obtém o ID da rota

        return [
            'descricao_api' => [
                'required',
                'string',
                'max:255',
                Rule::unique('configuracoes', 'descricao_api')->ignore($id) // Ignora o registro atual na verificação de unicidade
            ],
            'chave_api' => 'nullable|string', // Chave é opcional na atualização
            'token_api' => 'nullable|string',
            'webhooksecret' => 'nullable|string',
            'notificationurl' => 'nullable|string|url',
        ];
    }
}

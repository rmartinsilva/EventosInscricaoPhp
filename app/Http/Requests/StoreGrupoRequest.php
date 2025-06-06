<?php

namespace App\Http\Requests;

// Remover FormRequest se não for mais usado diretamente
// Remover RequestMessages, Validator, HttpResponseException imports se não forem mais necessários aqui

// Extender BaseRequest
class StoreGrupoRequest extends BaseRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    // Remover authorize() - herdado de BaseRequest
    // public function authorize(): bool
    // {
    //     return true;
    // }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Manter apenas as regras específicas
        return [
            'descricao' => 'required|string|max:255|unique:grupos,descricao',
        ];
    }

    // Remover messages() - herdado de BaseRequest
    // public function messages(): array
    // {
    //     return RequestMessages::common();
    // }

    // Remover failedValidation() - herdado de BaseRequest
    // protected function failedValidation(Validator $validator)
    // {
    //     // ...
    // }
}

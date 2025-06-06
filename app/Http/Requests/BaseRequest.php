<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Http\Requests\Common\RequestMessages;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * Assume authorization is handled by route middleware.
     * Override this method in specific requests if needed.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the common validation messages.
     *
     * Specific requests can override this method or merge arrays if needed.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return RequestMessages::common();
    }

    /**
     * Handle a failed validation attempt.
     *
     * Formats the error response for API consistency.
     *
     * @param  \Illuminate\Contracts\Validation\Validator  $validator
     * @return void
     *
     * @throws \Illuminate\Http\Exceptions\HttpResponseException
     */
    protected function failedValidation(Validator $validator)
    {
        $errors = $validator->errors();
        throw new HttpResponseException(response()->json([
            'message' => 'Os dados fornecidos são inválidos.',
            'errors' => $errors
        ], 422));
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * This method must be implemented by the extending class.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    abstract public function rules(): array;
}

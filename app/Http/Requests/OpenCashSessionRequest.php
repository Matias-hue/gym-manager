<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'opening_amount' => ['required', 'integer', 'min:0', 'max:100000000'],
        ];
    }

    public function attributes(): array
    {
        return ['opening_amount' => 'fondo inicial'];
    }

    public function messages(): array
    {
        return [
            'required' => 'Ingresá el :attribute.',
            'integer' => 'El :attribute tiene que ser un número entero, sin centavos.',
            'min' => 'El :attribute no puede ser negativo.',
            'max' => 'El :attribute es demasiado alto.',
        ];
    }
}

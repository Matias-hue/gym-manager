<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCashMovementRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(['income', 'expense'])],
            'concept' => ['required', 'string', 'max:150'],
            'amount' => ['required', 'integer', 'min:1', 'max:100000000'],
        ];
    }

    public function attributes(): array
    {
        return [
            'type' => 'tipo',
            'concept' => 'concepto',
            'amount' => 'monto',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Completá el campo :attribute.',
            'integer' => 'El :attribute tiene que ser un número entero, sin centavos.',
            'min' => 'El :attribute tiene que ser mayor a cero.',
            'max' => 'El valor de :attribute es demasiado alto.',
            'in' => 'El :attribute no es válido.',
        ];
    }
}

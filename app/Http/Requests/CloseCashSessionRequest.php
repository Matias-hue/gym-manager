<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CloseCashSessionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'counted_amount' => ['required', 'integer', 'min:0', 'max:100000000'],
            'notes' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'counted_amount' => 'efectivo contado',
            'notes' => 'nota',
        ];
    }

    public function messages(): array
    {
        return [
            'required' => 'Ingresá el :attribute.',
            'integer' => 'El :attribute tiene que ser un número entero, sin centavos.',
            'min' => 'El :attribute no puede ser negativo.',
            'max' => 'El valor de :attribute es demasiado alto.',
        ];
    }
}

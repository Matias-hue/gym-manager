<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePaymentRequest extends FormRequest
{
    // Un usuario solo puede cobrar a socios de su gimnasio
    public function authorize(): bool
    {
        return $this->user()->gym_id === $this->route('member')->gym_id;
    }

    public function rules(): array
    {
        $gymId = $this->route('member')->gym_id;

        return [
            'plan_id' => [
                'required',
                Rule::exists('plans', 'id')
                    ->where('gym_id', $gymId)
                    ->where('active', true)
                    ->whereNull('deleted_at'),
            ],
            'amount' => ['required', 'integer', 'min:1'],
            'method' => ['required', Rule::in(['cash', 'transfer', 'card', 'online'])],
            'months' => ['nullable', 'integer', 'min:1', 'max:24'],
            'starts_on' => ['nullable', 'date'],
            'ends_on' => ['nullable', 'date', 'after_or_equal:starts_on'],
            'time_slot' => ['nullable', Rule::in(['tarde', 'noche'])],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.required' => 'Elegí un plan.',
            'plan_id.exists' => 'El plan elegido no está disponible.',
            'amount.required' => 'Ingresá el monto cobrado.',
            'amount.integer' => 'El monto va en pesos enteros, sin centavos ni puntos.',
            'amount.min' => 'El monto debe ser mayor a cero.',
            'method.required' => 'Elegí el medio de pago.',
            'ends_on.after_or_equal' => 'La fecha de fin no puede ser anterior a la de inicio.',
        ];
    }
}

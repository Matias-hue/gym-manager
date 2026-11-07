<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ExtendMembershipRequest extends FormRequest
{
    public function authorize(): bool
    {
        // La prórroga solo se puede dar sobre membresías del propio gimnasio
        $membership = $this->route('membership');

        return $membership->member->gym_id === $this->user()->gym_id;
    }

    public function rules(): array
    {
        $membership = $this->route('membership');

        return [
            // Debe ser una fecha futura y posterior al vencimiento de la cuota
            'grace_until' => [
                'required',
                'date',
                'after_or_equal:today',
                'after:'.$membership->ends_on->toDateString(),
            ],
            'notes' => ['required', 'string', 'max:500'],
        ];
    }

    public function attributes(): array
    {
        return [
            'grace_until' => 'fecha de prórroga',
            'notes' => 'nota',
        ];
    }

    public function messages(): array
    {
        return [
            'grace_until.after' => 'La prórroga tiene que ser posterior al vencimiento de la cuota.',
            'grace_until.after_or_equal' => 'La prórroga no puede ser una fecha pasada.',
        ];
    }
}
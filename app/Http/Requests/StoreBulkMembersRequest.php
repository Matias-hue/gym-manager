<?php

namespace App\Http\Requests;

use App\Services\ImportMembers;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBulkMembersRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->gym_id !== null;
    }

    // Acá solo se valida la forma del envío; las reglas de cada fila las aplica ImportMembers
    public function rules(): array
    {
        return [
            'plan_id' => [
                'nullable',
                Rule::exists('plans', 'id')
                    ->where('gym_id', $this->user()->gym_id)
                    ->where('active', true)
                    ->whereNull('deleted_at'),
            ],
            'default_ends_on' => [
                'nullable',
                'string',
                'max:20',
                function (string $attribute, mixed $value, \Closure $fail) {
                    if (filled($value) && ! app(ImportMembers::class)->parseDate($value)) {
                        $fail('El vencimiento para todos no es válido. Escribilo como 15/11 o 15/11/2026.');
                    }
                },
            ],
            'rows' => ['required', 'array', 'max:300'],
            'rows.*.first_name' => ['nullable', 'string', 'max:255'],
            'rows.*.last_name' => ['nullable', 'string', 'max:255'],
            'rows.*.phone' => ['nullable', 'string', 'max:30'],
            'rows.*.plan_id' => ['nullable', 'integer'],
            'rows.*.time_slot' => ['nullable', Rule::in(ImportMembers::SLOTS)],
            'rows.*.ends_on' => ['nullable', 'string', 'max:20'],
            'confirm_duplicates' => ['nullable', 'boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'plan_id.exists' => 'El plan elegido no está disponible.',
            'rows.required' => 'Cargá al menos un socio.',
            'rows.max' => 'Se pueden cargar hasta 300 socios por vez. Guardá estos y seguí con el resto.',
        ];
    }
}

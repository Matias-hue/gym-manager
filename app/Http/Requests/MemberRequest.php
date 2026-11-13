<?php

namespace App\Http\Requests;

use App\Http\Requests\Concerns\ScopesToGym;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
    use ScopesToGym;

    public function authorize(): bool
    {
        return true;
    }

    // Limpia los datos antes de validar
    protected function prepareForValidation(): void
    {
        $clean = [];

        foreach (['first_name', 'last_name'] as $field) {
            if ($this->has($field)) {
                $clean[$field] = trim(preg_replace('/\s+/', ' ', (string) $this->input($field)));
            }
        }

        // Teléfono: solo dígitos, sin 0 inicial ni prefijo del país (54 / 549)
        if (filled($this->input('phone'))) {
            $phone = preg_replace('/\D+/', '', (string) $this->input('phone'));

            if (strlen($phone) > 10 && str_starts_with($phone, '549')) {
                $phone = substr($phone, 3);
            } elseif (strlen($phone) > 10 && str_starts_with($phone, '54')) {
                $phone = substr($phone, 2);
            }

            $phone = ltrim($phone, '0');

            $clean['phone'] = $phone !== '' ? $phone : null;
        }

        // Documento (opcional): sin puntos ni espacios, así la búsqueda lo encuentra igual.
        // Si queda vacío se guarda como "sin documento" (null) y nunca como texto vacío,
        // porque dos textos vacíos contarían como documento repetido.
        if (filled($this->input('document_number'))) {
            $document = preg_replace('/[^0-9A-Za-z]/', '', (string) $this->input('document_number'));

            $clean['document_number'] = $document !== '' ? $document : null;
        }

        $this->merge($clean);
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'regex:/^\d{10}$/'],
            // Único dentro del gimnasio (cuenta también a los socios eliminados, igual que la base).
            // Al editar, el socio no choca consigo mismo.
            'document_number' => [
                'nullable',
                'string',
                'max:20',
                $this->uniqueInGym('members', 'document_number')->ignore($this->route('member')),
            ],
            'email' => ['nullable', 'email', 'max:120'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'emergency_contact' => ['nullable', 'string', 'max:150'],
            'confirm_duplicate' => ['sometimes', 'boolean'],
        ];

        // El estado solo se edita en la ficha, no al dar de alta
        if ($this->route('member')) {
            $rules['status'] = ['required', Rule::in(['active', 'inactive', 'suspended'])];
        }

        return $rules;
    }

    public function messages(): array
    {
        return [
            'required' => 'Completá el campo :attribute.',
            'phone.regex' => 'Cargá el celular con código de área y número, sin 0 ni 15 (por ejemplo 3764123456).',
            'document_number.unique' => 'Ya hay un socio cargado con ese documento en tu gimnasio.',
            'email.email' => 'Ese email no parece válido.',
            'birth_date.date' => 'La fecha de nacimiento no es válida.',
            'birth_date.before' => 'La fecha de nacimiento tiene que ser anterior a hoy.',
            'max' => 'El campo :attribute es demasiado largo.',
        ];
    }

    public function attributes(): array
    {
        return [
            'first_name' => 'nombre',
            'last_name' => 'apellido',
            'phone' => 'teléfono',
            'document_number' => 'documento',
            'birth_date' => 'fecha de nacimiento',
            'emergency_contact' => 'contacto de emergencia',
            'status' => 'estado',
        ];
    }
}
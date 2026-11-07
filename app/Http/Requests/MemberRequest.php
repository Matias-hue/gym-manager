<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class MemberRequest extends FormRequest
{
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

            $clean['phone'] = ltrim($phone, '0');
        }

        // Documento (opcional): sin puntos ni espacios, así la búsqueda lo encuentra igual
        if (filled($this->input('document_number'))) {
            $clean['document_number'] = preg_replace('/[^0-9A-Za-z]/', '', (string) $this->input('document_number'));
        }

        $this->merge($clean);
    }

    public function rules(): array
    {
        $rules = [
            'first_name' => ['required', 'string', 'max:80'],
            'last_name' => ['required', 'string', 'max:80'],
            'phone' => ['nullable', 'regex:/^\d{10}$/'],
            'document_number' => ['nullable', 'string', 'max:20'],
            'email' => ['nullable', 'email', 'max:120'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'emergency_contact' => ['nullable', 'string', 'max:150'],
            'medical_notes' => ['nullable', 'string', 'max:2000'],
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
            'medical_notes' => 'observaciones',
            'status' => 'estado',
        ];
    }
}

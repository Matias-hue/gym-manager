<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreAttendanceRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            // Lo que escribe recepción: documento o nombre
            'code' => ['required_without:member_id', 'nullable', 'string', 'max:80'],
            // Cuando elige un socio de la lista de coincidencias
            'member_id' => ['nullable', 'integer'],
        ];
    }

    public function messages(): array
    {
        return [
            'code.required_without' => 'Escribí el documento o el nombre del socio.',
            'code.max' => 'El texto es demasiado largo.',
        ];
    }
}
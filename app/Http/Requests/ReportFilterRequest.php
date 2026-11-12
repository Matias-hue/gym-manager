<?php

namespace App\Http\Requests;

use App\Models\Gym;
use App\Services\Reports\ReportFilters;
use App\Services\Reports\ReportPeriod;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

class ReportFilterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('owner') ?? false;
    }

    public function rules(): array
    {
        return [
            'tab'     => ['nullable', Rule::in(array_keys(config('reports.tabs')))],
            'range'   => ['nullable', Rule::in(array_keys(config('reports.ranges')))],
            'from'    => ['nullable', 'date'],
            'to'      => ['nullable', 'date', 'after_or_equal:from'],
            'method'  => ['nullable', Rule::in(array_keys(config('reports.methods')))],
            'plan_id' => [
                'nullable',
                'integer',
                Rule::exists('plans', 'id')->where('gym_id', $this->user()?->gym_id),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'to.after_or_equal' => 'La fecha "hasta" no puede ser anterior a "desde".',
            'from.date'         => 'La fecha "desde" no es válida.',
            'to.date'           => 'La fecha "hasta" no es válida.',
            'range.in'          => 'El rango elegido no existe.',
            'method.in'         => 'El método de pago no es válido.',
            'plan_id.exists'    => 'El plan elegido no existe.',
        ];
    }

    public function activeTab(): string
    {
        return $this->input('tab') ?: array_key_first(config('reports.tabs'));
    }

    public function period(): ReportPeriod
    {
        if ($this->filled('from') && $this->filled('to')) {
            return ReportPeriod::make(
                Carbon::parse($this->input('from')),
                Carbon::parse($this->input('to')),
            );
        }

        return ReportPeriod::preset($this->input('range') ?: 'this_month');
    }

    public function filters(): ReportFilters
    {
        return new ReportFilters(
            gym: Gym::query()->findOrFail($this->user()->gym_id),
            period: $this->period(),
            method: $this->filled('method') ? (string) $this->input('method') : null,
            planId: $this->filled('plan_id') ? (int) $this->input('plan_id') : null,
        );
    }
}

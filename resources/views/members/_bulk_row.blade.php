{{--
    Una fila de la tabla de alta masiva.
    Recibe: $i (índice de la fila), $plans, $messages (errores de la fila) y $duplicate (bool).
    Los valores salen de old("rows.$i.campo"), así se conservan al volver con errores.
--}}
@php
    $cell = 'block w-full rounded-lg border bg-white px-2.5 py-2 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200';
    $border = ! empty($messages) ? 'border-red-400' : ($duplicate ? 'border-amber-400' : 'border-stone-200');
@endphp

<tbody data-row class="border-b border-stone-200 last:border-b-0 {{ $duplicate ? 'bg-amber-50/60' : '' }}">
    <tr class="align-top">
        <td class="min-w-36 py-1.5 pl-3 pr-1">
            <input
                type="text"
                name="rows[{{ $i }}][first_name]"
                value="{{ old("rows.{$i}.first_name") }}"
                aria-label="Nombre"
                autocomplete="off"
                class="{{ $cell }} {{ $border }}"
            >
        </td>
        <td class="min-w-36 px-1 py-1.5">
            <input
                type="text"
                name="rows[{{ $i }}][last_name]"
                value="{{ old("rows.{$i}.last_name") }}"
                aria-label="Apellido"
                autocomplete="off"
                class="{{ $cell }} {{ $border }}"
            >
        </td>
        <td class="min-w-36 px-1 py-1.5">
            <input
                type="text"
                inputmode="numeric"
                name="rows[{{ $i }}][phone]"
                value="{{ old("rows.{$i}.phone") }}"
                placeholder="3764123456"
                aria-label="Celular"
                autocomplete="off"
                class="{{ $cell }} {{ $border }}"
            >
        </td>
        <td class="min-w-44 px-1 py-1.5">
            <select name="rows[{{ $i }}][plan_id]" data-plan aria-label="Plan" class="{{ $cell }} {{ $border }}">
                <option value="">Igual que arriba</option>
                @foreach ($plans as $plan)
                    <option
                        value="{{ $plan->id }}"
                        data-capacity="{{ $plan->capacity_per_slot }}"
                        @selected((string) old("rows.{$i}.plan_id") === (string) $plan->id)
                    >{{ $plan->name }}</option>
                @endforeach
            </select>
        </td>
        <td class="min-w-28 px-1 py-1.5">
            <select name="rows[{{ $i }}][time_slot]" data-slot aria-label="Turno" class="{{ $cell }} {{ $border }} disabled:bg-stone-50 disabled:text-stone-300">
                <option value="">—</option>
                <option value="tarde" @selected(old("rows.{$i}.time_slot") === 'tarde')>Tarde</option>
                <option value="noche" @selected(old("rows.{$i}.time_slot") === 'noche')>Noche</option>
            </select>
        </td>
        <td class="min-w-32 px-1 py-1.5">
            <input
                type="text"
                inputmode="numeric"
                name="rows[{{ $i }}][ends_on]"
                value="{{ old("rows.{$i}.ends_on") }}"
                placeholder="15/11"
                aria-label="Vence el"
                autocomplete="off"
                class="{{ $cell }} {{ $border }}"
            >
        </td>
        <td class="w-12 py-1.5 pl-1 pr-3 text-right">
            <button
                type="button"
                data-remove
                aria-label="Quitar fila"
                class="inline-flex size-9 items-center justify-center rounded-lg text-lg text-stone-400 transition hover:bg-stone-100 hover:text-stone-700"
            >×</button>
        </td>
    </tr>

    @if (! empty($messages))
        <tr>
            <td colspan="7" class="px-3 pb-2.5 text-xs font-medium text-red-700">
                {{ implode(' ', $messages) }}
            </td>
        </tr>
    @elseif ($duplicate)
        <tr>
            <td colspan="7" class="px-3 pb-2.5 text-xs font-medium text-amber-800">
                Ya hay un socio con este nombre.
            </td>
        </tr>
    @endif
</tbody>

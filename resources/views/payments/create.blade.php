@php
    // Mismas clases que members/_form
    $input = 'mt-1.5 w-full rounded-lg border border-stone-200 bg-white px-3.5 py-2.5 text-sm text-stone-900 outline-none transition placeholder:text-stone-400 focus:border-stone-400 focus:ring-2 focus:ring-lime-200';
    $label = 'block text-sm font-medium text-stone-700';
    $hint = 'mt-1.5 text-xs text-stone-500';
    $stateStyles = [
        'current' => 'border-emerald-200 bg-emerald-50 text-emerald-900',
        'grace' => 'border-amber-200 bg-amber-50 text-amber-900',
        'expired' => 'border-red-200 bg-red-50 text-red-900',
        'none' => 'border-stone-200 bg-stone-50 text-stone-700',
    ];
@endphp

<x-gym-layout title="Cobrar">
    <section
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-labelledby="payment-title"
    >
        <div class="mx-auto max-w-2xl">

            <a href="{{ route('members.show', $member) }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-500 transition hover:text-stone-900">
                ← Volver a la ficha
            </a>

            <header class="mt-2 border-b border-stone-200 pb-6">
                <h1 id="payment-title" class="font-heading text-3xl font-semibold tracking-tight text-stone-950">
                    Cobrar
                </h1>
                <p class="mt-2 text-sm text-stone-500">
                    {{ $member->first_name }} {{ $member->last_name }}
                    @if ($member->document_number) · DNI {{ $member->document_number }} @endif
                </p>
            </header>

            {{-- Estado actual del socio --}}
            <div class="mt-6 rounded-xl border px-4 py-3 text-sm {{ $stateStyles[$state] ?? $stateStyles['none'] }}">
                @switch($state)
                    @case('current') <strong>Al día</strong> · vence el {{ $current->ends_on->format('d/m/Y') }} @break
                    @case('grace') <strong>En gracia</strong> · venció el {{ $current->ends_on->format('d/m/Y') }} @break
                    @case('expired') <strong>Vencido</strong> · venció el {{ $current->ends_on->format('d/m/Y') }} @break
                    @default <strong>Sin pagos registrados</strong>
                @endswitch
            </div>

            @if ($errors->any())
                <div role="alert" class="mt-4 rounded-lg border border-red-300 bg-red-50 px-4 py-3 text-sm font-medium text-red-800">
                    <ul class="list-disc space-y-1 pl-4">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="mt-6 rounded-xl border border-stone-200 bg-white p-5 sm:p-6">
                <form method="POST" action="{{ route('payments.store', $member) }}" class="space-y-5" id="collect-form"
                      data-occupancy='@json($occupancy)'>
                    @csrf

                    {{-- Plan --}}
                    <div>
                        <label for="plan_id" class="{{ $label }}">Plan</label>
                        <select name="plan_id" id="plan_id" required class="{{ $input }}">
                            <option value="">Elegí un plan…</option>
                            @foreach ($plans as $plan)
                                <option value="{{ $plan->id }}"
                                        data-price="{{ $plan->price }}"
                                        data-capacity="{{ $plan->capacity_per_slot }}"
                                        @selected(old('plan_id') == $plan->id)>
                                    {{ $plan->name }} — ${{ number_format($plan->price, 0, ',', '.') }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Turno (solo planes con cupo) --}}
                    <div id="slot-wrapper" class="hidden">
                        <label for="time_slot" class="{{ $label }}">Turno</label>
                        <select name="time_slot" id="time_slot" class="{{ $input }}">
                            <option value="">Elegí el turno…</option>
                            <option value="tarde" @selected(old('time_slot') === 'tarde')>Tarde</option>
                            <option value="noche" @selected(old('time_slot') === 'noche')>Noche</option>
                        </select>
                        <p id="slot-info" class="{{ $hint }}"></p>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="months" class="{{ $label }}">Meses</label>
                            <input type="number" name="months" id="months" min="1" max="24"
                                   value="{{ old('months', 1) }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="amount" class="{{ $label }}">Monto cobrado ($)</label>
                            <input type="number" name="amount" id="amount" min="1" step="1" required
                                   value="{{ old('amount') }}" class="{{ $input }}">
                            <p class="{{ $hint }}">Se propone según el plan; podés editarlo.</p>
                        </div>
                    </div>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="starts_on" class="{{ $label }}">Desde</label>
                            <input type="date" name="starts_on" id="starts_on"
                                   value="{{ old('starts_on', today()->toDateString()) }}" class="{{ $input }}">
                        </div>
                        <div>
                            <label for="ends_on" class="{{ $label }}">Hasta <span class="font-normal text-stone-400">(opcional)</span></label>
                            <input type="date" name="ends_on" id="ends_on" value="{{ old('ends_on') }}" class="{{ $input }}">
                            <p class="{{ $hint }}">Vacío = se calcula con los meses.</p>
                        </div>
                    </div>

                    {{-- Medio de pago --}}
                    <div>
                        <label for="method" class="{{ $label }}">Medio de pago</label>
                        <select name="method" id="method" required class="{{ $input }}">
                            @foreach (['cash' => 'Efectivo', 'transfer' => 'Transferencia', 'card' => 'Tarjeta', 'online' => 'Online'] as $value => $text)
                                <option value="{{ $value }}" @selected(old('method', 'cash') === $value)>{{ $text }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Nota --}}
                    <div>
                        <label for="notes" class="{{ $label }}">Nota <span class="font-normal text-stone-400">(opcional)</span></label>
                        <textarea name="notes" id="notes" rows="2" maxlength="1000"
                                  class="{{ $input }} py-2">{{ old('notes') }}</textarea>
                    </div>

                    <div class="flex flex-col-reverse gap-2 border-t border-stone-200 pt-6 sm:flex-row sm:justify-end">
                        <a href="{{ route('members.show', $member) }}"
                           class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50">
                            Cancelar
                        </a>
                        <button type="submit" id="submit-btn"
                                class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98] disabled:opacity-60">
                            Registrar cobro
                        </button>
                    </div>
                </form>
            </div>

        </div>
    </section>

    @vite('resources/js/payments/create.js')
</x-gym-layout>

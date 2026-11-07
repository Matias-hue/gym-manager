@php
    $membership = $member->currentMembership;
    $state = $member->state();
    $gymName = $member->gym->name;
    $limit = $membership?->plan?->visits_per_week;

    // Mensaje de WhatsApp según cómo está la cuota
    $firstName = $member->first_name;
    if ($membership && in_array($state, ['grace', 'expired'], true)) {
        $message = "Hola {$firstName}, te escribimos de {$gymName}. Tu cuota venció el "
            .$membership->ends_on->isoFormat('D [de] MMMM')
            .'. ¿Querés pasar a regularizarla? ¡Gracias!';
    } else {
        $message = "Hola {$firstName}, te escribimos de {$gymName}.";
    }
    $whatsapp = $member->whatsappUrl($message);

    $methods = ['cash' => 'Efectivo', 'transfer' => 'Transferencia', 'card' => 'Tarjeta', 'online' => 'Online'];
    $money = fn ($amount) => '$ '.number_format($amount, 0, ',', '.');
@endphp

<x-gym-layout :title="$member->full_name">
    <section
        class="min-h-[calc(100vh-3.5rem)] w-full px-6 py-9 lg:px-10 lg:py-10 xl:px-14"
        aria-labelledby="member-title"
    >
        <div class="mx-auto max-w-7xl">

            <a href="{{ route('members.index') }}" class="inline-flex min-h-11 items-center text-sm font-medium text-stone-500 transition hover:text-stone-900">
                ← Volver a socios
            </a>

            {{-- ENCABEZADO --}}
            <header class="mt-2 flex flex-col gap-5 border-b border-stone-200 pb-7 lg:flex-row lg:items-end lg:justify-between">
                <div>
                    <div class="flex flex-wrap items-center gap-3">
                        <h1 id="member-title" class="font-heading text-3xl font-semibold tracking-tight text-stone-950 sm:text-4xl">
                            {{ $member->full_name }}
                        </h1>
                        @include('members._state', ['state' => $state])

                        @if ($member->status !== 'active')
                            <span class="rounded-full bg-stone-100 px-2.5 py-1 text-xs font-medium text-stone-700">
                                {{ $member->status === 'inactive' ? 'Inactivo' : 'Suspendido' }}
                            </span>
                        @endif
                    </div>

                    <p class="mt-2 text-sm text-stone-500">
                        Socio desde {{ $member->joined_at?->isoFormat('D [de] MMMM [de] YYYY') ?? 'fecha sin cargar' }}
                    </p>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @if (\Illuminate\Support\Facades\Route::has('payments.create'))
                        <a href="{{ route('payments.create', $member) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-950 bg-stone-950 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-stone-800 active:scale-[0.98]">
                            {{ $membership ? 'Registrar pago' : 'Registrar primer pago' }}
                        </a>
                    @endif

                    @if ($whatsapp)
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50 active:scale-[0.98]">
                            Avisar por WhatsApp
                        </a>
                    @endif

                    <a href="{{ route('members.edit', $member) }}" class="inline-flex min-h-11 items-center justify-center rounded-lg border border-stone-300 bg-white px-4 py-2.5 text-sm font-semibold text-stone-950 transition hover:bg-stone-50 active:scale-[0.98]">
                        Editar
                    </a>
                </div>
            </header>

            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-3">

                {{-- DATOS --}}
                <section class="rounded-xl border border-stone-200 bg-white p-5 sm:p-6 lg:col-span-1" aria-labelledby="data-title">
                    <h2 id="data-title" class="font-heading text-xl font-semibold tracking-tight">Datos</h2>

                    <dl class="mt-4 space-y-4 text-sm">
                        <div>
                            <dt class="text-xs font-medium text-stone-500">Celular</dt>
                            <dd class="mt-0.5 text-stone-900">{{ $member->phone ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-stone-500">DNI</dt>
                            <dd class="mt-0.5 text-stone-900">{{ $member->document_number ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-stone-500">Email</dt>
                            <dd class="mt-0.5 break-words text-stone-900">{{ $member->email ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-stone-500">Fecha de nacimiento</dt>
                            <dd class="mt-0.5 text-stone-900">{{ $member->birth_date?->isoFormat('D [de] MMMM [de] YYYY') ?? '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-stone-500">Contacto de emergencia</dt>
                            <dd class="mt-0.5 text-stone-900">{{ $member->emergency_contact ?: '—' }}</dd>
                        </div>
                        <div>
                            <dt class="text-xs font-medium text-stone-500">Observaciones</dt>
                            <dd class="mt-0.5 whitespace-pre-line text-stone-900">{{ $member->medical_notes ?: '—' }}</dd>
                        </div>
                    </dl>
                </section>

                <div class="space-y-6 lg:col-span-2">

                    {{-- CUOTA ACTUAL --}}
                    <section class="rounded-xl border border-stone-200 bg-white p-5 sm:p-6" aria-labelledby="current-title">
                        <h2 id="current-title" class="font-heading text-xl font-semibold tracking-tight">Cuota actual</h2>

                        @if ($membership)
                            <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <p class="text-xs font-medium text-stone-500">Plan</p>
                                    <p class="mt-0.5 text-sm font-medium text-stone-900">
                                        {{ $membership->plan->name }}
                                        @if ($membership->time_slot)
                                            <span class="block text-xs font-normal text-stone-500">Turno {{ $membership->time_slot }}</span>
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs font-medium text-stone-500">Vence</p>
                                    <p class="mt-0.5 text-sm font-medium text-stone-900">
                                        {{ $membership->ends_on->isoFormat('D [de] MMMM [de] YYYY') }}
                                        @if ($membership->grace_until)
                                            <span class="block text-xs font-normal text-stone-500">
                                                Prórroga hasta el {{ $membership->grace_until->isoFormat('D [de] MMMM') }}
                                            </span>
                                        @endif
                                    </p>
                                </div>

                                @if ($limit)
                                    <div>
                                        <p class="text-xs font-medium text-stone-500">Visitas esta semana</p>
                                        <p class="mt-0.5 text-sm font-medium text-stone-900">{{ $visitsThisWeek }} de {{ $limit }}</p>
                                    </div>
                                @endif
                            </div>
                        @else
                            <p class="mt-3 text-sm text-stone-500">
                                Todavía no tiene ninguna cuota registrada.
                            </p>
                        @endif
                    </section>

                    {{-- PAGOS REGISTRADOS --}}
                    <section class="rounded-xl border border-stone-200 bg-white p-5 sm:p-6" aria-labelledby="payments-title">
                        <h2 id="payments-title" class="font-heading text-xl font-semibold tracking-tight">Pagos registrados</h2>

                        @if ($payments->isEmpty())
                            <p class="mt-3 text-sm text-stone-500">Todavía no hay pagos registrados.</p>
                        @else
                            <div class="mt-4 overflow-x-auto">
                                <table class="w-full min-w-[420px] text-left text-sm">
                                    <caption class="sr-only">Últimos pagos del socio</caption>

                                    <thead class="border-b border-stone-200 text-xs font-medium text-stone-500">
                                        <tr>
                                            <th scope="col" class="pb-3 pr-4">Fecha</th>
                                            <th scope="col" class="pb-3 pr-4">Monto</th>
                                            <th scope="col" class="pb-3">Forma de pago</th>
                                        </tr>
                                    </thead>

                                    <tbody class="divide-y divide-stone-100">
                                        @foreach ($payments as $payment)
                                            <tr>
                                                <td class="py-3 pr-4 text-stone-600">
                                                    {{ \Illuminate\Support\Carbon::parse($payment->paid_at)->isoFormat('D MMM YYYY') }}
                                                </td>
                                                <td class="py-3 pr-4 font-medium">{{ $money($payment->amount) }}</td>
                                                <td class="py-3 text-stone-600">{{ $methods[$payment->method] ?? $payment->method }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </section>

                    {{-- HISTORIAL DE CUOTAS --}}
                    @if ($memberships->count() > 1)
                        <section class="rounded-xl border border-stone-200 bg-white p-5 sm:p-6" aria-labelledby="history-title">
                            <h2 id="history-title" class="font-heading text-xl font-semibold tracking-tight">Historial de cuotas</h2>

                            <ul class="mt-4 divide-y divide-stone-100 text-sm">
                                @foreach ($memberships as $item)
                                    <li class="flex flex-wrap items-center justify-between gap-2 py-3">
                                        <span class="font-medium">{{ $item->plan->name }}</span>
                                        <span class="text-stone-500">
                                            {{ $item->starts_on->isoFormat('D MMM') }} al {{ $item->ends_on->isoFormat('D MMM YYYY') }}
                                            · {{ $money($item->price_charged) }}
                                        </span>
                                    </li>
                                @endforeach
                            </ul>
                        </section>
                    @endif

                </div>
            </div>

        </div>
    </section>
</x-gym-layout>

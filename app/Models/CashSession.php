<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;

class CashSession extends Model
{
    protected $guarded = [];

    protected $casts = [
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public const METHOD_LABELS = [
        'cash' => 'Efectivo',
        'transfer' => 'Transferencia',
        'card' => 'Tarjeta',
        'online' => 'Online',
    ];

    public function gym(): BelongsTo
    {
        return $this->belongsTo(Gym::class);
    }

    public function openedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'opened_by');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(CashMovement::class);
    }

    public function isOpen(): bool
    {
        return $this->closed_at === null;
    }

    // Movimientos que cuentan: se descartan los de pagos anulados
    public function validMovements(): HasMany
    {
        return $this->movements()->where(function (Builder $query) {
            $query->whereNull('payment_id')
                ->orWhereHas('payment', fn (Builder $payment) => $payment->whereNull('voided_at'));
        });
    }

    // Ingresos agrupados por forma de pago. Los ingresos manuales (sin pago) son efectivo.
    public function incomeByMethod(): Collection
    {
        return $this->validMovements()
            ->where('type', 'income')
            ->with('payment:id,method')
            ->get()
            ->groupBy(fn (CashMovement $movement) => $movement->payment?->method ?? 'cash')
            ->map(fn (Collection $group) => (int) $group->sum('amount'));
    }

    /**
     * Cuentas de la caja. Solo el efectivo entra al cajón: transferencias,
     * tarjeta y online se muestran aparte y no cuentan para el efectivo esperado.
     */
    public function summary(): array
    {
        $byMethod = $this->incomeByMethod();
        $cashIncome = (int) $byMethod->get('cash', 0);
        $expenses = (int) $this->validMovements()->where('type', 'expense')->sum('amount');
        $opening = (int) $this->opening_amount;

        return [
            'opening' => $opening,
            'cash_income' => $cashIncome,
            'expenses' => $expenses,
            'expected' => $opening + $cashIncome - $expenses,
            'other_methods' => $byMethod->except('cash'),
        ];
    }

    // Contado menos esperado: negativo = faltante, positivo = sobrante
    public function difference(): ?int
    {
        if ($this->counted_amount === null || $this->expected_amount === null) {
            return null;
        }

        return (int) $this->counted_amount - (int) $this->expected_amount;
    }
}

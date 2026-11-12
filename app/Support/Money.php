<?php

namespace App\Support;

/**
 * Formato de importes. Los montos se guardan como enteros en pesos.
 */
final class Money
{
    public static function format(int|float|null $amount): string
    {
        $amount = (float) ($amount ?? 0);

        return ($amount < 0 ? '-' : '').'$ '.number_format(abs($amount), 0, ',', '.');
    }

    public static function signed(int|float $amount): string
    {
        $sign = $amount > 0 ? '+' : '';

        return $sign.self::format($amount);
    }
}

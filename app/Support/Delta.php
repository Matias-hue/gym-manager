<?php

namespace App\Support;

final class Delta
{
    /**
     * Variación porcentual contra el período anterior.
     * Devuelve null cuando no hay base de comparación (anterior en cero).
     */
    public static function percent(int|float $current, int|float $previous): ?float
    {
        if ($previous == 0) {
            return null;
        }

        return round((($current - $previous) / abs($previous)) * 100, 1);
    }
}

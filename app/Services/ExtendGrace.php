<?php

namespace App\Services;

use App\Models\Membership;
use Illuminate\Support\Carbon;

class ExtendGrace
{
    /**
     * Guarda la prórroga (grace_until) y deja la nota en la membresía.
     * Las notas anteriores se conservan: cada prórroga se agrega como una línea nueva.
     */
    public function handle(Membership $membership, string $graceUntil, string $note, string $userName): Membership
    {
        $date = Carbon::parse($graceUntil);

        $line = sprintf(
            '[%s] Prórroga hasta %s (%s): %s',
            today()->format('d/m/Y'),
            $date->format('d/m/Y'),
            $userName,
            $note
        );

        $membership->update([
            'grace_until' => $date,
            'notes' => trim(($membership->notes ? $membership->notes."\n" : '').$line),
        ]);

        return $membership;
    }
}
<?php

namespace App\Support;

/**
 * Plantillas de mensajes definidas en config/reports.php (clave "whatsapp").
 */
final class WhatsappMessage
{
    public static function make(string $key, array $replace): string
    {
        $template = (string) config("reports.whatsapp.{$key}", '');

        $pairs = [];
        foreach ($replace as $name => $value) {
            $pairs[':'.$name] = (string) $value;
        }

        return strtr($template, $pairs);
    }
}

<?php

namespace App\Service;

/**
 * Klasa do zarządzania tłumaczeniami.
 * Ładuje tłumaczenia z pliku JSON i zapewnia dostęp do nich.
 */

class LocaleManager
{
    private array $translations;

    public function __construct(string $langFile)
    {
        $this->translations = json_decode(file_get_contents($langFile), true) ?? [];
    }

    public function get(string $key, array $replace = []): string
    {
        $message = $this->translations[$key] ?? $key;

        foreach ($replace as $placeholder => $value) {
            $message = str_replace('{' . $placeholder . '}', $value, $message);
        }

        return $message;
    }
}
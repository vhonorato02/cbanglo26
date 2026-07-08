<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Utilitários de string: normalização, slug e protocolo.
 */
final class Str
{
    /** Normaliza espaços e remove caracteres de controle. */
    public static function clean(string $value): string
    {
        $value = preg_replace('/[\x00-\x1F\x7F]/u', '', $value) ?? '';
        $value = preg_replace('/\s+/u', ' ', $value) ?? '';
        return trim($value);
    }

    /** Slug ASCII minúsculo (para chaves de duplicidade). */
    public static function slug(string $value): string
    {
        $value = mb_strtolower(self::clean($value), 'UTF-8');
        if (function_exists('transliterator_transliterate')) {
            $value = transliterator_transliterate('Any-Latin; Latin-ASCII', $value) ?? $value;
        } else {
            $value = strtr($value, [
                'á' => 'a', 'à' => 'a', 'ã' => 'a', 'â' => 'a', 'ä' => 'a',
                'é' => 'e', 'è' => 'e', 'ê' => 'e', 'ë' => 'e',
                'í' => 'i', 'ì' => 'i', 'î' => 'i', 'ï' => 'i',
                'ó' => 'o', 'ò' => 'o', 'õ' => 'o', 'ô' => 'o', 'ö' => 'o',
                'ú' => 'u', 'ù' => 'u', 'û' => 'u', 'ü' => 'u',
                'ç' => 'c', 'ñ' => 'n',
            ]);
        }
        $value = preg_replace('/[^a-z0-9]+/', '-', $value) ?? '';
        return trim($value, '-');
    }

    /**
     * Protocolo único e não previsível: CB26-XXXX-XXXX.
     * Alfabeto sem caracteres ambíguos (0/O, 1/I/L).
     */
    public static function protocolo(string $prefix = 'CB26'): string
    {
        $alphabet = '23456789ABCDEFGHJKMNPQRSTUVWXYZ';
        $block = static function () use ($alphabet): string {
            $out = '';
            for ($i = 0; $i < 4; $i++) {
                $out .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
            return $out;
        };
        return $prefix . '-' . $block() . '-' . $block();
    }

    /** Mantém somente dígitos (telefones). */
    public static function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}

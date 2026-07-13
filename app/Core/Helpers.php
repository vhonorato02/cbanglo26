<?php

/**
 * Funções auxiliares globais (escaping, URL, formatação).
 * Compatível com PHP 7.1+
 */

// Polyfills para funções de string (PHP 8.0+)
if (!function_exists('str_contains')) {
    function str_contains($haystack, $needle) {
        return strpos($haystack, $needle) !== false;
    }
}

if (!function_exists('str_starts_with')) {
    function str_starts_with($haystack, $needle) {
        return strpos($haystack, $needle) === 0;
    }
}

if (!function_exists('str_ends_with')) {
    function str_ends_with($haystack, $needle) {
        return $needle === '' || substr_compare($haystack, $needle, -strlen($needle)) === 0;
    }
}

if (!function_exists('e')) {
    /** Escapa saída para HTML — proteção XSS. */
    function e($value) {
        $value = $value ?? '';
        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('csv_cell')) {
    /** Impede que o Excel interprete conteúdo exportado como fórmula. */
    function csv_cell($value) {
        $value = (string) $value;
        return preg_match('/^[=+\-@\t\r]/', $value) ? "'" . $value : $value;
    }
}

if (!function_exists('url')) {
    /** URL absoluta (usa APP_URL quando definida). */
    function url($path = '') {
        $base = \App\Core\Env::get('APP_URL', '');
        if ($base === '') {
            $scheme = \App\Core\Session::isHttps() ? 'https' : 'http';
            $host = isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : 'localhost';
            $base = $scheme . '://' . $host;
        }
        return rtrim($base, '/') . '/' . ltrim($path, '/');
    }
}

if (!function_exists('asset')) {
    /** URL de asset com cache busting pelo mtime do arquivo. */
    function asset($path) {
        $file = BASE_PATH . '/public/' . ltrim($path, '/');
        $version = is_file($file) ? (string) filemtime($file) : '1';
        return url($path) . '?v=' . $version;
    }
}

if (!function_exists('redirect')) {
    function redirect($path, $code = 302) {
        header('Location: ' . (str_starts_with($path, 'http') ? $path : url($path)), true, $code);
        exit;
    }
}

if (!function_exists('data_br')) {
    /** Converte datas Y-m-d / Y-m-d H:i:s para exibição brasileira. */
    function data_br($date, $withTime = false) {
        if ($date === null || $date === '') {
            return '—';
        }
        $ts = strtotime($date);
        if ($ts === false) {
            return '—';
        }
        return date($withTime ? 'd/m/Y H:i' : 'd/m/Y', $ts);
    }
}

if (!function_exists('hora_br')) {
    /** Formata "09:00" como "9h" e "09:30" como "9h30" (estilo da campanha). */
    function hora_br($hora) {
        if ($hora === null || !preg_match('/^(\d{1,2}):(\d{2})/', $hora, $m)) {
            return '';
        }
        $h = (string) (int) $m[1];
        return $m[2] === '00' ? $h . 'h' : $h . 'h' . $m[2];
    }
}

if (!function_exists('json_response')) {
    function json_response($data, $status = 200) {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        exit;
    }
}

if (!function_exists('wants_json')) {
    function wants_json() {
        $accept = $_SERVER['HTTP_ACCEPT'] ?? '';
        $requestedWith = $_SERVER['HTTP_X_REQUESTED_WITH'] ?? '';
        return str_contains($accept, 'application/json')
            || strcasecmp($requestedWith, 'XMLHttpRequest') === 0;
    }
}

if (!function_exists('client_ip_hash')) {
    /**
     * Hash do IP do visitante (LGPD: não armazenamos o IP em claro).
     * O hash usa a APP_KEY como sal para impedir reversão por força bruta.
     */
    function client_ip_hash() {
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $key = \App\Core\Env::get('APP_KEY', 'cb-fallback-key');
        return hash_hmac('sha256', $ip, $key);
    }
}

<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Renderizador de views PHP com layout.
 * Compatível com PHP 7.1+
 */
final class View
{
    public static function render($template, $data = [], $layout = null) {
        $file = BASE_PATH . '/app/Views/' . $template . '.php';
        if (!is_file($file)) {
            throw new \RuntimeException("View não encontrada: {$template}");
        }
        extract($data, EXTR_SKIP);
        ob_start();
        require $file;
        $content = (string) ob_get_clean();

        if ($layout !== null) {
            $layoutFile = BASE_PATH . '/app/Views/layouts/' . $layout . '.php';
            if (!is_file($layoutFile)) {
                throw new \RuntimeException("Layout não encontrado: {$layout}");
            }
            ob_start();
            require $layoutFile;
            $content = (string) ob_get_clean();
        }
        return $content;
    }

    public static function show($template, $data = [], $layout = null) {
        echo self::render($template, $data, $layout);
    }
}

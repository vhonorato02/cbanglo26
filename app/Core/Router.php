<?php

declare(strict_types=1);

namespace App\Core;

/**
 * Roteador leve com parâmetros nomeados ({slug}) e middlewares.
 * Compatível com PHP 7.1+
 */
final class Router
{
    /** @var array */
    private $routes = [];

    /** @var callable|null */
    private $notFound = null;

    public function __construct() {
        $this->routes = [];
        $this->notFound = null;
    }

    public function get($pattern, $handler, $middleware = []) {
        $this->add('GET', $pattern, $handler, $middleware);
    }

    public function post($pattern, $handler, $middleware = []) {
        $this->add('POST', $pattern, $handler, $middleware);
    }

    public function add($method, $pattern, $handler, $middleware = []) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'handler' => $handler,
            'middleware' => $middleware,
        ];
    }

    public function setNotFound($handler) {
        $this->notFound = $handler;
    }

    public function dispatch($method, $uri) {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        $path = '/' . trim($path, '/');

        foreach ($this->routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }
            $regex = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route['pattern']);
            $regex = '#^' . $regex . '$#u';
            if (!preg_match($regex, $path, $matches)) {
                continue;
            }
            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $params = array_map('rawurldecode', $params);

            foreach ($route['middleware'] as $mw) {
                // Middleware retorna false para interromper (já respondeu)
                if ($mw() === false) {
                    return;
                }
            }

            $handler = $route['handler'];
            if (is_array($handler) && is_string($handler[0])) {
                $handler = [new $handler[0](), $handler[1]];
            }
            $handler(...array_values($params));
            return;
        }

        http_response_code(404);
        if ($this->notFound !== null) {
            ($this->notFound)();
        } else {
            echo 'Página não encontrada.';
        }
    }
}

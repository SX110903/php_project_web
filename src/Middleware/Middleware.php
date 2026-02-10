<?php

namespace App\Middleware;

/**
 * Middleware Interface
 * Define el contrato para todos los middlewares
 */
interface Middleware
{
    /**
     * Maneja la petición
     */
    public function handle(callable $next): mixed;
}

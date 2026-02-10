<?php

/**
 * PSR-4 Autoloader
 * Carga automáticamente las clases del namespace App
 */

spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Iniciar sesión segura
if (session_status() === PHP_SESSION_NONE) { 
    $config = require __DIR__ . '/config.php';

    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', 0); // Cambiar a 1 en producción con HTTPS
    ini_set('session.cookie_samesite', 'Strict');
    ini_set('session.gc_maxlifetime', $config['security']['session_lifetime']);

    session_name($config['security']['session_name']);
    session_start();

    // Regenerar ID de sesión periódicamente asi evitamos cargarla la base de datos
    if (!isset($_SESSION['last_regeneration'])) {
        $_SESSION['last_regeneration'] = time();
    } elseif (time() - $_SESSION['last_regeneration'] > 300) { // Cada 5 minutos
        session_regenerate_id(true);
        $_SESSION['last_regeneration'] = time();
    }
}

// Configurar zona horaria
$config = require __DIR__ . '/config.php';
date_default_timezone_set($config['app']['timezone']);

// Error reporting según entorno
if ($config['app']['env'] === 'production') {
    error_reporting(0);
    ini_set('display_errors', 0);
} else {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

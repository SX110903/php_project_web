<?php

/**
 * Logout Page
 */

require_once __DIR__ . '/../autoload.php';

use App\Controllers\AuthController;

$controller = new AuthController();
$controller->logout();

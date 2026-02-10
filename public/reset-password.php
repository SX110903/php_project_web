<?php

/**
 * Reset Password Page
 */

require_once __DIR__ . '/../autoload.php';

use App\Controllers\AuthController;

$controller = new AuthController();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $controller->resetPassword();
} else {
    $controller->showResetPassword();
}

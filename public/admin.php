<?php

/**
 * Admin Page
 */

require_once __DIR__ . '/../autoload.php';

use App\Controllers\DashboardController;

$controller = new DashboardController();
$controller->admin();

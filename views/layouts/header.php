<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    <?php
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $routeBase = '';
    $publicPos = strpos($scriptName, '/public/');

    if ($publicPos !== false) {
        $routeBase = substr($scriptName, 0, $publicPos + 7);
    }
    $assetBase = $routeBase;
    ?>
    <title><?= $title ?? 'Secure App' ?></title>
    <link rel="stylesheet" href="<?= $assetBase ?>/css/main.css">
    <link rel="stylesheet" href="<?= $assetBase ?>/css/forms.css">
    <?= $extraCss ?? '' ?>
</head>
<body>
    <script>
        window.APP_BASE = <?= json_encode($routeBase) ?>;
    </script>
    <div class="wrapper">

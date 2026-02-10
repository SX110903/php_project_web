<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="csrf-token" content="<?= $csrf_token ?? '' ?>">
    <?php
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $assetBase = str_starts_with($scriptName, '/public/') ? '/public' : '';
    ?>
    <title><?= $title ?? 'Secure App' ?></title>
    <link rel="stylesheet" href="<?= $assetBase ?>/css/main.css">
    <link rel="stylesheet" href="<?= $assetBase ?>/css/forms.css">
    <?= $extraCss ?? '' ?>
</head>
<body>
    <div class="wrapper">

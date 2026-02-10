    </div>
    <?php
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $assetBase = '';
    $publicPos = strpos($scriptName, '/public/');

    if ($publicPos !== false) {
        $assetBase = substr($scriptName, 0, $publicPos + 7);
    }
    ?>
    <script src="<?= $assetBase ?>/js/main.js"></script>
    <script src="<?= $assetBase ?>/js/validation.js"></script>
    <?= $extraJs ?? '' ?>
</body>
</html>

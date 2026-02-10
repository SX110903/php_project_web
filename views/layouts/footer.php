    </div>
    <?php
    $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
    $assetBase = str_starts_with($scriptName, '/public/') ? '/public' : '';
    ?>
    <script src="<?= $assetBase ?>/js/main.js"></script>
    <script src="<?= $assetBase ?>/js/validation.js"></script>
    <?= $extraJs ?? '' ?>
</body>
</html>

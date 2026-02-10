    </div>
    <?php $assetBase = $routeBase ?? ''; ?>
    <script src="<?= $assetBase ?>/js/main.js"></script>
    <script src="<?= $assetBase ?>/js/validation.js"></script>
    <?= $extraJs ?? '' ?>
</body>
</html>

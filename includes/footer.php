<?php if (($layout ?? 'public') === 'public'): ?>
<footer class="site-footer">
    <div class="container footer-main">
        <a class="navbar-brand questgo-brand footer-brand" href="<?= $baseUrl ?>/index.php">
            <span class="brand-mark" aria-hidden="true">Q</span>
            <span>quest<span class="brand-accent">go</span><small>TRAVEL INDIA, YOUR WAY</small></span>
        </a>
        <p>For all the roads that lead you somewhere.</p>
        <a href="<?= $baseUrl ?>/index.php#home" class="footer-top-link">Back to top <span aria-hidden="true">&uarr;</span></a>
    </div>
    <div class="container footer-bottom">
        <span>&copy; <?= date('Y') ?> QuestGo. Made for journeys across India.</span>
        <span>Plan close. Travel curious.</span>
    </div>
</footer>
<?php else: ?>
<footer class="dashboard-footer"><span>QuestGo travel workspace</span><span>Stored suggestions only · no live location, traffic or availability services</span></footer>
<?php endif; ?>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>
<script src="<?= $baseUrl ?>/assets/js/main.js"></script>
<?php foreach (($pageScripts ?? []) as $script): ?>
<script src="<?= $baseUrl ?>/<?= htmlspecialchars($script, ENT_QUOTES, 'UTF-8') ?>"></script>
<?php endforeach; ?>
</body>
</html>

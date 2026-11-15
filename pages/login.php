<?php
require_once __DIR__ . '/../backend/auth.php';
if (questgo_current_user()) {
    questgo_redirect(questgo_current_user()['role'] === 'admin' ? 'pages/admin/dashboard.php' : 'pages/user/dashboard.php');
}
$pageFlash = questgo_take_flash();
$pageTitle = 'Log in | QuestGo';
$pageStyles = ['assets/css/login.css'];
$pageScripts = ['assets/js/login.js'];
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-page">
    <section class="auth-visual login-visual">
        <a class="navbar-brand questgo-brand auth-brand" href="<?= $baseUrl ?>/index.php"><span class="brand-mark" aria-hidden="true">Q</span><span>quest<span class="brand-accent">go</span><small>TRAVEL INDIA, YOUR WAY</small></span></a>
        <div class="auth-visual-copy"><p class="eyebrow text-white">WELCOME BACK</p><h1>Your next<br>great journey<br>is waiting.</h1><p>Pick up where your travel inspiration left off.</p></div>
        <span class="auth-image-caption">India, one journey at a time.</span>
    </section>
    <section class="auth-form-side">
        <div class="auth-form-wrap">
            <a class="auth-back-link" href="<?= $baseUrl ?>/index.php">← Back to QuestGo</a>
            <p class="eyebrow">YOUR TRAVEL SPACE</p><h2>Log in</h2><p class="auth-subtitle">Welcome back. Enter your details to continue.</p>
            <?php if ($pageFlash): ?><div class="alert alert-<?= $pageFlash['type'] === 'success' ? 'success' : 'danger' ?>" role="alert"><?= htmlspecialchars($pageFlash['message'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <form class="auth-form" action="<?= $baseUrl ?>/backend/login-process.php" method="post">
                <?= questgo_csrf_field() ?>
                <label for="loginEmail">Email address</label>
                <input class="form-control" id="loginEmail" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                <div class="password-label-row"><label for="loginPassword">Password</label></div>
                <div class="password-input-wrap"><input class="form-control" id="loginPassword" type="password" name="password" placeholder="Enter your password" autocomplete="current-password" required><button type="button" class="password-toggle" data-password-toggle="loginPassword" aria-label="Show password">Show</button></div>
                <div class="form-check remember-row"><input class="form-check-input" id="rememberMe" type="checkbox" name="remember"><label class="form-check-label" for="rememberMe">Remember me</label></div>
                <button class="btn btn-auth-submit" type="submit">Log in <span aria-hidden="true">→</span></button>
                <p class="form-status" aria-live="polite"></p>
            </form>
            <p class="auth-switch">New to QuestGo? <a href="<?= $baseUrl ?>/pages/register.php">Create an account</a></p>
            <p class="auth-preview-note">Your account is protected by secure session-based sign-in.</p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>

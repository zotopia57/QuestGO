<?php
require_once __DIR__ . '/../backend/auth.php';
$pageFlash = questgo_take_flash();
$pageTitle = 'Create an account | QuestGo';
$pageStyles = ['assets/css/login.css', 'assets/css/register.css'];
$pageScripts = ['assets/js/login.js', 'assets/js/register.js'];
require __DIR__ . '/../includes/header.php';
?>
<main class="auth-page register-page">
    <section class="auth-visual register-visual">
        <a class="navbar-brand questgo-brand auth-brand" href="<?= $baseUrl ?>/index.php"><span class="brand-mark" aria-hidden="true">Q</span><span>quest<span class="brand-accent">go</span><small>TRAVEL INDIA, YOUR WAY</small></span></a>
        <div class="auth-visual-copy"><p class="eyebrow text-white">A LITTLE CLOSER TO GO</p><h1>Make every<br>journey feel<br>like yours.</h1><p>Save your ideas, plan your route and leave room for discovery.</p></div>
        <span class="auth-image-caption">Start with a place. Go from there.</span>
    </section>
    <section class="auth-form-side">
        <div class="auth-form-wrap">
            <a class="auth-back-link" href="<?= $baseUrl ?>/index.php">← Back to QuestGo</a>
            <p class="eyebrow">BEGIN YOUR QUEST</p><h2>Create account</h2><p class="auth-subtitle">Set up your space for journeys across India.</p>
            <?php if ($pageFlash): ?><div class="alert alert-<?= $pageFlash['type'] === 'success' ? 'success' : 'danger' ?>" role="alert"><?= htmlspecialchars($pageFlash['message'], ENT_QUOTES, 'UTF-8') ?></div><?php endif; ?>
            <form class="auth-form" action="<?= $baseUrl ?>/backend/register-process.php" method="post">
                <?= questgo_csrf_field() ?>
                <label for="fullName">Full name</label><input class="form-control" id="fullName" type="text" name="full_name" placeholder="Your name" autocomplete="name" required maxlength="100">
                <label for="registerEmail">Email address</label><input class="form-control" id="registerEmail" type="email" name="email" placeholder="you@example.com" autocomplete="email" required>
                <label for="registerPassword">Password</label>
                <div class="password-input-wrap"><input class="form-control" id="registerPassword" type="password" name="password" placeholder="At least 8 characters" autocomplete="new-password" minlength="8" required><button type="button" class="password-toggle" data-password-toggle="registerPassword" aria-label="Show password">Show</button></div>
                <label for="confirmPassword">Confirm password</label><input class="form-control" id="confirmPassword" type="password" name="confirm_password" placeholder="Enter your password again" autocomplete="new-password" required>
                <div class="form-check terms-row"><input class="form-check-input" id="terms" type="checkbox" name="terms" value="1" required><label class="form-check-label" for="terms">I agree to the terms and conditions.</label></div>
                <button class="btn btn-auth-submit" type="submit">Create account <span aria-hidden="true">→</span></button>
                <p class="form-status" aria-live="polite"></p>
            </form>
            <p class="auth-switch">Already registered? <a href="<?= $baseUrl ?>/pages/login.php">Log in</a></p>
            <p class="auth-preview-note">Your password is securely hashed before it is stored. New public accounts receive the user role.</p>
        </div>
    </section>
</main>
<?php require __DIR__ . '/../includes/footer.php'; ?>

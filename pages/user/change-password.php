<?php
$layout = 'user'; $activePage = 'password'; $pageTitle = 'Change password | QuestGo';
$pageHeading = 'Change password'; $pageEyebrow = 'ACCOUNT SECURITY'; $pageIntro = 'Use a strong, unique password for your account.';
$pageStyles = ['assets/css/other-pages.css']; $pageScripts = ['assets/js/user.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="panel-card password-panel"><div class="panel-heading"><div><p class="eyebrow">KEEP YOUR ACCOUNT SAFE</p><h2>Choose a new password</h2></div></div><form class="app-form" action="<?= $baseUrl ?>/backend/change-password-process.php" method="post"><?= questgo_csrf_field() ?>
    <label for="currentPassword">Current password</label><input class="form-control" id="currentPassword" name="current_password" type="password" autocomplete="current-password" required>
    <label for="newPassword">New password</label><input class="form-control" id="newPassword" name="new_password" type="password" minlength="8" autocomplete="new-password" required>
    <label for="confirmNewPassword">Confirm new password</label><input class="form-control" id="confirmNewPassword" name="confirm_password" type="password" minlength="8" autocomplete="new-password" required>
    <p class="password-hint">Use at least 8 characters. Avoid reusing a password from another account.</p><button class="btn btn-primary-action" type="submit">Update password</button><p class="form-status" aria-live="polite"></p>
</form></section>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

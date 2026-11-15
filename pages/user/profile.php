<?php
$layout = 'user'; $activePage = 'profile'; $pageTitle = 'Your profile | QuestGo';
$pageHeading = 'Your profile'; $pageEyebrow = 'ACCOUNT DETAILS'; $pageIntro = 'Review the personal details associated with your travel space.';
$pageStyles = ['assets/css/other-pages.css'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-4"><div class="col-lg-4"><section class="profile-card"><div class="profile-avatar"><?= htmlspecialchars(strtoupper(substr($currentUser['name'], 0, 1)), ENT_QUOTES, 'UTF-8') ?></div><h2><?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?></h2><p>QuestGo member</p><span class="profile-status">Active account</span><div class="profile-meta"><span>Email <strong><?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?></strong></span></div></section></div>
<div class="col-lg-8"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">YOUR DETAILS</p><h2>Profile information</h2></div></div><form class="app-form" action="<?= $baseUrl ?>/backend/profile-process.php" method="post"><?= questgo_csrf_field() ?><label for="profileName">Full name</label><input id="profileName" class="form-control" name="name" value="<?= htmlspecialchars($currentUser['name'], ENT_QUOTES, 'UTF-8') ?>" maxlength="100" required><label for="profileEmail">Email address</label><input id="profileEmail" class="form-control" name="email" type="email" value="<?= htmlspecialchars($currentUser['email'], ENT_QUOTES, 'UTF-8') ?>" maxlength="254" required><button class="btn btn-primary-action" type="submit">Update profile</button><p class="form-status" aria-live="polite"></p></form></section></div></div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

<?php
$layout = $layout ?? 'user';
require_once __DIR__ . '/../backend/auth.php';
$currentUser = questgo_require_role($layout === 'admin' ? 'admin' : 'user');
$pageFlash = questgo_take_flash();
$pageStyles = array_merge(['assets/css/portal.css'], $pageStyles ?? []);
require __DIR__ . '/header.php';
?>
<div class="dashboard-layout">
    <?php require __DIR__ . (($layout === 'admin') ? '/admin-sidebar.php' : '/user-sidebar.php'); ?>
    <main class="dashboard-main">
        <div class="dashboard-topbar"><span class="topbar-context"><?= $layout === 'admin' ? 'QuestGo / Admin' : 'QuestGo / My travel space' ?></span><span class="preview-pill"><i></i> Connected workspace</span></div>
        <div class="dashboard-content" data-csrf-token="<?= htmlspecialchars(questgo_csrf_token(), ENT_QUOTES, 'UTF-8') ?>" data-base-url="<?= htmlspecialchars($baseUrl, ENT_QUOTES, 'UTF-8') ?>">
            <div class="page-heading">
                <div><p class="eyebrow"><?= htmlspecialchars($pageEyebrow ?? 'YOUR TRAVEL SPACE', ENT_QUOTES, 'UTF-8') ?></p><h1><?= htmlspecialchars($pageHeading ?? $pageTitle, ENT_QUOTES, 'UTF-8') ?></h1><?php if (!empty($pageIntro)): ?><p><?= htmlspecialchars($pageIntro, ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?></div>
            </div>
            <?php if ($pageFlash): ?>
                <div class="alert alert-<?= $pageFlash['type'] === 'success' ? 'success' : 'danger' ?>" role="alert"><?= htmlspecialchars($pageFlash['message'], ENT_QUOTES, 'UTF-8') ?></div>
            <?php endif; ?>

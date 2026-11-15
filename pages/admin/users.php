<?php
$layout = 'admin'; $activePage = 'users'; $pageTitle = 'Manage users | QuestGo';
$pageHeading = 'Manage users'; $pageEyebrow = 'ACCOUNT DIRECTORY'; $pageIntro = 'Review user accounts and their current access status.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/admin.js', 'assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="panel-card"><div class="admin-table-tools"><label class="filter-search"><span aria-hidden="true">⌕</span><input id="adminUserSearch" type="search" placeholder="Search name or email" aria-label="Search users"></label><span class="muted-small">Accounts in QuestGo</span></div>
<div class="table-responsive"><table class="table app-table admin-user-table"><thead><tr><th>User</th><th>Email</th><th>Joined</th><th>Journeys</th><th>Status</th><th>Action</th></tr></thead><tbody><tr><td colspan="6">Loading user accounts…</td></tr></tbody></table></div><p class="inline-note">Administrator accounts are protected from last-admin lockout and cannot be changed through public registration.</p></section>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

<?php
$layout = 'admin'; $activePage = 'dashboard'; $pageTitle = 'Admin dashboard | QuestGo';
$pageHeading = 'Good morning, admin.'; $pageEyebrow = 'SITE OVERVIEW'; $pageIntro = 'A current snapshot of QuestGo content and registrations.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="admin-welcome"><div><p class="eyebrow text-white">QUESTGO ADMIN</p><h2>Small steps make<br>the whole journey.</h2><p>Manage the content that helps travellers find their way.</p></div><div class="admin-orbit" aria-hidden="true">Q</div></section>
<div class="row g-3 admin-stats">
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon">♙</span><small>Registered users</small><strong>—</strong><span>Loading…</span></article></div>
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon pink">⌖</span><small>Destinations</small><strong>—</strong><span>Loading…</span></article></div>
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon blue">✳</span><small>Activities</small><strong>—</strong><span>Loading…</span></article></div>
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon gold">↗</span><small>Journeys</small><strong>—</strong><span>Loading…</span></article></div>
</div>
<div class="row g-4 admin-dashboard-lower">
    <div class="col-xl-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">LATEST SIGN-UPS</p><h2>Recent registrations</h2></div><a href="<?= $baseUrl ?>/pages/admin/users.php">Manage users <span aria-hidden="true">→</span></a></div>
        <div class="table-responsive"><table class="table app-table"><thead><tr><th>Name</th><th>Email</th><th>Joined</th><th>Status</th></tr></thead><tbody data-admin-recent-users><tr><td colspan="4">Loading registrations…</td></tr></tbody></table></div>
    </section></div>
    <div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">CONTENT SNAPSHOT</p><h2>Destinations by region</h2></div></div><div class="stat-bars" data-admin-regions><p class="inline-note">Loading destination counts…</p></div><p class="inline-note">Counts reflect stored destination records.</p></section></div>
</div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

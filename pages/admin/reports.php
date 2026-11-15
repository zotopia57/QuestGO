<?php
$layout = 'admin'; $activePage = 'reports'; $pageTitle = 'Reports and statistics | QuestGo';
$pageHeading = 'Reports & statistics'; $pageEyebrow = 'A GLANCE AT THE PLATFORM'; $pageIntro = 'Review counts from the stored QuestGo records.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-3 admin-stats"><div class="col-sm-6 col-xl-3"><article class="stat-card"><small>Registered users</small><strong>—</strong><span>Loading…</span></article></div><div class="col-sm-6 col-xl-3"><article class="stat-card"><small>Destinations</small><strong>—</strong><span>Loading…</span></article></div><div class="col-sm-6 col-xl-3"><article class="stat-card"><small>Activities</small><strong>—</strong><span>Loading…</span></article></div><div class="col-sm-6 col-xl-3"><article class="stat-card"><small>Journeys</small><strong>—</strong><span>Loading…</span></article></div></div>
<div class="row g-4 reports-panels"><div class="col-lg-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">DESTINATION DIRECTORY</p><h2>Places by region</h2></div></div><div class="report-bars" data-admin-regions><p class="inline-note">Loading destination counts…</p></div></section></div>
<div class="col-lg-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">DESTINATION CATEGORIES</p><h2>Stored places by category</h2></div></div><div class="report-category-list" data-admin-report-categories><p class="inline-note">Loading category counts…</p></div></section></div></div>
<p class="inline-note">Statistics show stored records only and are not user behaviour or live service metrics.</p>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

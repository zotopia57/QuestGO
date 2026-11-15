<?php
$layout = 'admin'; $activePage = 'journeys'; $pageTitle = 'Manage journeys | QuestGo';
$pageHeading = 'Manage journeys'; $pageEyebrow = 'TRAVEL ACTIVITY'; $pageIntro = 'Review stored journey records in read-only mode.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/admin.js', 'assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="panel-card"><div class="admin-table-tools"><label class="filter-search"><span aria-hidden="true">⌕</span><input id="adminJourneySearch" type="search" placeholder="Search journey, destination or user" aria-label="Search journeys"></label><span class="muted-small">Dates DD-MM-YYYY · view only</span></div><div class="table-responsive"><table class="table app-table"><thead><tr><th>Journey</th><th>Traveller</th><th>Route</th><th>Dates</th><th>Budget</th><th>Status</th></tr></thead><tbody data-admin-journeys><tr><td colspan="6">Loading journeys…</td></tr></tbody></table></div><p class="inline-note">Admin journey access is read-only and limited to operational overview information.</p></section>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

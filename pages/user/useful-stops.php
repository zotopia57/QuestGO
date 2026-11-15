<?php
$layout = 'user'; $activePage = 'stops'; $pageTitle = 'Useful stops | QuestGo';
$pageHeading = 'Useful stops'; $pageEyebrow = 'A GOOD STOP GOES A LONG WAY'; $pageIntro = 'Browse helpful stop categories for a destination or planned journey.';
$pageStyles = ['assets/css/other-pages.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="panel-card stop-search-panel"><div class="panel-heading"><div><p class="eyebrow">SOURCED DIRECTORY</p><h2>Find a useful stop</h2></div></div>
    <form class="app-form inline-form" id="usefulStopsFilter" action="<?= $baseUrl ?>/backend/user/useful-stops-process.php" method="get">
        <label class="visually-hidden" for="stopDestination">Destination or state</label><input class="form-control" id="stopDestination" name="location" placeholder="Try Jaipur, Munnar or Kerala">
        <label class="visually-hidden" for="stopCity">City</label><input class="form-control" id="stopCity" name="city" placeholder="Filter by city">
        <label class="visually-hidden" for="stopCategory">Stop category</label><select class="form-select" id="stopCategory" name="category"><option value="">All stop categories</option></select>
        <label class="visually-hidden" for="stopJourney">Use a journey's start and destination cities</label><select class="form-select" id="stopJourney" name="journey_id"><option value="">No journey filter</option></select>
        <button class="btn btn-primary-action" type="submit">Show useful stops</button><p class="form-status w-100" aria-live="polite"></p>
    </form>
</section>
<section class="notice-card" aria-labelledby="emergencyInfoHeading"><span aria-hidden="true">!</span><p><strong id="emergencyInfoHeading">Emergency Information.</strong> For immediate emergency assistance in India, call <a href="tel:112" aria-label="Call India's emergency number, 112">112</a>. QuestGo is not an emergency service and does not dispatch help or provide live location-based services.</p></section>
<div class="notice-card"><span aria-hidden="true">ⓘ</span><p><strong>Directory data is not live.</strong> Each listing links to its recorded source. Locations, phone numbers, hours and availability may change; verify essential services directly. Journey filters show results only in the start or destination city, not along a calculated route. No route proximity or live distance is provided.</p></div>
<div class="stop-category-grid" id="usefulStopsResults"><div class="empty-state">Loading stored suggestions…</div></div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

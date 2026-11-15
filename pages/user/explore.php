<?php
$layout = 'user'; $activePage = 'explore'; $pageTitle = 'Explore places | QuestGo';
$pageHeading = 'Explore places'; $pageEyebrow = 'FIND YOUR NEXT PLACE'; $pageIntro = 'Browse illustrative destination and activity estimates across India.';
$pageStyles = ['assets/css/explore.css']; $pageScripts = ['assets/js/user.js'];
$searchQuery = is_string($_GET['q'] ?? null) ? trim($_GET['q']) : '';
$regionQuery = is_string($_GET['region'] ?? null) ? trim($_GET['region']) : '';
$categoryRaw = is_string($_GET['category'] ?? null) ? trim($_GET['category']) : '';
$categoryInput = strtolower($categoryRaw);
$categoryMap = ['hill-stations' => 'Hill Stations', 'beaches' => 'Beaches', 'historical' => 'Historical', 'religious' => 'Religious', 'wildlife' => 'Wildlife', 'culture' => 'Culture', 'nature' => 'Nature', 'adventure' => 'Adventure'];
$categoryQuery = $categoryMap[$categoryInput] ?? $categoryRaw;
$stateQuery = is_string($_GET['state'] ?? null) ? trim($_GET['state']) : '';
$pageScripts[] = 'assets/js/portal-data.js';
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="state-explorer-intro">
    <p class="eyebrow">EXPLORE INDIA BY STATE</p>
    <h2>Choose a state or Union Territory</h2>
    <p>Select a state to see its stored destinations. Open a destination for its description, image, estimated cost and suggested activities.</p>
</section>
<form class="explore-filters" id="placeFilters" action="<?= $baseUrl ?>/pages/user/explore.php" method="get" role="search">
    <label class="filter-search"><span aria-hidden="true">⌕</span><input id="placeSearch" name="q" type="search" value="<?= htmlspecialchars($searchQuery, ENT_QUOTES, 'UTF-8') ?>" placeholder="Search places or states"></label>
    <select class="form-select" id="filterState" name="state" aria-label="Choose a state or Union Territory"><option value="">All states &amp; UTs</option><?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] as $state): ?><option value="<?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?>"<?= strcasecmp($stateQuery, $state) === 0 ? ' selected' : '' ?>><?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
    <select class="form-select" id="filterRegion" name="region" aria-label="Filter by region"><option value="">All regions</option><?php foreach (['North','South','East','West','Central','Northeast'] as $region): ?><option value="<?= $region ?>"<?= strcasecmp($regionQuery, $region) === 0 ? ' selected' : '' ?>><?= $region ?></option><?php endforeach; ?></select>
    <select class="form-select" id="filterCategory" name="category" aria-label="Filter by category"><option value="">All categories</option><?php foreach (['Historical','Religious','Nature','Adventure','Beaches','Hill Stations','Wildlife','Culture','Food & Culture','Shopping','Entertainment'] as $category): ?><option value="<?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?>"<?= strcasecmp($categoryQuery, $category) === 0 ? ' selected' : '' ?>><?= htmlspecialchars($category, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select>
</form>
<div class="results-caption"><span><strong id="placeCount">0</strong> destinations</span><span>Costs and activity times are illustrative estimates, not guaranteed prices or availability.</span></div>
<div class="row g-4" id="placeGrid">
    <div class="col-12"><div class="empty-state">Loading destinations…</div></div>
</div>
<div class="modal fade" id="placeDetailsModal" tabindex="-1" aria-labelledby="placeDetailsTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="placeDetailsTitle">Destination details</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="placeDetailsBody"></div></div></div></div>
<div class="modal fade" id="journeySelectModal" tabindex="-1" aria-labelledby="journeySelectTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="journeySelectTitle">Add to a journey</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><form id="addPlaceJourneyForm" class="app-form"><label for="journeyForPlace">Choose a journey</label><select class="form-select" id="journeyForPlace" required></select><label for="journeyStopDate">Visit date (optional)</label><input class="form-control" type="date" id="journeyStopDate"><label for="journeyStopNotes">Notes (optional)</label><textarea class="form-control" id="journeyStopNotes" rows="2"></textarea><p class="form-status" aria-live="polite"></p><button class="btn btn-primary-action" type="submit">Add journey stop</button></form></div></div></div></div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

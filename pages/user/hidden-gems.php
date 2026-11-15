<?php
$layout = 'user'; $activePage = 'gems'; $pageTitle = 'Hidden gems of India | QuestGo';
$pageHeading = 'Explore beyond the usual.'; $pageEyebrow = 'DISCOVERY COLLECTION';
$pageIntro = 'Browse lesser-known places and selected established destinations, clearly labelled as such. None is presented as an undiscovered secret; check local access guidance before travelling.';
$pageStyles = ['assets/css/explore.css', 'assets/css/other-pages.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="panel-card">
    <form class="app-form inline-form" id="placeFilters" action="<?= $baseUrl ?>/backend/user/places-process.php" method="get" data-hidden-gems="1">
        <label class="visually-hidden" for="gemSearch">Search lesser-known places</label>
        <input class="form-control" id="gemSearch" name="q" type="search" value="<?= htmlspecialchars((string) ($_GET['q'] ?? ''), ENT_QUOTES, 'UTF-8') ?>" placeholder="Search a place, city or state">
        <label class="visually-hidden" for="gemState">Filter by state or Union Territory</label>
        <select class="form-select" id="gemState" name="state"><option value="">All states and Union Territories</option>
            <?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] as $state): ?>
                <option value="<?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?></option>
            <?php endforeach; ?>
        </select>
        <button class="btn btn-primary-action" type="submit">Search places</button>
    </form>
</section>
<div class="results-caption"><span><strong id="placeCount">0</strong> curated places</span><span>Costs and durations are illustrative estimates.</span></div>
<div class="row g-4" id="placeGrid"><div class="col-12"><div class="empty-state">Loading places…</div></div></div>
<div class="modal fade" id="placeDetailsModal" tabindex="-1" aria-labelledby="placeDetailsTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="placeDetailsTitle">Destination details</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body" id="placeDetailsBody"></div></div></div></div>
<div class="modal fade" id="journeySelectModal" tabindex="-1" aria-labelledby="journeySelectTitle" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><div class="modal-header"><h2 class="modal-title fs-5" id="journeySelectTitle">Add to a journey</h2><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button></div><div class="modal-body"><form id="addPlaceJourneyForm" class="app-form"><label for="journeyForPlace">Choose a journey</label><select class="form-select" id="journeyForPlace" required></select><label for="journeyStopDate">Visit date (optional)</label><input class="form-control" type="date" id="journeyStopDate"><label for="journeyStopNotes">Notes (optional)</label><textarea class="form-control" id="journeyStopNotes" rows="2"></textarea><p class="form-status" aria-live="polite"></p><button class="btn btn-primary-action" type="submit">Add journey stop</button></form></div></div></div></div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

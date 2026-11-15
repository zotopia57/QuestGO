<?php
$layout = 'admin'; $activePage = 'places'; $pageTitle = 'Manage places | QuestGo';
$pageHeading = 'Manage places'; $pageEyebrow = 'DESTINATION DIRECTORY'; $pageIntro = 'Review destination details and prepare new India place listings.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/admin.js', 'assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-4">
    <div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">DESTINATION DETAILS</p><h2>Add or edit a place</h2></div></div><form id="placeAdminForm" class="app-form" action="<?= $baseUrl ?>/backend/admin/places-management.php" method="post">
        <?= questgo_csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="place_id" value="">
        <label for="placeName">Destination name</label><input id="placeName" class="form-control" name="name" placeholder="e.g. Hampi" required>
        <label for="placeCity">City</label><input id="placeCity" class="form-control" name="city" placeholder="e.g. Hosapete" required>
        <div class="row g-3"><div class="col-md-6"><label for="placeState">State / UT</label><select id="placeState" class="form-select" name="state_or_ut" required><option value="">Select</option><?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] as $state): ?><option value="<?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div><div class="col-md-6"><label for="placeRegion">Region</label><select id="placeRegion" class="form-select" name="region" required><option value="">Select</option><?php foreach (['North','South','East','West','Central','Northeast'] as $region): ?><option value="<?= $region ?>"><?= $region ?></option><?php endforeach; ?></select></div></div>
        <label for="placeCategory">Category</label><select id="placeCategory" class="form-select" name="category_id" data-category-options="destination" required><option value="">Loading categories…</option></select>
        <label for="placeImage">Photograph URL or local path</label><input id="placeImage" class="form-control" name="image_url" placeholder="assets/images/destinations/... or https://example.com/photo.jpg">
        <label for="placeDescription">Description</label><textarea id="placeDescription" class="form-control" name="description" rows="3" required></textarea>
        <div class="row g-3"><div class="col-md-6"><label for="placeExpense">Estimated expenses (₹)</label><input id="placeExpense" class="form-control" name="estimated_cost" type="number" min="0" step="0.01" value="0" required></div><div class="col-md-6"><label for="placeDuration">Suggested duration</label><input id="placeDuration" class="form-control" name="suggested_duration" placeholder="e.g. 2–3 days" required></div></div>
        <div class="row g-3"><div class="col-md-6"><label for="placeDistance">Distance (km, optional)</label><input id="placeDistance" class="form-control" name="distance" type="number" min="0" step="0.01"></div><div class="col-md-6"><label for="placeSeason">Best visiting season (optional)</label><input id="placeSeason" class="form-control" name="best_visiting_season"></div></div>
        <div class="form-check"><input class="form-check-input" id="placeHiddenGem" name="is_hidden_gem" type="checkbox" value="1"><label class="form-check-label" for="placeHiddenGem">Include in the discovery collection</label></div>
        <label for="placeDiscoveryStatus">Visibility label</label><select id="placeDiscoveryStatus" class="form-select" name="discovery_status"><option value="lesser_known">Lesser-known place</option><option value="established">Established destination</option></select>
        <label for="placeThingsToDo">Things to do</label><textarea id="placeThingsToDo" class="form-control" name="things_to_do" rows="2"></textarea>
        <label for="placeTravelTips">Travel tips</label><textarea id="placeTravelTips" class="form-control" name="travel_tips" rows="2"></textarea>
        <label for="placeAccessNotes">Access and safety information</label><textarea id="placeAccessNotes" class="form-control" name="access_notes" rows="2"></textarea>
        <button class="btn btn-primary-action" type="submit">Save destination</button><button class="btn btn-link-quest" type="reset">Clear form</button><p class="form-status" aria-live="polite"></p>
    </form></section></div>
    <div class="col-xl-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">DESTINATION CATALOGUE</p><h2>Existing destinations</h2></div></div>
        <input id="adminPlaceSearch" class="form-control mb-2" type="search" placeholder="Search place, city or state">
        <select id="adminGemFilter" class="form-select mb-2"><option value="">All destinations</option><option value="1">Lesser-known places</option><option value="0">Other destinations</option></select>
        <div class="table-responsive"><table class="table app-table"><thead><tr><th>Place</th><th>State</th><th>Region</th><th>Category</th><th>Actions</th></tr></thead><tbody data-admin-places><tr><td colspan="5">Loading destinations…</td></tr></tbody></table></div></section></div>
</div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

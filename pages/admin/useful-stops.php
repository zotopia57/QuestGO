<?php
$layout = 'admin'; $activePage = 'stops'; $pageTitle = 'Manage useful stops | QuestGo';
$pageHeading = 'Manage useful stops'; $pageEyebrow = 'SOURCED STOP DIRECTORY';
$pageIntro = 'Add only real listings with a source that supports their address and details.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-4">
    <div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">VERIFIED LISTING</p><h2>Add or edit a stop</h2></div></div>
        <form id="usefulStopAdminForm" class="app-form" action="<?= $baseUrl ?>/backend/admin/useful-stops-management.php" method="post">
            <?= questgo_csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="stop_id" value="">
            <label for="stopNameAdmin">Name</label><input id="stopNameAdmin" class="form-control" name="name" maxlength="180" required>
            <label for="stopCategoryAdmin">Category</label><select id="stopCategoryAdmin" class="form-select" name="category_id" data-stop-category-options required><option value="">Loading categories…</option></select>
            <label for="stopAddressAdmin">Address / precise location</label><input id="stopAddressAdmin" class="form-control" name="address" maxlength="500" required>
            <div class="row g-3"><div class="col-md-6"><label for="stopCityAdmin">City</label><input id="stopCityAdmin" class="form-control" name="city" required></div><div class="col-md-6"><label for="stopStateAdmin">State / UT</label><select id="stopStateAdmin" class="form-select" name="state_or_ut" required><option value="">Choose</option>
                <?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] as $state): ?><option value="<?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?>"><?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?>
            </select></div></div>
            <label for="stopDestinationAdmin">Related destination (optional)</label><select id="stopDestinationAdmin" class="form-select" name="place_id" data-place-options><option value="">No associated destination</option></select>
            <label for="stopDescriptionAdmin">Description</label><textarea id="stopDescriptionAdmin" class="form-control" name="description" rows="3" required></textarea>
            <label for="stopSourceAdmin">Source URL</label><input id="stopSourceAdmin" class="form-control" name="source_url" type="url" placeholder="https://…" required>
            <label for="stopSourceNameAdmin">Source name</label><input id="stopSourceNameAdmin" class="form-control" name="source_name" maxlength="120" required>
            <label for="stopImageAdmin">Photograph path (optional)</label><input id="stopImageAdmin" class="form-control" name="image_url" placeholder="assets/images/destinations/...">
            <div class="row g-3"><div class="col-md-6"><label for="stopContactAdmin">Verified contact (optional)</label><input id="stopContactAdmin" class="form-control" name="contact_phone" type="tel"></div><div class="col-md-6"><label for="stopDistanceAdmin">Supported distance (km, optional)</label><input id="stopDistanceAdmin" class="form-control" name="distance_km" type="number" min="0" step="0.01"></div></div>
            <button class="btn btn-primary-action" type="submit">Save useful stop</button><button class="btn btn-link-quest" type="reset">Clear form</button><p class="form-status" aria-live="polite"></p>
        </form>
    </section></div>
    <div class="col-xl-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">DIRECTORY RECORDS</p><h2>Useful stops</h2></div></div>
        <div class="row g-2 mb-2"><div class="col-md-7"><input class="form-control" id="adminStopSearch" type="search" placeholder="Search name, city or state"></div><div class="col-md-5"><select class="form-select" id="adminStopCategoryFilter"><option value="">All categories</option></select></div></div>
        <div class="table-responsive"><table class="table app-table"><thead><tr><th>Stop</th><th>City</th><th>Category</th><th>Actions</th></tr></thead><tbody data-admin-stops><tr><td colspan="4">Loading stops…</td></tr></tbody></table></div>
    </section></div>
</div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

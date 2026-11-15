<?php
$layout = 'admin'; $activePage = 'activities'; $pageTitle = 'Manage activities | QuestGo';
$pageHeading = 'Manage activities'; $pageEyebrow = 'FREE-TIME DIRECTORY'; $pageIntro = 'Prepare activity suggestions for the rule-based matcher.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-4"><div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">MATCHER SUGGESTION</p><h2>Add or edit an activity</h2></div></div><form id="activityAdminForm" class="app-form" action="<?= $baseUrl ?>/backend/admin/activities-management.php" method="post">
    <?= questgo_csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="activity_id" value="">
    <label for="activityName">Activity name</label><input id="activityName" name="title" class="form-control" required>
    <label for="activityPlace">Destination</label><select id="activityPlace" name="place_id" class="form-select" data-place-options required><option value="">Loading destinations…</option></select>
    <label for="activityCategoryAdmin">Category</label><select id="activityCategoryAdmin" name="category_id" class="form-select" data-category-options="activity" required><option value="">Loading categories…</option></select>
    <label for="activityDescription">Description</label><textarea id="activityDescription" name="description" class="form-control" rows="3" required></textarea>
    <div class="row g-3"><div class="col-6"><label for="activityDurationAdmin">Duration (minutes)</label><input id="activityDurationAdmin" name="duration_minutes" class="form-control" type="number" min="1" max="10080" required></div><div class="col-6"><label for="activityCostAdmin">Estimated cost (₹)</label><input id="activityCostAdmin" name="estimated_cost" class="form-control" type="number" min="0" step="0.01" required></div></div>
    <label for="activityInterests">Interest tags</label><input id="activityInterests" name="interests" class="form-control" placeholder="e.g. History, Culture">
    <label for="activityBestTime">Suitable time or season (optional)</label><input id="activityBestTime" name="best_time" class="form-control" maxlength="160">
    <label for="activityImageAdmin">Photograph path (optional)</label><input id="activityImageAdmin" name="image_url" class="form-control" placeholder="assets/images/destinations/...">
    <button class="btn btn-primary-action" type="submit">Save activity</button><button class="btn btn-link-quest" type="reset">Clear form</button><p class="form-status" aria-live="polite"></p>
</form></section></div>
<div class="col-xl-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">STORED ACTIVITY DATA</p><h2>Activity suggestions</h2></div></div>
    <div class="row g-2 mb-2"><div class="col-md-7"><input class="form-control" id="adminActivitySearch" type="search" placeholder="Search title, location or state"></div><div class="col-md-5"><select class="form-select" id="adminActivityCategoryFilter"><option value="">All categories</option></select></div></div>
    <div class="table-responsive"><table class="table app-table"><thead><tr><th>Activity</th><th>Location</th><th>Category</th><th>Duration</th><th>Cost</th><th>Actions</th></tr></thead><tbody data-admin-activities><tr><td colspan="6">Loading activities…</td></tr></tbody></table></div></section></div></div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

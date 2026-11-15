<?php
$layout = 'admin'; $activePage = 'categories'; $pageTitle = 'Manage categories | QuestGo';
$pageHeading = 'Manage categories'; $pageEyebrow = 'CONTENT ORGANISATION'; $pageIntro = 'Keep destination and activity labels consistent.';
$pageStyles = ['assets/css/admin-dashboard.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-4"><div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">CONTENT LABEL</p><h2>Add or edit a category</h2></div></div><form id="categoryAdminForm" class="app-form" action="<?= $baseUrl ?>/backend/admin/category-management.php" method="post"><?= questgo_csrf_field() ?><input type="hidden" name="action" value="add"><input type="hidden" name="category_id" value=""><label for="categoryName">Category name</label><input id="categoryName" name="category_name" class="form-control" placeholder="e.g. Wildlife" required><label for="categoryType">Used for</label><select id="categoryType" name="category_type" class="form-select" required><option value="">Choose</option><option value="destination">Destination</option><option value="activity">Activity</option></select><button class="btn btn-primary-action" type="submit">Save category</button><button class="btn btn-link-quest" type="reset">Clear form</button><p class="form-status" aria-live="polite"></p></form></section></div>
<div class="col-xl-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">STORED TAXONOMY</p><h2>Current categories</h2></div></div><div class="category-admin-list" data-admin-categories><p class="inline-note">Loading categories…</p></div></section></div></div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

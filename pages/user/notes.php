<?php
$layout = 'user'; $activePage = 'notes'; $pageTitle = 'Travel notes | QuestGo';
$pageHeading = 'Notes for the road.'; $pageEyebrow = 'KEEP THE LITTLE DETAILS'; $pageIntro = 'Collect reminders, ideas and the details that make a trip yours.';
$pageStyles = ['assets/css/other-pages.css']; $pageScripts = ['assets/js/user.js', 'assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-4">
    <div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">WRITE IT DOWN</p><h2>New travel note</h2></div></div><form class="app-form" action="<?= $baseUrl ?>/backend/user/notes-process.php" method="post">
        <?= questgo_csrf_field() ?><input type="hidden" name="action" value="create">
        <label for="noteTitle">Note title</label><input class="form-control" id="noteTitle" name="title" placeholder="e.g. Places for breakfast" required>
        <label for="noteJourney">Related journey</label><select class="form-select" id="noteJourney" name="journey_id"><option value="">No journey selected</option></select>
        <label for="noteBody">Your note</label><textarea class="form-control" id="noteBody" name="content" rows="6" placeholder="A thought, a reminder, a place to look up…" required></textarea>
        <button class="btn btn-primary-action" type="submit">Save note</button>
    </form></section></div>
    <div class="col-xl-7"><div class="notes-list"><div class="empty-state">Loading your notes…</div></div><p class="inline-note">Notes are visible only to their owner.</p></div>
</div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

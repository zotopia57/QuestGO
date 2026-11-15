<?php
$layout = 'user'; $activePage = 'matcher'; $pageTitle = 'Free-time activity matcher | QuestGo';
$pageHeading = 'Make the most of a little time.'; $pageEyebrow = 'QUESTGO SIGNATURE FEATURE'; $pageIntro = 'Find activity ideas that fit your free hours, interests and budget.';
$pageStyles = ['assets/css/other-pages.css']; $pageScripts = ['assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="matcher-banner"><div><span class="matcher-spark">✳</span><p class="eyebrow">YOUR TIME, YOUR KIND OF ADVENTURE</p><h2>What would feel good<br>to do right now?</h2><p>Rule-based matching · stored activity suggestions</p></div><div class="matcher-art" aria-hidden="true">Q</div></section>
<section class="panel-card matcher-form-panel"><form id="activityFilters" class="app-form matcher-form" action="<?= $baseUrl ?>/backend/user/activity-process.php" method="get">
    <div class="row g-3">
        <div class="col-md-6 col-xl-3"><label for="activityLocation">Indian city or destination</label><input class="form-control" id="activityLocation" name="location" placeholder="e.g. Jaipur"></div>
        <div class="col-md-6 col-xl-3"><label for="freeTime">Available free time</label><select class="form-select" id="freeTime" name="free_time"><option value="30">30 minutes</option><option value="60">1 hour</option><option value="120">2 hours</option><option value="300" selected>3–5 hours</option><option value="720">A full day</option></select></div>
        <div class="col-md-6 col-xl-3"><label for="activityBudget">Budget (₹)</label><input class="form-control" id="activityBudget" name="budget" type="number" min="0" value="1500"></div>
        <div class="col-md-6 col-xl-3"><label for="activityCategory">Preferred category</label><select class="form-select" id="activityCategory" name="category"><option value="">Anything</option><option>Adventure</option><option>Nature and outdoors</option><option>Food and local experiences</option><option>Shopping</option><option>Photography</option><option>Entertainment</option><option>Culture and heritage</option><option>Relaxation</option><option>Family-friendly</option><option>Culture</option><option>Food</option><option>Nature</option><option>History</option></select></div>
    </div>
    <fieldset class="interest-fieldset"><legend>What are you interested in?</legend><div class="interest-options"><?php foreach (['food','history','nature','culture','walking','architecture','adventure','photography','religion','crafts','shopping','relaxation','family'] as $interest): ?><label><input type="checkbox" name="interests[]" value="<?= htmlspecialchars($interest, ENT_QUOTES, 'UTF-8') ?>"> <span><?= htmlspecialchars(ucfirst($interest), ENT_QUOTES, 'UTF-8') ?></span></label><?php endforeach; ?></div></fieldset>
    <button class="btn btn-primary-action" type="submit">Find my free-time ideas <span aria-hidden="true">✳</span></button><p id="matcherStatus" class="matcher-status" aria-live="polite">Enter your preferences to find matching stored activities.</p>
</form></section>
<div class="results-caption"><span><strong id="activityCount">0</strong> matching activities</span><span>Costs and durations are estimates.</span></div>
<div class="row g-3" id="activityResults"></div>
<div class="empty-state d-none" id="activitiesEmpty">No activities match those filters. Try more time or a broader location.</div>
<p class="inline-note">Matching is rule-based and uses stored activity data. Costs and durations are estimates, not live availability.</p>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

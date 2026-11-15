<?php
$layout = 'user'; $activePage = 'dashboard'; $pageTitle = 'Your travel dashboard | QuestGo';
$pageHeading = 'Good morning, traveller.'; $pageEyebrow = 'YOUR TRAVEL SPACE'; $pageIntro = 'A little overview of the journeys and places on your mind.';
$pageStyles = ['assets/css/user-dashboard.css']; $pageScripts = ['assets/js/user.js', 'assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<section class="welcome-banner"><div><p class="eyebrow text-white">YOUR NEXT CHAPTER</p><h2>Every great journey<br>starts with a first step.</h2><p>Plan the details. Keep the discoveries.</p></div><a class="btn btn-quest" href="<?= $baseUrl ?>/pages/user/journey-planner.php">Plan a journey <span aria-hidden="true">→</span></a></section>
<div class="row g-3 stat-row">
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon">↗</span><small>Total journeys</small><strong>—</strong><span>Loading…</span></article></div>
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon pink">♡</span><small>Saved places</small><strong>—</strong><span>Loading…</span></article></div>
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon blue">₹</span><small>Planned budget</small><strong>—</strong><span>Loading…</span></article></div>
    <div class="col-6 col-xl-3"><article class="stat-card"><span class="stat-icon gold">✳</span><small>Travel notes</small><strong>—</strong><span>Loading…</span></article></div>
</div>
<div class="row g-4 dashboard-lower">
    <div class="col-xl-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">ON THE HORIZON</p><h2>Recent journeys</h2></div><a href="<?= $baseUrl ?>/pages/user/journey-planner.php">View planner <span aria-hidden="true">→</span></a></div>
        <div class="journey-row"><span class="journey-thumb thumb-kerala"></span><div class="journey-details"><strong>Loading your journeys…</strong><span></span></div><span class="journey-status"></span></div>
    </section></div>
    <div class="col-xl-5"><section class="panel-card quick-actions"><div class="panel-heading"><div><p class="eyebrow">MAKE A MOVE</p><h2>Quick actions</h2></div></div>
        <a href="<?= $baseUrl ?>/pages/user/journey-planner.php"><span>↗</span><div><strong>Start a journey plan</strong><small>Bring your trip details together</small></div><b>→</b></a>
        <a href="<?= $baseUrl ?>/pages/user/activity-matcher.php"><span>✳</span><div><strong>Find something to do</strong><small>Match ideas to your free time</small></div><b>→</b></a>
        <a href="<?= $baseUrl ?>/pages/user/explore.php"><span>⌖</span><div><strong>Explore somewhere new</strong><small>Browse places across India</small></div><b>→</b></a>
    </section></div>
</div>
<section class="suggestion-strip"><div><p class="eyebrow">A THOUGHT FOR YOUR NEXT TRIP</p><h2>Find a quieter side of Munnar.</h2><p>Tea gardens, cool highland air and winding roads through Kerala.</p></div><a href="<?= $baseUrl ?>/pages/user/explore.php?q=Munnar">Discover Munnar <span aria-hidden="true">↗</span></a></section>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

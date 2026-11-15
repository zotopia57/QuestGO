<?php
$layout = 'user'; $activePage = 'journey'; $pageTitle = 'Journey planner | QuestGo';
$pageHeading = 'Journey planner'; $pageEyebrow = 'MAKE A PLAN'; $pageIntro = 'Gather the key details for a journey across India.';
$pageStyles = ['assets/css/journey.css']; $pageScripts = ['assets/js/user.js', 'assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="row g-4">
    <div class="col-xl-7"><section class="panel-card form-panel"><div class="panel-heading"><div><p class="eyebrow">YOUR NEXT ADVENTURE</p><h2>Start with the essentials</h2></div></div>
        <form class="app-form" action="<?= $baseUrl ?>/backend/user/journey-process.php" method="post">
            <?= questgo_csrf_field() ?><input type="hidden" name="action" value="add">
            <label for="journeyTitle">Journey title</label><input id="journeyTitle" class="form-control" name="title" placeholder="e.g. Long weekend in the hills" required>
            <div class="row g-3"><div class="col-md-6"><label for="startCity">Starting city</label><input id="startCity" class="form-control" name="start_city" placeholder="e.g. Bengaluru" required></div><div class="col-md-6"><label for="startState">Starting state / UT</label><select id="startState" class="form-select" name="start_state" required><option value="">Choose one</option><?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] as $state): ?><option><?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div></div>
            <div class="row g-3"><div class="col-md-6"><label for="destinationCity">Destination city</label><input id="destinationCity" class="form-control" name="destination_city" placeholder="e.g. Ooty" required></div><div class="col-md-6"><label for="destinationState">Destination state / UT</label><select id="destinationState" class="form-select" name="destination_state" required><option value="">Choose one</option><?php foreach (['Andhra Pradesh','Arunachal Pradesh','Assam','Bihar','Chhattisgarh','Goa','Gujarat','Haryana','Himachal Pradesh','Jharkhand','Karnataka','Kerala','Madhya Pradesh','Maharashtra','Manipur','Meghalaya','Mizoram','Nagaland','Odisha','Punjab','Rajasthan','Sikkim','Tamil Nadu','Telangana','Tripura','Uttar Pradesh','Uttarakhand','West Bengal','Andaman and Nicobar Islands','Chandigarh','Dadra and Nagar Haveli and Daman and Diu','Delhi','Jammu and Kashmir','Ladakh','Lakshadweep','Puducherry'] as $state): ?><option><?= htmlspecialchars($state, ENT_QUOTES, 'UTF-8') ?></option><?php endforeach; ?></select></div></div>
            <div class="row g-3"><div class="col-md-6"><label for="departDate">Departure date</label><input id="departDate" class="form-control" type="date" name="departure_date" required></div><div class="col-md-6"><label for="returnDate">Return date</label><input id="returnDate" class="form-control" type="date" name="return_date" required></div></div>
            <div class="row g-3"><div class="col-md-6"><label for="travellers">Travellers</label><input id="travellers" class="form-control" type="number" min="1" max="50" name="travellers" value="1" required></div><div class="col-md-6"><label for="budget">Estimated budget (₹)</label><input id="budget" class="form-control" type="number" min="0" step="100" name="budget" placeholder="e.g. 25000" required></div></div>
            <button type="submit" class="btn btn-primary-action">Save journey <span aria-hidden="true">→</span></button>
        </form>
    </section></div>
    <div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">YOUR SAVED JOURNEYS</p><h2>Your travel board</h2></div></div>
        <div id="journeyList"><div class="empty-state">Loading your journeys…</div></div>
        <p class="inline-note">Journey records are private to the signed-in account.</p>
    </section></div>
</div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

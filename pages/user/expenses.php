<?php
$layout = 'user'; $activePage = 'expenses'; $pageTitle = 'Travel expenses | QuestGo';
$pageHeading = 'Keep an eye on the spend.'; $pageEyebrow = 'TRAVEL BUDGET'; $pageIntro = 'Record trip costs and keep your rupee budget in view.';
$pageStyles = ['assets/css/other-pages.css']; $pageScripts = ['assets/js/user.js', 'assets/js/portal-data.js'];
require __DIR__ . '/../../includes/dashboard-start.php';
?>
<div class="expense-summary"><div><span>Total expenses</span><strong>Calculated from your records</strong></div><div><span>Journey filter</span><strong>All journeys</strong></div><div><span>Currency</span><strong>Indian Rupees (₹)</strong></div></div>
<div class="row g-4">
    <div class="col-xl-5"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">ADD TO THE LEDGER</p><h2>Record an expense</h2></div></div><form class="app-form" action="<?= $baseUrl ?>/backend/user/expenses-process.php" method="post">
        <?= questgo_csrf_field() ?><input type="hidden" name="action" value="add">
        <label for="expenseTitle">Expense title</label><input class="form-control" id="expenseTitle" name="title" placeholder="e.g. Train tickets" required>
        <label for="expenseCategory">Category</label><select class="form-select" id="expenseCategory" name="category" required><option value="">Choose a category</option><option>Transport</option><option>Accommodation</option><option>Food</option><option>Activities</option><option>Shopping</option><option>Other</option></select>
        <div class="row g-3"><div class="col-md-6"><label for="expenseAmount">Amount (₹)</label><input class="form-control" id="expenseAmount" type="number" min="0.01" step="0.01" name="amount" required></div><div class="col-md-6"><label for="expenseDate">Date</label><input class="form-control" id="expenseDate" type="date" name="date" required></div></div>
        <label for="expenseJourney">Related journey</label><select class="form-select" id="expenseJourney" name="journey_id"><option value="">No journey</option></select>
        <label for="expenseNotes">Notes</label><textarea class="form-control" id="expenseNotes" name="notes" rows="3" placeholder="Optional details"></textarea>
        <button class="btn btn-primary-action" type="submit">Add expense</button>
    </form></section></div>
    <div class="col-xl-7"><section class="panel-card"><div class="panel-heading"><div><p class="eyebrow">YOUR LEDGER</p><h2>Recent expenses</h2></div><span class="muted-small">Dates DD-MM-YYYY</span></div>
        <label for="expenseFilterJourney">Filter by journey</label><select class="form-select mb-3" id="expenseFilterJourney"><option value="">All journeys</option></select>
        <div class="table-responsive"><table class="table app-table"><thead><tr><th>Expense</th><th>Category</th><th>Date</th><th class="text-end">Amount</th></tr></thead><tbody><tr><td colspan="4">Loading your expenses…</td></tr></tbody></table></div>
        <div class="expense-breakdown" aria-live="polite"></div><p class="inline-note">Expenses are visible only to their owner.</p>
    </section></div>
</div>
<?php require __DIR__ . '/../../includes/dashboard-end.php'; ?>

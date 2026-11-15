<aside class="dashboard-sidebar user-sidebar" aria-label="User navigation">
    <a class="navbar-brand questgo-brand sidebar-brand" href="<?= $baseUrl ?>/index.php"><span class="brand-mark" aria-hidden="true">Q</span><span>quest<span class="brand-accent">go</span><small>YOUR TRAVEL SPACE</small></span></a>
    <p class="sidebar-label">YOUR SPACE</p>
    <nav class="sidebar-nav">
        <?php
        $userLinks = [
            'dashboard' => ['Dashboard', 'dashboard.php', '◫'],
            'journey' => ['Journey planner', 'journey-planner.php', '↗'],
            'explore' => ['Explore places', 'explore.php', '⌖'],
            'gems' => ['Hidden gems', 'hidden-gems.php', '✧'],
            'stops' => ['Useful stops', 'useful-stops.php', '◎'],
            'matcher' => ['Free-time matcher', 'activity-matcher.php', '✳'],
            'saved' => ['Saved places', 'saved-places.php', '♡'],
            'expenses' => ['Expenses', 'expenses.php', '₹'],
            'notes' => ['Travel notes', 'notes.php', '▤'],
        ];
        foreach ($userLinks as $key => [$label, $file, $icon]):
        ?>
            <a class="sidebar-link<?= ($activePage ?? '') === $key ? ' active' : '' ?>" href="<?= $baseUrl ?>/pages/user/<?= $file ?>"><span aria-hidden="true"><?= $icon ?></span><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <p class="sidebar-label sidebar-account-label">ACCOUNT</p>
    <nav class="sidebar-nav">
        <a class="sidebar-link<?= ($activePage ?? '') === 'profile' ? ' active' : '' ?>" href="<?= $baseUrl ?>/pages/user/profile.php"><span aria-hidden="true">◉</span>Profile</a>
        <a class="sidebar-link<?= ($activePage ?? '') === 'password' ? ' active' : '' ?>" href="<?= $baseUrl ?>/pages/user/change-password.php"><span aria-hidden="true">⌑</span>Change password</a>
    </nav>
    <div class="sidebar-bottom"><form action="<?= $baseUrl ?>/backend/logout.php" method="post"><?= questgo_csrf_field() ?><button type="submit">Log out</button></form><a href="<?= $baseUrl ?>/index.php">← Back to QuestGo</a></div>
</aside>

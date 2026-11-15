<aside class="dashboard-sidebar admin-sidebar" aria-label="Admin navigation">
    <a class="navbar-brand questgo-brand sidebar-brand" href="<?= $baseUrl ?>/index.php"><span class="brand-mark" aria-hidden="true">Q</span><span>quest<span class="brand-accent">go</span><small>ADMIN CONSOLE</small></span></a>
    <p class="sidebar-label">OVERVIEW</p>
    <nav class="sidebar-nav">
        <?php
        $adminLinks = [
            'dashboard' => ['Dashboard', 'dashboard.php', '◫'],
            'users' => ['Manage users', 'users.php', '♙'],
            'places' => ['Manage places', 'places.php', '⌖'],
            'stops' => ['Manage useful stops', 'useful-stops.php', '◎'],
            'activities' => ['Manage activities', 'activities.php', '✳'],
            'categories' => ['Manage categories', 'categories.php', '▦'],
            'journeys' => ['Manage journeys', 'journeys.php', '↗'],
            'reports' => ['Reports & statistics', 'reports.php', '▤'],
        ];
        foreach ($adminLinks as $key => [$label, $file, $icon]):
        ?>
            <a class="sidebar-link<?= ($activePage ?? '') === $key ? ' active' : '' ?>" href="<?= $baseUrl ?>/pages/admin/<?= $file ?>"><span aria-hidden="true"><?= $icon ?></span><?= $label ?></a>
        <?php endforeach; ?>
    </nav>
    <p class="sidebar-label sidebar-account-label">ACCOUNT</p>
    <nav class="sidebar-nav"><a class="sidebar-link<?= ($activePage ?? '') === 'profile' ? ' active' : '' ?>" href="<?= $baseUrl ?>/pages/admin/profile.php"><span aria-hidden="true">◉</span>Admin profile</a></nav>
    <div class="sidebar-bottom"><form action="<?= $baseUrl ?>/backend/logout.php" method="post"><?= questgo_csrf_field() ?><button type="submit">Log out</button></form><a href="<?= $baseUrl ?>/index.php">← Back to QuestGo</a></div>
</aside>

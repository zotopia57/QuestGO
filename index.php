<?php
$pageTitle = 'Explore India, Plan Your Journey with QuestGo';
$pageStyles = ['assets/css/home.css'];
require __DIR__ . '/includes/header.php';
?>
<main>
    <section class="home-hero" id="home">
        <img class="home-hero-photo" src="<?= $baseUrl ?>/assets/images/destinations/munnar.jpg" alt="Tea gardens and mountain slopes around Munnar, Kerala">
        <div class="container home-hero-content">
            <p class="eyebrow text-white">MADE FOR JOURNEYS ACROSS INDIA</p>
            <h1>Explore India,<br>Plan Your Journey<br><span>with QuestGo.</span></h1>
            <p class="home-hero-copy">Discover places worth the detour, plan journeys that feel like yours, and keep every travel detail together.</p>
            <form class="destination-search" action="<?= $baseUrl ?>/pages/user/explore.php" method="get">
                <label class="visually-hidden" for="homeSearch">Search an Indian destination</label>
                <span class="search-pin" aria-hidden="true">⌖</span>
                <input id="homeSearch" name="q" type="search" placeholder="Where in India would you like to go?" autocomplete="off">
                <button type="submit">Explore places <span aria-hidden="true">&rarr;</span></button>
            </form>
            <p class="home-hero-footnote">India only, from the mountains to the coast.</p>
        </div>
        <div class="hero-location"><span class="location-dot"></span><span><strong>Western Ghats</strong><small>South India</small></span></div>
    </section>

    <section class="section-space home-popular" id="popular">
        <div class="container">
            <div class="section-heading-row">
                <div><p class="eyebrow">START WITH A PLACE</p><h2 class="section-title">Popular destinations<span>.</span></h2></div>
                <a class="text-link" href="<?= $baseUrl ?>/pages/user/explore.php">Explore all places <span aria-hidden="true">&rarr;</span></a>
            </div>
            <div class="row g-4">
                <div class="col-12 col-md-6 col-lg-3"><a class="home-place-card" href="<?= $baseUrl ?>/pages/user/explore.php?q=Goa"><img src="<?= $baseUrl ?>/assets/images/destinations/palolem-beach.jpg" alt="Palolem Beach on Goa's southern coast" loading="lazy"><span>BEACHES &amp; SUNSETS</span><strong>Goa</strong><small>West India</small></a></div>
                <div class="col-12 col-md-6 col-lg-3"><a class="home-place-card" href="<?= $baseUrl ?>/pages/user/explore.php?q=Jaipur"><img src="<?= $baseUrl ?>/assets/images/destinations/jaipur.jpg" alt="Ornate archway in Jaipur's City Palace, Rajasthan" loading="lazy"><span>FORTS &amp; COLOUR</span><strong>Jaipur</strong><small>Rajasthan · West India</small></a></div>
                <div class="col-12 col-md-6 col-lg-3"><a class="home-place-card" href="<?= $baseUrl ?>/pages/user/explore.php?q=Munnar"><img src="<?= $baseUrl ?>/assets/images/destinations/munnar.jpg" alt="Tea plantations in the hills around Munnar, Kerala" loading="lazy"><span>HILLS &amp; TEA GARDENS</span><strong>Munnar</strong><small>Kerala · South India</small></a></div>
                <div class="col-12 col-md-6 col-lg-3"><a class="home-place-card" href="<?= $baseUrl ?>/pages/user/explore.php?q=Meghalaya"><img src="<?= $baseUrl ?>/assets/images/destinations/living-root-bridges.jpg" alt="Living root bridge in the Khasi Hills of Meghalaya" loading="lazy"><span>WATERFALLS &amp; CLOUDS</span><strong>Meghalaya</strong><small>Northeast India</small></a></div>
            </div>
        </div>
    </section>

    <section class="state-section section-space" id="states">
        <div class="container">
            <div class="section-heading-row">
                <div><p class="eyebrow">28 STATES, 8 UNION TERRITORIES</p><h2 class="section-title">India, in every direction.</h2></div>
                <p class="section-lead">Choose a region to begin exploring. Places shown are inspiration, not live travel information.</p>
            </div>
            <div class="region-grid">
                <a href="<?= $baseUrl ?>/pages/user/explore.php?region=North" class="region-card region-north"><span>01</span><strong>North</strong><small>Himalayan trails &amp; heritage cities</small></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?region=South" class="region-card region-south"><span>02</span><strong>South</strong><small>Coastlines, hills &amp; temple towns</small></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?region=East" class="region-card region-east"><span>03</span><strong>East</strong><small>Culture, rivers &amp; quiet escapes</small></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?region=West" class="region-card region-west"><span>04</span><strong>West</strong><small>Desert forts &amp; sea-side days</small></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?region=Central" class="region-card region-central"><span>05</span><strong>Central</strong><small>Wildlife, forests &amp; old capitals</small></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?region=Northeast" class="region-card region-northeast"><span>06</span><strong>Northeast</strong><small>Living roots &amp; green valleys</small></a>
            </div>
            <details class="state-list">
                <summary>View all states and Union Territories</summary>
                <p><strong>States:</strong> Andhra Pradesh, Arunachal Pradesh, Assam, Bihar, Chhattisgarh, Goa, Gujarat, Haryana, Himachal Pradesh, Jharkhand, Karnataka, Kerala, Madhya Pradesh, Maharashtra, Manipur, Meghalaya, Mizoram, Nagaland, Odisha, Punjab, Rajasthan, Sikkim, Tamil Nadu, Telangana, Tripura, Uttar Pradesh, Uttarakhand and West Bengal.</p>
                <p><strong>Union Territories:</strong> Andaman and Nicobar Islands, Chandigarh, Dadra and Nagar Haveli and Daman and Diu, Delhi, Jammu and Kashmir, Ladakh, Lakshadweep and Puducherry.</p>
            </details>
        </div>
    </section>

    <section class="category-section section-space" id="categories">
        <div class="container">
            <p class="eyebrow">TRAVEL YOUR WAY</p><h2 class="section-title">What are you in the mood for?</h2>
            <div class="category-list">
                <a href="<?= $baseUrl ?>/pages/user/explore.php?category=Hill-stations">Hill stations <span>↗</span></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?category=Beaches">Beaches <span>↗</span></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?category=Historical">Historical places <span>↗</span></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?category=Religious">Religious destinations <span>↗</span></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?category=Wildlife">Wildlife &amp; nature <span>↗</span></a>
                <a href="<?= $baseUrl ?>/pages/user/explore.php?category=Culture">Culture &amp; food <span>↗</span></a>
            </div>
        </div>
    </section>

    <section class="planner-section section-space" id="planner">
        <div class="container"><div class="row align-items-center g-4">
            <div class="col-lg-6"><div class="planner-image"><img src="<?= $baseUrl ?>/assets/images/destinations/hampi.jpg" alt="Ancient stone temples and boulder-strewn landscape at Hampi, Karnataka" loading="lazy"></div></div>
            <div class="col-lg-6 planner-copy">
                <p class="eyebrow">A BETTER WAY TO GET THERE</p><h2 class="section-title">Make a plan.<br><span>Leave room for wonder.</span></h2>
                <p>Bring your starting point, destination, dates and budget into one journey plan. Keep important details close while you make the trip your own.</p>
                <a class="btn btn-quest" href="<?= $baseUrl ?>/pages/user/journey-planner.php">Meet the Journey Planner <span aria-hidden="true">&rarr;</span></a>
            </div>
        </div></div>
    </section>

    <section class="tool-section section-space" id="tools">
        <div class="container"><div class="row g-4">
            <div class="col-md-6"><article class="tool-card tool-stops"><p class="eyebrow">BETWEEN HERE &amp; THERE</p><h2>Make the stops part of the story.</h2><p>Browse useful stop suggestions such as food, fuel, rest spots and essential services. Suggestions are curated examples, not live, route-aware listings.</p><a href="<?= $baseUrl ?>/pages/user/useful-stops.php">Explore Useful Stops <span aria-hidden="true">&rarr;</span></a></article></div>
            <div class="col-md-6"><article class="tool-card tool-time"><p class="eyebrow">A FEW HOURS TO YOURSELF</p><h2>Good things fit in the gaps.</h2><p>Tell QuestGo how much time you have, what you enjoy and your budget. A rule-based matcher can help you shortlist local activities.</p><a href="<?= $baseUrl ?>/pages/user/activity-matcher.php">Try the Free-Time Matcher <span aria-hidden="true">&rarr;</span></a></article></div>
        </div></div>
    </section>

    <section class="hidden-gems section-space" id="hidden-gems">
        <div class="container"><div class="section-heading-row"><div><p class="eyebrow">BEYOND THE USUAL</p><h2 class="section-title">A few lesser-known places.</h2></div><a class="text-link" href="<?= $baseUrl ?>/pages/user/hidden-gems.php">Explore the collection <span aria-hidden="true">&rarr;</span></a></div>
            <div class="row g-4">
                <div class="col-md-4"><a class="gem-card" href="<?= $baseUrl ?>/pages/user/hidden-gems.php?q=Orchha"><img src="<?= $baseUrl ?>/assets/images/destinations/orchha.jpg" alt="Jahangir Mahal palace in Orchha, Madhya Pradesh" loading="lazy"><span>Established · Madhya Pradesh</span><strong>Orchha</strong><small>Riverside history, at a gentler pace</small></a></div>
                <div class="col-md-4"><a class="gem-card" href="<?= $baseUrl ?>/pages/user/hidden-gems.php?q=Ziro"><img src="<?= $baseUrl ?>/assets/images/destinations/ziro-valley.jpg" alt="Green paddy fields and hills in Ziro Valley, Arunachal Pradesh" loading="lazy"><span>Lesser-known · Arunachal Pradesh</span><strong>Ziro Valley</strong><small>Pine-scented paths and rich culture</small></a></div>
                <div class="col-md-4"><a class="gem-card" href="<?= $baseUrl ?>/pages/user/hidden-gems.php?q=Gokarna"><img src="<?= $baseUrl ?>/assets/images/destinations/gokarna.jpg" alt="Coastline and beaches near Gokarna, Karnataka" loading="lazy"><span>Established · Karnataka</span><strong>Gokarna</strong><small>Coastal walks and slower mornings</small></a></div>
            </div>
        </div>
    </section>

    <section class="home-cta"><div class="container d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-4"><div><p class="eyebrow text-white">YOUR NEXT CHAPTER IS CLOSER THAN YOU THINK</p><h2>Ready to make a journey yours?</h2></div><div class="d-flex gap-2"><a class="btn btn-light" href="<?= $baseUrl ?>/pages/register.php">Create an account</a><a class="btn btn-outline-light" href="<?= $baseUrl ?>/pages/login.php">Log in</a></div></div></section>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>

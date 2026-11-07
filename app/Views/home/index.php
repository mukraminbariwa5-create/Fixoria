<?php
$u = user();
$isPro = is_role('provider');
$isClientOrGuest = !$u || is_role('client');
?>
<div class="container home">

  <section class="home-hero">
    <section class="home-banner" aria-label="Search for a service">
      <picture class="hero-photo">
        <source media="(min-width: 601px)" srcset="<?= e(asset('img/hero-technician.webp')) ?>">
        <img src="<?= e(asset('img/hero-placeholder.svg')) ?>" width="1440" height="960" alt="" fetchpriority="high" decoding="async">
      </picture>
      <span class="hero-tag" aria-hidden="true"><?= icon('tag') ?></span>
      <div class="banner-copy">
        <p class="hero-eyebrow hero-desktop-greeting"><?= $u ? 'Good morning, ' . e($u['first_name']) : 'Welcome to Servease' ?></p>
        <p class="hero-mobile-greeting"><?= $u ? 'Hi, ' . e($u['first_name']) : 'Hello there' ?> <span aria-hidden="true">👋</span></p>
        <h1><span class="hero-desktop-title">A little help.<br>A better home.</span><span class="hero-mobile-title">What needs a hand?</span></h1>
        <p class="hero-description">Find trusted local pros for your everyday to-dos.</p>
        <form class="banner-search" action="<?= e(url('/services')) ?>" method="get" role="search">
          <label class="banner-field">
            <?= icon('search') ?>
            <span class="sr-only">What can we help with?</span>
            <input name="q" type="search" placeholder="What can we help with?" autocomplete="off" data-home-search="<?= e(Lang::text('Search services')) ?>">
          </label>
          <button class="btn btn-primary banner-go" type="submit" aria-label="Find a service"><span class="search-button-label">Find a service</span><span class="search-button-icon" aria-hidden="true"><?= icon('search') ?></span></button>
        </form>
        <p class="banner-note">ID-checked pros <span>·</span> Clear prices <span>·</span> Local expertise</p>
      </div>
    </section>
    <?php if ($next): ?>
      <?= partial('upcoming-booking', ['next' => $next, 'isPro' => $isPro]) ?>
    <?php elseif ($u && $isPro): ?>
      <aside class="side-card business-summary">
        <div class="side-head"><span class="side-title">Your business</span></div>
        <div class="side-big"><strong><?= (int) $pendingRequests ?></strong><span>new requests waiting</span></div>
        <p class="small muted">Your next great job starts here.</p>
        <a class="btn btn-primary" href="<?= e(url('/bookings')) ?>">Open bookings <?= icon('arrow-right') ?></a>
      </aside>
    <?php else: ?>
      <aside class="side-card home-get-started">
        <span class="side-title"><?= icon('ticket') ?> Your next booking</span>
        <div class="home-booking-empty">
          <span class="empty-booking-icon" aria-hidden="true"><?= icon('calendar') ?></span>
          <div>
            <h2>No upcoming bookings</h2>
            <p class="muted small">Your next service will appear here.</p>
          </div>
        </div>
        <a class="btn btn-primary" href="<?= e(url('/services')) ?>">Explore services <?= icon('arrow-right') ?></a>
      </aside>
    <?php endif; ?>
  </section>

  <?php if ($reminders): ?>
  <!-- Customer to-dos: pay for finished jobs, then review them -->
  <section class="reminders" aria-label="Needs your attention">
    <?php foreach ($reminders as [$kind, $href, $head, $text]): ?>
      <a class="reminder reminder-<?= $kind ?>" href="<?= e(url($href)) ?>">
        <span class="reminder-ic"><?= icon(['pay' => 'wallet', 'overdue' => 'calendar-check'][$kind] ?? 'star') ?></span>
        <span class="grow"><strong><?= e($head) ?></strong><small><?= e($text) ?></small></span>
        <?= icon('chevron-right', 'reminder-go') ?>
      </a>
    <?php endforeach; ?>
  </section>
  <?php endif; ?>

  <!-- Get the app (phones that can install it; hidden once installed or dismissed) -->
  <div class="install-card show-md" data-install-card hidden>
    <span class="install-app-icon"><img src="<?= e(url('/assets/img/icon-192.png')) ?>" alt="" width="44" height="44"></span>
    <span class="grow"><strong>Get the app</strong><small>Full screen, one tap from your home screen</small></span>
    <button class="btn btn-primary btn-sm" type="button" data-install>Install</button>
    <button class="icon-btn install-close" type="button" data-install-dismiss aria-label="Not now"><?= icon('x') ?></button>
  </div>

  <!-- 2. Categories -->
  <section class="home-section home-categories">
    <div class="section-head">
      <h2>What do you need help with?</h2>
      <a class="more" href="<?= e(url('/categories')) ?>">View all</a>
    </div>
    <div class="cat-row">
      <?php
        $categoryOrder = ['aircon', 'cleaning', 'plumbing', 'electrical', 'carpentry', 'painting', 'handyman', 'moving'];
        $homeCategories = [];
        foreach ($categoryOrder as $slug) foreach ($categories as $c) if ($c['slug'] === $slug) $homeCategories[] = $c;
      ?>
      <?php foreach ($homeCategories as $c): ?>
        <a class="cat-item" href="<?= e(url('/services', ['category' => $c['slug']])) ?>">
          <span class="cat-icon"><?= category_art($c['slug'], $c['icon']) ?></span>
          <span class="cat-name"><?= e($c['slug'] === 'cleaning' ? 'Cleaning' : preg_replace('/ &.*$/', '', $c['name'])) ?></span>
        </a>
      <?php endforeach; ?>
      <a class="cat-item cat-more" href="<?= e(url('/categories')) ?>"><span class="cat-icon"><?= icon('more-services') ?></span><span class="cat-name">More</span></a>
    </div>
  </section>

  <?php if ($isPro && $jobs): ?>
  <section class="home-section">
    <div class="section-head">
      <h2>New jobs from customers</h2>
      <a class="more" href="<?= e(url('/jobs')) ?>">Job board</a>
    </div>
    <div class="grid grid-2">
      <?php foreach ($jobs as $j): ?><?= View::partial('jobs/_card', ['j' => $j]) ?><?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($popular): ?>
  <!-- Near you: services in the chosen city -->
  <section class="home-section home-popular">
    <div class="section-head">
      <div>
        <h2>Popular near you</h2>
        <p class="muted small"><?= $popularInCity ? 'Trusted services in ' : 'Trusted services near ' ?><?= e($city) ?></p>
      </div>
      <a class="more" href="<?= e(url('/services', $popularInCity ? ['city' => $city] : [])) ?>">View all <?= icon('chevron-right') ?></a>
    </div>
    <div class="near-row">
      <?php foreach ($popular as $s): ?><?= partial('near-card', ['s' => $s]) ?><?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if ($isClientOrGuest): ?>
  <section class="home-job-prompt">
    <?= icon('wrench') ?><strong>Have something else in mind?</strong>
    <span>Can't find the exact service? Tell us what you need and get matched with local pros.</span>
    <a class="btn btn-outline btn-sm" href="<?= e(url('/jobs/new')) ?>">Post a job</a>
  </section>
  <?php endif; ?>

  <!-- Top-rated pros -->
  <section class="home-section">
    <div class="section-head">
      <div>
        <h2>Top-rated pros</h2>
        <p class="muted small">ID-checked, nearest to <?= e($city) ?> first</p>
      </div>
      <a class="more" href="<?= e(url('/providers', ['sort' => 'top'])) ?>">See all</a>
    </div>
    <div class="pro-tiles">
      <?php foreach (array_slice($featured, 0, 4) as $p): ?><?= partial('pro-row', ['p' => $p]) ?><?php endforeach; ?>
    </div>
  </section>


  <?php if ($reviews): ?>
  <section class="home-section hide-md">
    <div class="section-head"><h2>Recent reviews</h2></div>
    <div class="review-scroller">
      <?php foreach ($reviews as $r): ?>
        <figure class="quote-card">
          <?= stars(5) ?>
          <blockquote><?= e($r['comment']) ?></blockquote>
          <figcaption>
            <?= avatar(['id' => $r['client_uid'], 'first_name' => $r['first_name'], 'last_name' => $r['last_name'], 'avatar' => $r['avatar']], 'sm') ?>
            <span><strong><?= e($r['first_name'] . ' ' . mb_substr($r['last_name'], 0, 1) . '.') ?></strong><small>Hired <?= e($r['p_first']) ?> for <?= e(mb_strtolower(preg_replace('/ &.*$/', '', (string) $r['category_name']))) ?></small></span>
          </figcaption>
        </figure>
      <?php endforeach; ?>
    </div>
  </section>
  <?php endif; ?>

  <?php if (!$u): ?>
  <section class="home-section hide-md">
    <div class="promo-band">
      <div class="promo-copy">
        <span class="eyebrow">For service pros</span>
        <h2>Grow your business with Servease</h2>
        <p>Get found by customers in your city, send quotes on open jobs and run every booking from one dashboard.</p>
        <ul class="check-list">
          <li><?= icon('check-circle') ?><span>Free profile with your services, prices and work photos</span></li>
          <li><?= icon('check-circle') ?><span>Verified badge that wins more customers</span></li>
          <li><?= icon('check-circle') ?><span>Set your own working hours</span></li>
        </ul>
        <a class="btn btn-accent btn-lg" href="<?= e(url('/register', ['role' => 'provider'])) ?>">Join as a pro <?= icon('arrow-right') ?></a>
      </div>
      <div class="promo-art"><?= partial('art', ['kind' => 'team']) ?></div>
    </div>
  </section>
  <?php endif; ?>
</div>

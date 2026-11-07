<?php
/** Live upcoming booking, reflowing for desktop, tablet, and phone. */
$person = ['id' => $next['other_id'], 'first_name' => $next['first_name'], 'last_name' => $next['last_name'], 'avatar' => $next['avatar']];
$step = ['requested' => 0, 'accepted' => 1, 'in_progress' => 2][$next['status']] ?? 0;
$checked = !$isPro && ($next['other_verification_status'] ?? '') === 'verified';
$date = $next['scheduled_date'] ? fmt_date($next['scheduled_date'], 'D, M j') : 'Date to be agreed';
$time = $next['scheduled_time'] ? date('g:i A', strtotime($next['scheduled_time'])) : '';
?>
<aside class="side-card upcoming-card" aria-label="Your next booking">
  <div class="side-head"><span class="side-title"><?= icon('ticket') ?><?= $isPro ? 'Your next job' : 'Your next booking' ?></span><?= badge($next['status']) ?></div>
  <div class="upcoming-details">
    <?= avatar($person, 'md', 'upcoming-avatar') ?>
    <div class="upcoming-service"><strong><?= e($next['title']) ?></strong><span class="upcoming-person"><?= $isPro ? 'for ' : '' ?><?= e($next['first_name'] . ' ' . mb_substr($next['last_name'], 0, 1) . '.') ?><?php if ($checked): ?> · ID checked <span class="verified-mark" title="ID checked"><?= icon('badge-check') ?></span><?php endif; ?></span></div>
    <div class="upcoming-schedule"><span><?= icon('calendar') ?><?= e($date) ?><?= $time ? ' · ' . e($time) : '' ?></span><strong><?= money($next['amount']) ?></strong></div>
    <a class="upcoming-open" href="<?= e(url('/bookings/' . $next['id'])) ?>" aria-label="View booking: <?= e($next['title']) ?>"><?= icon('chevron-right') ?></a>
  </div>
  <ol class="side-track" aria-label="Booking progress">
    <?php foreach (['Requested', 'Confirmed', 'In progress', 'Completed'] as $i => $label): ?>
      <li class="<?= $i < $step ? 'is-done' : ($i === $step ? 'is-now' : '') ?>"<?= $i === $step ? ' aria-current="step"' : '' ?>><span class="side-track-dot"><?= $i < $step ? icon('check') : '' ?></span><span class="side-track-lbl"><?= $label ?></span></li>
    <?php endforeach; ?>
  </ol>
  <div class="side-actions"><a class="btn btn-primary grow" href="<?= e(url('/bookings/' . $next['id'])) ?>">View booking</a><a class="icon-btn icon-btn-outline" href="<?= e(url('/messages')) ?>" aria-label="Open inbox"><?= icon('message') ?></a></div>
</aside>

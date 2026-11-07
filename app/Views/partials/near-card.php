<?php
/** Photo-led discovery card; a compact service row on phones. */
$link = url('/providers/' . $s['provider_id'], ['service' => $s['id']]) . '#services';
$photo = service_photo($s['category_slug'] ?? null, $s['cover'] ?? null, (int) $s['id']);
$p = price_parts($s['price'], $s['price_unit']);
$pro = ['id' => $s['provider_id'], 'first_name' => $s['first_name'], 'last_name' => $s['last_name'], 'avatar' => $s['avatar']];
?>
<article class="near-card">
  <span class="near-media<?= $photo ? '' : ' is-icon' ?>" aria-hidden="true">
    <?php if ($photo): ?><img src="<?= e($photo) ?>" alt="" loading="lazy" decoding="async"><?php else: ?><?= category_art($s['category_slug'] ?? null, $s['category_icon'] ?? 'wrench') ?><?php endif; ?>
  </span>
  <?php if (!is_role('provider', 'admin')): ?>
    <form class="card-save" method="post" action="<?= e(url('/providers/' . $s['provider_id'] . '/save')) ?>" data-save-toggle data-login="<?= e(url('/login')) ?>">
      <?= csrf_field() ?><button class="save-btn<?= !empty($s['is_saved']) ? ' is-saved' : '' ?>" type="submit" aria-pressed="<?= !empty($s['is_saved']) ? 'true' : 'false' ?>" aria-label="Save <?= e($s['first_name']) ?>" data-no-lock><?= icon('heart', 'icon-sm') ?></button>
    </form>
  <?php endif; ?>
  <div class="near-body">
    <h3 class="near-title"><a href="<?= e($link) ?>"><?= e($s['title']) ?></a></h3>
    <div class="near-provider"><?= avatar($pro, 'xs') ?><span><?= e($s['first_name'] . ' ' . mb_substr($s['last_name'], 0, 1) . '.') ?><?php if ($s['verification_status'] === 'verified'): ?> · ID checked<?php endif; ?></span><?php if ($s['verification_status'] === 'verified'): ?><span class="verified-mark" title="ID checked"><?= icon('badge-check') ?></span><?php endif; ?></div>
    <div class="near-bottom">
      <span class="near-rating"><?php if ((int) $s['rating_count'] > 0): ?><?= icon('star') ?><strong><?= number_format((float) $s['rating_avg'], 1) ?></strong> (<?= (int) $s['rating_count'] ?>)<?php else: ?><span class="muted">New pro</span><?php endif; ?></span>
      <span class="price-tag discovery-price"><?php if ($p['from']): ?><small>From</small><?php endif; ?><?= $p['amount'] ?><?php if (in_array($s['price_unit'], ['hour', 'day'], true)): ?><small><?= e($p['unit']) ?></small><?php endif; ?></span>
    </div>
  </div>
</article>

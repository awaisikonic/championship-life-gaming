<?php
if (!defined('ABSPATH')) { exit; }
?>
<div class="wrap">
  <h1>Demo Seed Import</h1>

  <?php if (!empty($notice)) : ?>
    <div class="notice notice-<?php echo (isset($_GET['clg_seed']) && $_GET['clg_seed'] === 'ok') ? 'success' : 'error'; ?> is-dismissible">
      <p><?php echo esc_html($notice); ?></p>
    </div>
  <?php endif; ?>

  <p>
    This importer creates a ready-to-test demo dataset:
    <strong>Week 1 (Days 1–7), Day 1 with 5 sequential mini-games, 5 questionnaires, and sample questions</strong>.
    It is safe to re-run: existing items with the same slug are updated.
  </p>

  <hr />

  <h2>Option A — One-click import (bundled)</h2>
  <?php if ($bundled_exists) : ?>
    <form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
      <?php wp_nonce_field('clg_seed_import_action', 'clg_seed_import_nonce'); ?>
      <input type="hidden" name="action" value="clg_import_seed" />
      <input type="hidden" name="mode" value="bundled" />
      <p>
        <button type="submit" class="button button-primary">Import Week 1 Demo Seed</button>
      </p>
    </form>
    <p class="description">
      Bundled seed file found at: <code><?php echo esc_html(str_replace(ABSPATH, '', $bundled_seed)); ?></code>
    </p>
  <?php else : ?>
    <p class="description" style="color:#b91c1c;">
      Bundled seed file not found. Please use Option B (upload JSON).
    </p>
  <?php endif; ?>

  <hr />

  <h2>Option B — Upload a seed JSON</h2>
  <form method="post" enctype="multipart/form-data" action="<?php echo esc_url(admin_url('admin-post.php')); ?>">
    <?php wp_nonce_field('clg_seed_import_action', 'clg_seed_import_nonce'); ?>
    <input type="hidden" name="action" value="clg_import_seed" />
    <input type="hidden" name="mode" value="upload" />

    <p>
      <input type="file" name="seed_file" accept="application/json,.json" required />
    </p>

    <p>
      <button type="submit" class="button">Upload & Import</button>
    </p>

    <p class="description">
      JSON must match the plugin seed schema (version 1). Use the bundled demo seed as an example.
    </p>
  </form>

  <hr />

  <h2>After import — quick checks</h2>
  <ol>
    <li>Go to <strong>Championship Life → Week Packs</strong> and open <strong>Week 1 - Level 1 Pack</strong>.</li>
    <li>Go to <strong>Championship Life → Days</strong> → open <strong>Day 1</strong> and confirm 5 mini-games appear (drag/drop order saved).</li>
    <li>Go to <strong>Mini-games</strong> and confirm each mini-game links to one questionnaire.</li>
    <li>Go to <strong>Questionnaires</strong> and confirm each has at least one question in its ordered list.</li>
  </ol>
</div>

<?php
/**
 * Day -> Week Pack (read-only) metabox.
 * Variables from render_day_pack_metabox():
 * @var int      $week_pack_id
 * @var WP_Post  $week_pack
 * @var int      $pack_number
 * @var int      $start_day
 * @var int      $end_day
 */
?>

<?php if (empty($week_pack_id) || empty($week_pack)) : ?>
  <p><em>No Week Pack assigned yet.</em></p>
  <p class="description">Create/edit a Week Pack and set its day range (e.g., 1–7). Saving the pack auto-links matching days.</p>
<?php else : ?>
  <p style="margin:0 0 8px;">
    <strong><?php echo esc_html($week_pack->post_title); ?></strong>
  </p>
  <p style="margin:0 0 8px;">
    Pack #<?php echo (int) $pack_number; ?><br>
    Days <?php echo (int) $start_day; ?>–<?php echo (int) $end_day; ?>
  </p>
  <p style="margin:0;">
    <a class="button button-small" href="<?php echo esc_url(get_edit_post_link($week_pack_id)); ?>">Edit Week Pack</a>
  </p>
<?php endif; ?>

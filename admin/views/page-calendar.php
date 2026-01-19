<?php
/**
 * Calendar & Days page.
 * Vars: $base_start_date, $total_days, $tz, $reset_time, $default_theme_id
 */

$themes = get_posts([
    'post_type' => CLG_CPT::CPT_THEME,
    'posts_per_page' => -1,
    'post_status' => ['publish','draft'],
    'orderby' => 'title',
    'order' => 'ASC',
]);
?>

<div class="wrap">
  <h1>Championship Life — Calendar & Days</h1>
  <?php settings_errors('clg_calendar'); ?>

  <p class="description">Days are fixed calendar entries: Day N = Base Start Date + (N-1). This tool creates/updates Day posts safely.</p>

  <form method="post" action="options.php" style="max-width:860px;">
    <?php settings_fields('clg_calendar'); ?>
    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row"><label for="<?php echo esc_attr(CLG_Options::KEY_BASE_START_DATE); ?>">Base start date</label></th>
          <td>
            <input type="date" name="<?php echo esc_attr(CLG_Options::KEY_BASE_START_DATE); ?>" id="<?php echo esc_attr(CLG_Options::KEY_BASE_START_DATE); ?>" value="<?php echo esc_attr($base_start_date); ?>" />
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="<?php echo esc_attr(CLG_Options::KEY_TOTAL_DAYS); ?>">Total days</label></th>
          <td>
            <input type="number" min="1" step="1" class="small-text" name="<?php echo esc_attr(CLG_Options::KEY_TOTAL_DAYS); ?>" id="<?php echo esc_attr(CLG_Options::KEY_TOTAL_DAYS); ?>" value="<?php echo esc_attr($total_days); ?>" />
            <p class="description">Generate 30/90 days (you can change later and re-sync).</p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="<?php echo esc_attr(CLG_Options::KEY_TIMEZONE); ?>">Timezone</label></th>
          <td>
            <input type="text" class="regular-text" name="<?php echo esc_attr(CLG_Options::KEY_TIMEZONE); ?>" id="<?php echo esc_attr(CLG_Options::KEY_TIMEZONE); ?>" value="<?php echo esc_attr($tz); ?>" placeholder="America/New_York" />
            <p class="description">Used later for daily reset and streak calculations.</p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="<?php echo esc_attr(CLG_Options::KEY_DAILY_RESET_TIME); ?>">Daily reset time</label></th>
          <td>
            <input type="time" name="<?php echo esc_attr(CLG_Options::KEY_DAILY_RESET_TIME); ?>" id="<?php echo esc_attr(CLG_Options::KEY_DAILY_RESET_TIME); ?>" value="<?php echo esc_attr($reset_time); ?>" />
            <p class="description">Example: 00:00 means day changes at midnight.</p>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="<?php echo esc_attr(CLG_Options::KEY_DEFAULT_THEME_ID); ?>">Default theme</label></th>
          <td>
            <select name="<?php echo esc_attr(CLG_Options::KEY_DEFAULT_THEME_ID); ?>" id="<?php echo esc_attr(CLG_Options::KEY_DEFAULT_THEME_ID); ?>">
              <option value="0">— None —</option>
              <?php foreach ($themes as $t): ?>
                <option value="<?php echo (int)$t->ID; ?>" <?php selected((int)$default_theme_id, (int)$t->ID); ?>><?php echo esc_html($t->post_title); ?> (ID: <?php echo (int)$t->ID; ?>)</option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
      </tbody>
    </table>

    <?php submit_button('Save Calendar Settings'); ?>
  </form>

  <hr />

  <form method="post" style="margin-top:16px;">
    <?php wp_nonce_field('clg_generate_days_action', 'clg_generate_days_nonce'); ?>
    <input type="hidden" name="clg_generate_days" value="1" />
    <?php submit_button('Generate / Sync Days', 'primary'); ?>
    <p class="description">This will create missing Days and update existing ones to match the calendar.</p>
  </form>
</div>

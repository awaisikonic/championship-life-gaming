<?php
/**
 * Settings page.
 * Vars: $opts (array)
 */

function clg_checked($v) { return ((int)$v) === 1 ? 'checked' : ''; }

$themes = get_posts([
    'post_type' => CLG_CPT::CPT_THEME,
    'posts_per_page' => -1,
    'post_status' => ['publish','draft'],
    'orderby' => 'title',
    'order' => 'ASC',
]);
?>

<div class="wrap">
  <h1>Championship Life — Rewards & Rules</h1>
  <p class="description">Gameplay rules and reward configuration. (Frontend gameplay uses REST later—this page only stores options.)</p>

  <form method="post" action="options.php" style="max-width:900px;">
    <?php settings_fields('clg_settings'); ?>

    <table class="form-table" role="presentation">
      <tbody>
        <tr>
          <th scope="row">Day unlocking</th>
          <td>
            <label><input type="checkbox" name="<?php echo esc_attr(CLG_Options::KEY_ALLOW_PLAY_AHEAD); ?>" value="1" <?php echo clg_checked($opts['allow_play_ahead']); ?> /> Allow play-ahead (future days clickable)</label>
          </td>
        </tr>

        <tr>
          <th scope="row">Day completion</th>
          <td>
            <label><input type="checkbox" name="<?php echo esc_attr(CLG_Options::KEY_DAY_COMPLETE_REQUIRES_ALL); ?>" value="1" <?php echo clg_checked($opts['day_complete_requires_all']); ?> /> Day requires all mini-games complete</label>
          </td>
        </tr>

        <tr>
          <th scope="row">Mini-game sequencing</th>
          <td>
            <p class="description">Within a day, mini-games are sequential (Game 2 locked until Game 1 completed). This is enforced by progress logic in Step 3/4.</p>
          </td>
        </tr>

        <tr>
          <th scope="row">Replay</th>
          <td>
            <label style="display:block;margin-bottom:6px;"><input type="checkbox" name="<?php echo esc_attr(CLG_Options::KEY_ALLOW_REPLAY); ?>" value="1" <?php echo clg_checked($opts['allow_replay']); ?> /> Allow replay</label>
            <label><input type="checkbox" name="<?php echo esc_attr(CLG_Options::KEY_REPLAY_AWARDS_POINTS); ?>" value="1" <?php echo clg_checked($opts['replay_awards_points']); ?> /> Replays award points (usually OFF)</label>
          </td>
        </tr>

        <tr>
          <th scope="row">Streak trigger</th>
          <td>
            <select name="<?php echo esc_attr(CLG_Options::KEY_STREAK_TRIGGER); ?>">
              <option value="any" <?php selected($opts['streak_trigger'], 'any'); ?>>Any mini-game completed in a day</option>
              <option value="all" <?php selected($opts['streak_trigger'], 'all'); ?>>Only if day completed</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="clg_default_minigame_points">Default mini-game points</label></th>
          <td>
            <input type="number" class="small-text" min="0" step="1" id="clg_default_minigame_points" name="<?php echo esc_attr(CLG_Options::KEY_DEFAULT_MINIGAME_POINTS); ?>" value="<?php echo esc_attr($opts['default_minigame_points']); ?>" />
          </td>
        </tr>

        <tr>
          <th scope="row">Day bonus</th>
          <td>
            <label style="display:block;margin-bottom:6px;"><input type="checkbox" name="<?php echo esc_attr(CLG_Options::KEY_DAY_BONUS_ENABLED); ?>" value="1" <?php echo clg_checked($opts['day_bonus_enabled']); ?> /> Enable day completion bonus</label>
            <label>Points: <input type="number" class="small-text" min="0" step="1" name="<?php echo esc_attr(CLG_Options::KEY_DAY_BONUS_POINTS); ?>" value="<?php echo esc_attr($opts['day_bonus_points']); ?>" /></label>
          </td>
        </tr>

        <tr>
          <th scope="row">Pack bonus</th>
          <td>
            <label style="display:block;margin-bottom:6px;"><input type="checkbox" name="<?php echo esc_attr(CLG_Options::KEY_PACK_BONUS_ENABLED); ?>" value="1" <?php echo clg_checked($opts['pack_bonus_enabled']); ?> /> Enable pack completion bonus</label>
            <label>Points: <input type="number" class="small-text" min="0" step="1" name="<?php echo esc_attr(CLG_Options::KEY_PACK_BONUS_POINTS); ?>" value="<?php echo esc_attr($opts['pack_bonus_points']); ?>" /></label>
          </td>
        </tr>

        <tr>
          <th scope="row">Default Theme</th>
          <td>
            <select name="<?php echo esc_attr(CLG_Options::KEY_DEFAULT_THEME_ID); ?>">
              <option value="0">— None —</option>
              <?php foreach ($themes as $t): ?>
                <option value="<?php echo (int)$t->ID; ?>" <?php selected((int)$opts['default_theme_id'], (int)$t->ID); ?>><?php echo esc_html($t->post_title); ?> (ID: <?php echo (int)$t->ID; ?>)</option>
              <?php endforeach; ?>
            </select>
          </td>
        </tr>
      </tbody>
    </table>

    <?php submit_button('Save Settings'); ?>
  </form>
</div>

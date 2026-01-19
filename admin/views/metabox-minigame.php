<?php
/**
 * Mini-game Settings metabox.
 *
 * Variables from render_minigame_metabox():
 * @var string    $type
 * @var int       $points
 * @var int       $questionnaire_id
 * @var string    $icon_key
 * @var WP_Post[] $questionnaires
 */

$types = [
    'single_mcq' => 'MCQ (Single Select)',
    'multi_mcq'  => 'MCQ (Multi Select)',
    'text'       => 'Text',
    'slider'     => 'Slider',
];
?>

<table class="form-table" role="presentation">
  <tbody>
    <tr>
      <th scope="row"><label for="cl_minigame_type">Mini-game type</label></th>
      <td>
        <select name="cl_minigame_type" id="cl_minigame_type">
          <?php foreach ($types as $k => $label): ?>
            <option value="<?php echo esc_attr($k); ?>" <?php selected($type, $k); ?>><?php echo esc_html($label); ?></option>
          <?php endforeach; ?>
        </select>
        <p class="description">Controls how the questionnaire is rendered on the frontend.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_points">Points (first completion)</label></th>
      <td>
        <input type="number" min="0" step="1" name="cl_points" id="cl_points" value="<?php echo esc_attr($points); ?>" class="small-text" />
        <p class="description">Points are awarded only on first completion. Replays award 0.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_circle_label">Circle label (optional)</label></th>
      <td>
        <input type="text" name="cl_circle_label" id="cl_circle_label" value="<?php echo esc_attr($circle_label ?? ''); ?>" class="regular-text" />
        <p class="description">Short label shown on the mini-game circle (e.g., “1”, “A”, “Q1”).</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_questionnaire_id">Questionnaire</label></th>
      <td>
        <select name="cl_questionnaire_id" id="cl_questionnaire_id">
          <option value="0">— Select —</option>
          <?php foreach ($questionnaires as $q): ?>
            <option value="<?php echo (int) $q->ID; ?>" <?php selected($questionnaire_id, (int) $q->ID); ?>>
              <?php echo esc_html($q->post_title); ?> (ID: <?php echo (int) $q->ID; ?>)
            </option>
          <?php endforeach; ?>
        </select>
        <p class="description">Each mini-game maps to one questionnaire.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_icon_key">Icon key (optional)</label></th>
      <td>
        <input type="text" name="cl_icon_key" id="cl_icon_key" value="<?php echo esc_attr($icon_key); ?>" class="regular-text" />
        <p class="description">Optional key for the frontend to choose an icon (e.g., bolt, pencil).</p>
      </td>
    </tr>
  </tbody>
</table>

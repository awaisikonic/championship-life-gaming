<?php
/**
 * Question Settings metabox.
 * Vars from render_question_metabox():
 * @var string $question_text
 * @var array  $linked_questionnaires
 * @var string $qtype
 * @var int    $required
 * @var string $options_json
 * @var mixed  $min
 * @var mixed  $max
 * @var mixed  $step
 * @var int    $min_len
 * @var int    $max_len
 * @var int    $sort_order
 */

$qtypes = [
    'single_mcq' => 'MCQ (Single Select)',
    'multi_mcq'  => 'MCQ (Multi Select)',
    'text'       => 'Text',
    'slider'     => 'Slider',
];

$decoded = [];
if (!empty($options_json)) {
    $tmp = json_decode($options_json, true);
    if (is_array($tmp)) { $decoded = $tmp; }
}
$options_text = '';
if (!empty($decoded)) {
    $options_text = implode("\n", array_map('strval', $decoded));
}
?>

<table class="form-table" role="presentation">
  <tbody>
    <tr>
      <th scope="row"><label for="cl_question_text">Question Text</label></th>
      <td>
        <textarea name="cl_question_text" id="cl_question_text" rows="4" class="large-text"><?php echo esc_textarea($question_text); ?></textarea>
        <p class="description">Stored in the Question post content. This is what the user sees during gameplay.</p>
      </td>
    </tr>

    <tr>
      <th scope="row">Linked Questionnaires</th>
      <td>
        <?php if (empty($linked_questionnaires)): ?>
          <em>Not linked to any questionnaire yet.</em>
        <?php else: ?>
          <ul style="margin:0; padding-left:18px;">
            <?php foreach ($linked_questionnaires as $qq): ?>
              <li>
                <a href="<?php echo esc_url(get_edit_post_link($qq->ID)); ?>"><?php echo esc_html($qq->post_title); ?></a>
                <code style="margin-left:6px;">ID: <?php echo (int) $qq->ID; ?></code>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
        <p class="description">Questions are attached to questionnaires from the Questionnaire edit screen.</p>
      </td>
    </tr>
    <tr>
      <th scope="row"><label for="cl_question_type">Question type</label></th>
      <td>
        <select name="cl_question_type" id="cl_question_type">
          <?php foreach ($qtypes as $k => $label): ?>
            <option value="<?php echo esc_attr($k); ?>" <?php selected($qtype, $k); ?>><?php echo esc_html($label); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>

    <tr>
      <th scope="row">Required?</th>
      <td>
        <label><input type="checkbox" name="cl_required" value="1" <?php checked($required, 1); ?> /> Yes</label>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_sort_order">Sort order</label></th>
      <td>
        <input type="number" name="cl_sort_order" id="cl_sort_order" value="<?php echo esc_attr($sort_order); ?>" class="small-text" min="1" step="1" />
        <p class="description">Used when a questionnaire wants to render questions in order.</p>
      </td>
    </tr>

    <tr class="clg-row clg-row-options">
      <th scope="row"><label for="cl_options_text">Options (MCQ)</label></th>
      <td>
        <textarea name="cl_options_lines" id="cl_options_text" rows="6" class="large-text" placeholder="One option per line"><?php echo esc_textarea($options_text); ?></textarea>
        <p class="description">For single/multi MCQ only. Saved as JSON array.</p>
      </td>
    </tr>

    <tr class="clg-row clg-row-slider">
      <th scope="row">Slider range</th>
      <td>
        <label>Min <input type="number" name="cl_min" value="<?php echo esc_attr($min); ?>" class="small-text" step="1" /></label>
        <label style="margin-left:10px;">Max <input type="number" name="cl_max" value="<?php echo esc_attr($max); ?>" class="small-text" step="1" /></label>
        <label style="margin-left:10px;">Step <input type="number" name="cl_step" value="<?php echo esc_attr($step); ?>" class="small-text" step="1" /></label>
        <p class="description">For slider type. Enforced on submit in gameplay.</p>
      </td>
    </tr>

    <tr class="clg-row clg-row-text">
      <th scope="row">Text limits</th>
      <td>
        <label>Min length <input type="number" name="cl_min_len" value="<?php echo esc_attr($min_len); ?>" class="small-text" min="0" step="1" /></label>
        <label style="margin-left:10px;">Max length <input type="number" name="cl_max_len" value="<?php echo esc_attr($max_len); ?>" class="small-text" min="1" step="1" /></label>
        <p class="description">For text type. Default max length is 500.</p>
      </td>
    </tr>
  </tbody>
</table>

<script>
(function(){
  function toggleRows(){
    var t = document.getElementById('cl_question_type');
    if(!t) return;
    var val = t.value;
    var opt = document.querySelectorAll('.clg-row-options');
    var sli = document.querySelectorAll('.clg-row-slider');
    var txt = document.querySelectorAll('.clg-row-text');
    opt.forEach(function(el){ el.style.display = (val==='single_mcq'||val==='multi_mcq') ? '' : 'none'; });
    sli.forEach(function(el){ el.style.display = (val==='slider') ? '' : 'none'; });
    txt.forEach(function(el){ el.style.display = (val==='text') ? '' : 'none'; });
  }
  document.addEventListener('DOMContentLoaded', toggleRows);
  var t = document.getElementById('cl_question_type');
  if(t) t.addEventListener('change', toggleRows);
  toggleRows();
})();
</script>

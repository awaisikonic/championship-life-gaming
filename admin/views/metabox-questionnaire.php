<?php
/**
 * Questionnaire Settings metabox.
 *
 * Variables from render_questionnaire_metabox():
 * @var string    $instructions
 * @var string    $questionnaire_type
 * @var string    $completion_mode
 * @var string    $scoring_mode
 * @var array     $selected_ids
 * @var WP_Post[] $questions
 */

$completion_modes = [
    'all_questions' => 'All required questions',
];

$scoring_modes = [
    'participation' => 'Participation (no correctness)',
];

$types = [
    'single_mcq' => 'MCQ (Single Select)',
    'multi_mcq'  => 'MCQ (Multi Select)',
    'text'       => 'Text',
    'slider'     => 'Slider',
];

$questions_by_id = [];
foreach ($questions as $q) {
    $questions_by_id[(int)$q->ID] = $q;
}
?>

<table class="form-table" role="presentation">
  <tbody>
    <tr>
      <th scope="row"><label for="cl_questionnaire_type">Type</label></th>
      <td>
        <select name="cl_questionnaire_type" id="cl_questionnaire_type">
          <?php foreach ($types as $k => $label): ?>
            <option value="<?php echo esc_attr($k); ?>" <?php selected($questionnaire_type, $k); ?>><?php echo esc_html($label); ?></option>
          <?php endforeach; ?>
        </select>
        <p class="description">Controls which questionnaire template is used on the frontend.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_instructions">Instructions (optional)</label></th>
      <td>
        <textarea name="cl_instructions" id="cl_instructions" rows="3" class="large-text"><?php echo esc_textarea($instructions); ?></textarea>
        <p class="description">Shown above the questionnaire to guide the user.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_completion_mode">Completion mode</label></th>
      <td>
        <select name="cl_completion_mode" id="cl_completion_mode">
          <?php foreach ($completion_modes as $k => $label): ?>
            <option value="<?php echo esc_attr($k); ?>" <?php selected($completion_mode, $k); ?>><?php echo esc_html($label); ?></option>
          <?php endforeach; ?>
        </select>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_scoring_mode">Scoring mode</label></th>
      <td>
        <select name="cl_scoring_mode" id="cl_scoring_mode">
          <?php foreach ($scoring_modes as $k => $label): ?>
            <option value="<?php echo esc_attr($k); ?>" <?php selected($scoring_mode, $k); ?>><?php echo esc_html($label); ?></option>
          <?php endforeach; ?>
        </select>
        <p class="description">MVP uses participation only. Correctness rules can be added later.</p>
      </td>
    </tr>

    <tr>
      <th scope="row">Questions (ordered)</th>
      <td>
        <p class="description">Use the dropdown to add questions and drag to reorder. The order defines the UI sequence.</p>

        <div style="margin:10px 0;">
          <select id="clg_add_question" style="min-width:360px;">
            <option value="">Select a question...</option>
            <?php foreach ($questions as $q): ?>
              <option value="<?php echo (int) $q->ID; ?>"><?php echo esc_html($q->post_title); ?> (ID: <?php echo (int) $q->ID; ?>)</option>
            <?php endforeach; ?>
          </select>
          <button type="button" class="button" id="clg_add_question_btn" style="margin-left:6px;">Add</button>
        </div>

        <input type="hidden" name="cl_question_ids_csv" value="" />

        <ul id="clg_question_sortable" class="clg-sortable" style="margin:12px 0;max-width:700px;">
          <?php if (empty($selected_ids)): ?>
            <li class="clg-empty" style="padding:10px;border:1px dashed #c3c4c7;background:#f6f7f7;">No questions selected yet.</li>
          <?php else: ?>
            <?php foreach ($selected_ids as $qid):
              $qid = (int) $qid;
              $q = isset($questions_by_id[$qid]) ? $questions_by_id[$qid] : get_post($qid);
              if (!$q) continue;
            ?>
              <li class="clg-item" data-id="<?php echo $qid; ?>" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #dcdcde;background:#fff;margin-bottom:6px;cursor:move;">
                <span class="dashicons dashicons-move" aria-hidden="true"></span>
                <strong style="flex:1;"><?php echo esc_html($q->post_title); ?></strong>
                <code>ID: <?php echo $qid; ?></code>
                <input type="hidden" name="cl_question_ids[]" value="<?php echo $qid; ?>" />
                <button type="button" class="button-link-delete clg-remove" style="margin-left:8px;">Remove</button>
              </li>
            <?php endforeach; ?>
          <?php endif; ?>
        </ul>

        <p class="description">Saved as CSV in post meta for simplicity.</p>
      </td>
    </tr>
  </tbody>
</table>

<script>
(function(){
  // Lightweight admin-only logic: add/remove list items.
  // Sorting is handled by WordPress's jQuery UI sortable already enqueued on edit screens.
  var addBtn = document.getElementById('clg_add_question_btn');
  var select = document.getElementById('clg_add_question');
  var list = document.getElementById('clg_question_sortable');
  if (!addBtn || !select || !list) return;

  function hasId(id){
    return !!list.querySelector('li.clg-item[data-id="'+id+'"]');
  }

  function removeEmpty(){
    var empty = list.querySelector('li.clg-empty');
    if (empty) empty.remove();
  }

  addBtn.addEventListener('click', function(){
    var id = parseInt(select.value, 10);
    if (!id) return;
    if (hasId(id)) return;
    removeEmpty();

    var title = select.options[select.selectedIndex].text;
    var li = document.createElement('li');
    li.className = 'clg-item';
    li.setAttribute('data-id', id);
    li.style.cssText = 'display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #dcdcde;background:#fff;margin-bottom:6px;cursor:move;';
    li.innerHTML = '<span class="dashicons dashicons-move" aria-hidden="true"></span>'+
                   '<strong style="flex:1;">'+title.replace(/\s*\(ID:.*\)\s*$/, '')+'</strong>'+
                   '<code>ID: '+id+'</code>'+
                   '<input type="hidden" name="cl_question_ids[]" value="'+id+'" />'+
                   '<button type="button" class="button-link-delete clg-remove" style="margin-left:8px;">Remove</button>';
    list.appendChild(li);
  });

  list.addEventListener('click', function(e){
    var btn = e.target.closest('button.clg-remove');
    if (!btn) return;
    var li = btn.closest('li.clg-item');
    if (li) li.remove();
    if (!list.querySelector('li.clg-item')) {
      var empty = document.createElement('li');
      empty.className = 'clg-empty';
      empty.style.cssText = 'padding:10px;border:1px dashed #c3c4c7;background:#f6f7f7;';
      empty.textContent = 'No questions selected yet.';
      list.appendChild(empty);
    }
  });
})();
</script>

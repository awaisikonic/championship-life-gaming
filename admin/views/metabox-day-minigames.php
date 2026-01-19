<?php
/**
 * Day -> Mini-games (sequential) metabox.
 * Variables provided by render_day_minigames_metabox():
 * @var WP_Post   $post
 * @var array     $rows
 * @var WP_Post[] $minigames
 */

$selected_ids = [];
foreach ($rows as $r) {
    $selected_ids[] = (int) $r->minigame_post_id;
}

$by_id = [];
foreach ($minigames as $mg) {
    $by_id[(int) $mg->ID] = $mg;
}
?>

<div class="clg-metabox">
    <p class="description">
        Add mini-games to this Day and reorder them. <strong>Unlock is sequential</strong>: Game 1 unlocks first, then Game 2, and so on.
    </p>

    <div style="margin:12px 0;">
        <label for="clg_add_minigame" style="display:block;font-weight:600;margin-bottom:6px;">Add mini-game</label>
        <select id="clg_add_minigame" style="min-width:320px;">
            <option value="">Select a mini-game...</option>
            <?php foreach ($minigames as $mg): ?>
                <option value="<?php echo (int) $mg->ID; ?>"><?php echo esc_html($mg->post_title); ?> (ID: <?php echo (int) $mg->ID; ?>)</option>
            <?php endforeach; ?>
        </select>
        <button type="button" class="button" id="clg_add_minigame_btn" style="margin-left:6px;">Add</button>
    </div>

    <input type="hidden" name="cl_day_minigame_ids[]" value="" />

    <ul id="clg_minigame_sortable" class="clg-sortable" style="margin:12px 0;">
        <?php if (empty($selected_ids)): ?>
            <li class="clg-empty" style="padding:10px;border:1px dashed #c3c4c7;background:#f6f7f7;">No mini-games assigned yet. Use the dropdown above to add.</li>
        <?php else: ?>
            <?php foreach ($selected_ids as $mg_id):
                $mg = isset($by_id[$mg_id]) ? $by_id[$mg_id] : get_post($mg_id);
                if (!$mg) continue;
            ?>
                <li class="clg-item" data-id="<?php echo (int) $mg_id; ?>" style="display:flex;align-items:center;gap:10px;padding:10px;border:1px solid #dcdcde;background:#fff;margin-bottom:6px;cursor:move;">
                    <span class="dashicons dashicons-move" aria-hidden="true"></span>
                    <strong style="flex:1;"><?php echo esc_html($mg->post_title); ?></strong>
                    <code>ID: <?php echo (int) $mg_id; ?></code>
                    <input type="hidden" name="cl_day_minigame_ids[]" value="<?php echo (int) $mg_id; ?>" />
                    <button type="button" class="button-link-delete clg-remove" style="margin-left:8px;">Remove</button>
                </li>
            <?php endforeach; ?>
        <?php endif; ?>
    </ul>

    <p class="description">
        Tip: Drag items to reorder. The order here defines the sequential unlock (Game 1 -> Game 2 -> ...).
    </p>
</div>

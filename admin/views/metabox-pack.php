<?php
/**
 * Week Pack metabox.
 * Vars: $pack_number, $start_day, $end_day
 */
?>

<table class="form-table" role="presentation">
  <tbody>
    <tr>
      <th scope="row"><label for="cl_pack_number">Pack number</label></th>
      <td>
        <input type="number" name="cl_pack_number" id="cl_pack_number" value="<?php echo esc_attr($pack_number); ?>" class="small-text" min="1" step="1" />
        <p class="description">Example: Week 1 = Pack 1.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_start_day">Start day number</label></th>
      <td>
        <input type="number" name="cl_start_day" id="cl_start_day" value="<?php echo esc_attr($start_day); ?>" class="small-text" min="1" step="1" />
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_end_day">End day number</label></th>
      <td>
        <input type="number" name="cl_end_day" id="cl_end_day" value="<?php echo esc_attr($end_day); ?>" class="small-text" min="1" step="1" />
        <p class="description">Days in this range belong to this pack.</p>
      </td>
    </tr>
  </tbody>
</table>

<p class="description">After saving, days in this range will reference this pack number via meta. You can change the range anytime.</p>

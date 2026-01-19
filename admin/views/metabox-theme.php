<?php
/**
 * Theme metabox.
 * Variables from render_theme_metabox():
 * @var string $primary
 * @var string $secondary
 * @var string $bg_token
 */
?>

<table class="form-table" role="presentation">
  <tbody>
    <tr>
      <th scope="row"><label for="cl_primary">Primary color</label></th>
      <td>
        <input type="text" name="cl_primary" id="cl_primary" value="<?php echo esc_attr($primary); ?>" class="regular-text" placeholder="#111827" />
        <p class="description">Hex color used as the primary accent.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_secondary">Secondary color</label></th>
      <td>
        <input type="text" name="cl_secondary" id="cl_secondary" value="<?php echo esc_attr($secondary); ?>" class="regular-text" placeholder="#6b7280" />
        <p class="description">Hex color used as secondary accent/text.</p>
      </td>
    </tr>

    <tr>
      <th scope="row"><label for="cl_bg_token">Background token</label></th>
      <td>
        <input type="text" name="cl_bg_token" id="cl_bg_token" value="<?php echo esc_attr($bg_token); ?>" class="regular-text" placeholder="default" />
        <p class="description">A simple label your frontend can interpret (e.g., day1, day2, dark).</p>
      </td>
    </tr>
  </tbody>
</table>

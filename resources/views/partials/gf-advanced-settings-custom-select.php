<li class="external_options field_setting">
    <label for="field_populate_external_option" class="section_label">
        <?php _e('Externe opties', '') ?>
        <?php gform_tooltip('form_field_external_options') ?>
    </label>
    <select id="field_populate_external_options_value" onchange="SetFieldProperty('field_populate_external_option', this.value);">
        <option value="">Kies een optie</option>
        <option value="post.locations">Locaties</option>
        <?php foreach ($vars['external_options'] ?? [] as $option) : ?>
            <option value="<?php echo $option['value']; ?>">
                <?php echo $option['label']; ?>
            </option>
        <?php endforeach; ?>
    </select>
</li>

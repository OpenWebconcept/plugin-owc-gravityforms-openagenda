<script type='text/javascript'>
    jQuery.each(fieldSettings, function(index, value) {
        if (['select', 'multiselect', 'checkbox', 'radio'].includes(index)) {
            fieldSettings[index] += ', .external_options';
        }
    });

    // Select and multiselect elements.
    jQuery(document).bind('gform_load_field_settings', function(event, field, form) {
        jQuery('#field_populate_external_options_value').val(field['field_populate_external_option']);
    });
</script>

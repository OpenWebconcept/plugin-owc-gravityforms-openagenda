<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use GFAddOn;
use GFForms;
use OWC\OpenAgenda\Foundation\ServiceProvider;

class GravityFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->registerAddOn();

        $hooks = new Hooks();
        add_filter('gform_predefined_choices', $hooks->addBulkChoices(...));
        add_filter('gform_pre_render', $hooks->populateExternalOptionsFields(...));
        add_filter('gform_pre_validation', $hooks->populateExternalOptionsFields(...));
        add_filter('gform_pre_submission_filter', $hooks->populateExternalOptionsFields(...));

        if (is_admin()) {
            add_filter('gform_admin_pre_render', $hooks->populateExternalOptionsFields(...));
        }

        add_action('gform_field_advanced_settings', $hooks->addExternalOptionsSelect(...), 10, 2);
        add_action('gform_editor_js', $hooks->addExternalOptionsSelectScript(...));
        add_action('gform_tooltips', $hooks->addExternalOptionsSelectTooltip(...));
    }

    public function registerAddOn(): void
    {
        if (! method_exists('GFForms', 'include_addon_framework')) {
            return;
        }

        GFForms::include_feed_addon_framework();
        GFAddOn::register(GravityFormsAddOn::class);
        GravityFormsAddOn::get_instance();
    }
}

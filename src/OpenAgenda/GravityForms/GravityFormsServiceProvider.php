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
        $hooks = new Hooks();

        $this->plugin->loader->addAction('gform_loaded', $this, 'registerAddOn');
        $this->plugin->loader->addFilter('gform_predefined_choices', $hooks, 'addBulkChoices');
        $this->plugin->loader->addFilter('gform_pre_render', $hooks, 'populateExternalOptionsFields');
        $this->plugin->loader->addFilter('gform_pre_validation', $hooks, 'populateExternalOptionsFields');
        $this->plugin->loader->addFilter('gform_pre_submission_filter', $hooks, 'populateExternalOptionsFields');

        if (is_admin()) {
            $this->plugin->loader->addFilter('gform_admin_pre_render', $hooks, 'populateExternalOptionsFields');
        }

        $this->plugin->loader->addAction('gform_field_advanced_settings', $hooks, 'addExternalOptionsSelect', 10, 2);
        $this->plugin->loader->addAction('gform_editor_js', $hooks, 'addExternalOptionsSelectScript');
        $this->plugin->loader->addAction('gform_tooltips', $hooks, 'addExternalOptionsSelectTooltip');
    }

    public function registerAddOn(): void
    {
        GFForms::include_feed_addon_framework();
        GFAddOn::register(GravityFormsAddOn::class);
    }
}

<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use GFAddOn;
use GFForms;
use function OWC\OpenAgenda\Foundation\resolve;
use OWC\OpenAgenda\Foundation\ServiceProvider;

class GravityFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->plugin->loader->addAction('gform_loaded', $this, 'registerAddOn');
        $this->plugin->loader->addFilter('gform_predefined_choices', $this, 'addBulkChoices');
    }

    public function registerAddOn(): void
    {
        GFForms::include_feed_addon_framework();
        GFAddOn::register(GravityFormsAddon::class);
    }

    public function addBulkChoices(array $choices): array
    {
        $predefinedChoices = resolve('config')->get('predefined_choices_gf', []);

        foreach ($predefinedChoices as $key => $value) {
            $choices[$key] = $value;
        }

        return $choices;
    }
}

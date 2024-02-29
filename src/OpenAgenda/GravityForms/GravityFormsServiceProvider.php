<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use OWC\OpenAgenda\Foundation\ServiceProvider;

class GravityFormsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->plugin->loader->addAction('gform_loaded', $this, 'registerAddOn');
        $this->plugin->loader->addFilter('gform_predefined_choices', $this, 'addBulkChoices');
    }

    public function registerAddOn()
    {
        \GFForms::include_feed_addon_framework();
        \GFAddOn::register(GravityFormsAddon::class);
    }

    public function addBulkChoices(array $choices): array
    {
        //TODO: verplaatsen naar config file
        $choices['OpenAgenda: Prijstype'] = [
            'Vast (of gratis)|fixed',
            'Vanaf|min',
            'Variabel|min_max',
        ];

        $choices['OpenAgenda: Datumtype'] = [
            'Specified|specific',
            'Comples|complex',
        ];
        $choices['OpenAgenda: Weekdag patroon'] = [
            'Elke|every',
            'Elke eerste|first',
            'Elke tweede|second',
            'Elke derde|third',
            'Elke vierde|fourth',
            'Elke laatste|last'
        ];
        $choices['OpenAgenda: Weekdagen'] = [
            'Maandag|monday',
            'Dinsdag|tuesday',
            'Woensdag|wednesday',
            'Donderdag|thursday',
            'Vrijdag|friday',
            'Zaterdag|saturday',
            'Zondag|sunday',
        ];
        $choices['OpenAgenda: Maanden'] = [
            'Januari|january',
            'Februari|february',
            'Maart|march',
            'April|april',
            'Mei|may',
            'Juni|june',
            'Juli|july',
            'Augustus|august',
            'September|september',
            'Oktober|october',
            'November|november',
            'December|december'
        ];
        $choices['OpenAgenda: Toegankelijkheid'] = [
            'Openbaar|public',
            'Gedeeltelijk openbaar|partly_public',
            'Besloten|closed',
        ];
        $choices['OpenAgenda: Registratie'] = [
            'Niet vereist|not_required',
            'Verplicht|mandatory',
            'Optioneel|optional',
        ];

        return $choices;
    }
}

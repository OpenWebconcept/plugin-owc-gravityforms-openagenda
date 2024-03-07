<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use function OWC\OpenAgenda\Foundation\resolve;
use function OWC\OpenAgenda\Foundation\view;

class Hooks
{
    public function addBulkChoices(array $choices): array
    {
        $predefinedChoices = resolve('config')->get('predefined_choices_gf', []);

        foreach ($predefinedChoices as $key => $value) {
            $choices[$key] = $value;
        }

        return $choices;
    }

    public function addExternalOptionsSelect($position, $formID): void
    {
        //create settings on position 50 (right after Admin Label)
        if (50 !== $position) {
            return;
        }

        $taxonomies = (new \OWC\OpenAgenda\Http\Endpoints\GetEventTaxonomies())->request('GET');

        echo view('partials/gf-advanced-settings-custom-select.php', ['external_options' => array_map(function ($taxonomy) {
            return [
                'value' => sprintf('tax.%s', $taxonomy['rest_base']),
                'label' => $taxonomy['name'],
            ];
        }, $taxonomies)]);
    }

    public function addExternalOptionsSelectScript(): void
    {
        echo view('partials/gf-advanced-settings-custom-select-script.php');
    }

    public function addExternalOptionsSelectTooltip(array $tooltips)
    {
        $tooltips['form_field_external_options'] = "<strong>Externe opties</strong>Vul de keuzes van dit veld vanuit de ingestelde externe bron.";

        return $tooltips;
    }
}

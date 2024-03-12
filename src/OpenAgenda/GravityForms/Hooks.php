<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use Exception;
use function OWC\OpenAgenda\Foundation\resolve;
use function OWC\OpenAgenda\Foundation\view;
use OWC\OpenAgenda\Http\Endpoints\GetLocations;
use OWC\OpenAgenda\Http\Endpoints\GetTaxonomyTerms;

class Hooks
{
    private const ALLOWED_FIELD_TYPES = [
        'select',
        'multiselect',
        'checkbox',
        'radio',
    ];

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
        // Create settings on position 50 (right after Admin Label).
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

    public function populateCheckboxes(array $form)
    {
        foreach ($form['fields'] as &$field) {
            if (! in_array($field->type, self::ALLOWED_FIELD_TYPES)) {
                continue;
            }

            try {
                $choices = $this->prepareFieldOptions($field->field_populate_external_option);
            } catch (Exception $e) {

                continue;
            }

            $field->choices = $choices ? $choices : $field->choices;
        }

        return $form;
    }

    protected function prepareFieldOptions(string $option)
    {
        if (empty($option)) {
            return [];
        }

        list($type, $restBase) = explode('.', $option); // $option is something like: tax.doelgroep.

        if ('tax' === $type) {
            $options = $this->getFieldOptionsTax($restBase);
            $choices = $this->formatTaxOptions($options);
        } elseif ('post' === $type) {
            $options = $this->getFieldOptionsPost($restBase);
            $choices = $this->formatPostOptions($options);
        } else {
            $choices = [];
        }

        if (empty($choices)) {
            return [];
        }

        return $choices;
    }

    protected function getFieldOptionsTax(string $restBase): array
    {
        $options = (new GetTaxonomyTerms())->setRestBase($restBase)->request('GET');

        return ! empty($options) ? $options : [];
    }

    protected function getFieldOptionsPost(string $restBase): array
    {
        if ('locations' === $restBase) {
            $options = (new GetLocations())->request('GET');
            $options = $options['results'] ?? [];
        }

        return ! empty($options) ? $options : [];
    }

    protected function formatTaxOptions(array $options): array
    {
        if (empty($options)) {
            return [];
        }

        $choices = [];

        foreach ($options as $option) {
            $choices[] = [ 'text' => $option['name'], 'value' => $option['id'] ];
        }

        return $choices;
    }

    protected function formatPostOptions(array $options): array
    {
        if (empty($options)) {
            return [];
        }

        $choices = [];

        foreach ($options as $option) {
            $choices[] = [ 'text' => $option['title'], 'value' => $option['id'] ];
        }

        return $choices;
    }
}

<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use Exception;
use GF_Field;
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

        try {
            $taxonomies = (new \OWC\OpenAgenda\Http\Endpoints\GetEventTaxonomies())->request('GET');
        } catch (Exception $e) {
            $taxonomies = [];
        }

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

    public function populateExternalOptionsFields(array $form): array
    {
        foreach ($form['fields'] as &$field) {
            if (! in_array($field->type, self::ALLOWED_FIELD_TYPES) || empty($field->field_populate_external_option)) {
                continue;
            }

            try {
                $choices = $this->prepareFieldOptions($field->field_populate_external_option);

                if (empty($choices)) {
                    throw new Exception('Unable to prepare field options.');
                }
            } catch (Exception $e) {
                continue;
            }

            $field->choices = $choices;

            if ('checkbox' === $field->type) {
                $field->inputs = $this->formatCheckboxInputIDs($field);
            }
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

        return $choices;
    }

    protected function getFieldOptionsTax(string $restBase): array
    {
        $options = (new GetTaxonomyTerms())->setRestBase($restBase)->appendParametersToURL(['per_page' => 25, 'orderby' => 'id'])->request('GET');

        return ! empty($options) ? $options : [];
    }

    protected function getFieldOptionsPost(string $restBase): array
    {
        if ('locations' === $restBase) {
            $options = (new GetLocations())->request('GET');

            $options = $options['results'] ?? [];
            array_unshift($options, ['id' => '', 'title' => 'Selecteer een locatie']); // Prepend blank option.
        }

        return ! empty($options) ? $options : [];
    }

    protected function formatTaxOptions(array $options): array
    {
        $choices = [];

        foreach ($options as $option) {
            $choices[] = [ 'text' => $option['name'], 'value' => $option['id'] ];
        }

        return $choices;
    }

    protected function formatPostOptions(array $options): array
    {
        $choices = [];

        foreach ($options as $option) {
            $choices[] = [ 'text' => $option['title'], 'value' => $option['id'] ];
        }

        return $choices;
    }

    /**
     * Checkbox fields which are custom populated needs the input properties to be formatted correctly.
     * This is done based on the choices property of the checkbox field.
     */
    protected function formatCheckboxInputIDs(GF_Field $field): array
    {
        $start = 1;

        return array_map(function ($item) use ($field, &$start) {
            // Skipping index that are multiples of 10 (multiples of 10 create problems as the input IDs)
            if ($start % 10 === 0) {
                $start++;
            }

            $inputId = sprintf('%d.%d', $field->id, $start);
            $start++;

            return [
                'id' => $inputId,
                'label' => $item['text'],
            ];
        }, $field->choices);
    }
}

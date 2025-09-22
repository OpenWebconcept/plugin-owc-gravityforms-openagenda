<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms\Feed;

use Exception;
use GFFeedAddOn;
use OWC\OpenAgenda\GravityForms\Feed\Services\PeriodFieldService;
use OWC\OpenAgenda\Http\Endpoints\ExportEvent;
use OWC\OpenAgenda\Traits\ImageProcessingTrait;

class ProcesFeed
{
    use ImageProcessingTrait;

    protected GFFeedAddOn $GFFeedAddOn;
    protected array $feed;
    protected array $entry;
    protected array $form;
    protected array $settings;
    protected array $fieldMap;
    protected array $submissionData = [];

    public function __construct(GFFeedAddOn $GFFeedAddOn, array $feed, array $entry, array $form)
    {
        $this->GFFeedAddOn = $GFFeedAddOn;
        $this->feed = $feed;
        $this->entry = $entry;
        $this->form = $form;
    }

    /**
     * Prepare the plugin settings and mapping of fields.
     */
    public function prepare(): self
    {
        $this->settings = $this->GFFeedAddOn->get_plugin_settings() ?: [];
        $this->fieldMap = $this->GFFeedAddOn->get_field_map_fields($this->feed, 'mappedFields'); // Maybe use array_filter in the future. For now all the field are required otherwise the API will throw errors.

        return $this;
    }

    public function process(): void
    {
        try {
            $this->populateSubmissionData();
            $this->cleanupPossibleRedudantLocationData();
            $this->setDefaultEventLanguage();
            $this->exportToOpenAgenda();
        } catch (Exception $e) {
            $this->GFFeedAddOn->add_feed_error($e->getMessage(), $this->feed, $this->entry, $this->form);
        }
    }

    protected function populateSubmissionData(): void
    {
        foreach ($this->fieldMap as $name => $fieldID) {
            $value = $this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID);

            switch (true) {
                case $name === 'repeating_exclude_date':
                    $this->submissionData[$name] = $this->handleExcludedDates((string) $fieldID);

                    break;

                case $name === 'period-weekdays':
                    $this->handlePeriods((string) $fieldID);

                    break;

                case str_starts_with($name, 'tax_'):
                    $this->submissionData[$name] = wp_parse_list($value);

                    break;

                case $name === 'thumbnail':
                    $this->submissionData[$name] = $this->urlToBase64($value);

                    break;

                case in_array($name, ['media_files', 'images'], true):
                    $this->submissionData[$name] = array_map(function ($v) {
                        return $this->urlToBase64($v);
                    }, wp_parse_list($value));

                    break;

                case $name === 'specific_dates_and_times':
                    $this->handleDatesAndTimes((string) $fieldID);

                    break;
                case $name === 'dates_type':
                    $this->submissionData[$name] = 'specific'; // Force 'specific', requirements have changed.

                    break;
                default:
                    $this->submissionData[$name] = $value;
            }
        }
    }

    /**
     * Handle the excluded dates from the custom DateRepeater field.
     */
    protected function handleExcludedDates(string $fieldID): array
    {
        $values = rgar($this->entry, $fieldID);

        if (! is_array($values) || 1 > count($values)) {
            return [];
        }

        // Get the first row of the repeater field which holds the unique hardcoded ID.
        $firstRow = $values[0] ?? [];

        // The key used is the unique ID of the DateRepeater field, repeater fields are still in beta and expect a hardcoded ID.
        $firstKey = array_key_first($firstRow);

        if (is_null($firstKey)) {
            return [];
        }

        $dates = array_map(function ($row) use ($firstKey) {
            return $row[$firstKey] ?? null;
        }, $values);

        return array_filter($dates);
    }

    /**
     * Handle the periods from the custom PeriodField field.
     */
    protected function handlePeriods(string $fieldID): void
    {
        $values = rgar($this->entry, $fieldID);

        if (! is_array($values) || [] === $values) {
            return;
        }

        $allDates = [];

        foreach (array_values($values) as $period) {
            $allDates = array_merge($allDates, (new PeriodFieldService())->handlePeriod($period));
        }

        $this->submissionData['dates'] = array_merge($this->submissionData['dates'] ?? [], $allDates);
    }

    /**
     * Get the values from the DateTimeRepeaterField field and overwrite the array keys to match the OpenAgenda API requirements.
     */
    protected function handleDatesAndTimes(string $fieldID): void
    {
        $values = rgar($this->entry, $fieldID);

        if (! is_array($values) || 1 > count($values)) {
            return;
        }

        $withCorrectKeys = [];

        foreach (array_values($values) as $key => $value) {
            $ordered = array_values($value);

            [$date, $startTime, $endTime] = $ordered + [null, null, null];

            if (! is_string($date) || ! is_string($startTime) || ! is_string($endTime)) {
                continue;
            }

            if ('' === $date || '' === $startTime || '' === $endTime) {
                continue;
            }

            $withCorrectKeys[$key] = [
                'start_date' => $date,
                'end_date' => $date,
                'start_time' => $startTime,
                'end_time' => $endTime,
            ];
        }

        $this->submissionData['dates'] = array_merge($this->submissionData['dates'] ?? [], $withCorrectKeys);
    }

    /**
     * When an existing location is selected, unset possible redundant location data.
     */
    protected function cleanupPossibleRedudantLocationData(): void
    {
        if (empty($this->submissionData['location'])) {
            return;
        }

        unset($this->submissionData['location_description']);
        unset($this->submissionData['location_address']);
        unset($this->submissionData['location_zipcode']);
        unset($this->submissionData['location_city']);
    }

    /**
     * Set default language for events if none is provided.
     * The default language is set to 'nl_NL'.
     */
    protected function setDefaultEventLanguage(): void
    {
        if (! empty($this->submissionData['language'])) {
            return;
        }

        $this->submissionData['language'] = 'nl_NL';
    }

    protected function exportToOpenAgenda(): void
    {
        try {
            $result = (new ExportEvent())->request('POST', $this->submissionData);
        } catch (Exception $exception) {
            $this->GFFeedAddOn->add_feed_error($exception->getMessage(), $this->feed, $this->entry, $this->form);

            return;
        }

        $this->GFFeedAddOn->add_note($this->entry['id'], $result['message']);
    }
}

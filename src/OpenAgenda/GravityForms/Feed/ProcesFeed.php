<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms\Feed;

use Exception;
use GFFeedAddOn;
use OWC\OpenAgenda\Http\Endpoints\ExportEvent;
use OWC\OpenAgenda\Traits\ImageProcessingTrait;

class ProcesFeed
{
    use ImageProcessingTrait;

    private const VALID_WEEKDAYS = [
        'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday',
    ];

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
                case in_array($name, ['start_date', 'end_date', 'start_time', 'end_time'], true) && ! empty($value):
                    $this->submissionData['dates'][0][$name] = $value;

                    break;
                case $name === 'weekday_occurrence':
                    $this->submissionData['dates'][0][$name] = $value;

                    break;

                case $name === 'repeating_exclude_date':
                    // Handle custom DateRepeater GF field from the project which uses this plug-in.
                    $this->submissionData[$name] = $this->handleExcludeDates((string) $fieldID);

                    break;

                case $name === 'weekday_times':
                    // Handle custom WeekdayTimesRepeaterField GF field from the project which uses this plug-in.
                    $this->handleWeekdayTimes((string) $fieldID);

                    break;

                case in_array($name, ['weekdays', 'months'], true):
                    $this->submissionData['dates'][0][$name] = wp_parse_list($value);

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
                default:
                    $this->submissionData[$name] = $value;
            }
        }
    }

    /**
     * Handle the exclude dates from the DateRepeater field.
     */
    protected function handleExcludeDates(string $fieldID): array
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
     * Handle the weekday times from the WeekdayTimesField.
     * This field is used for overwriting the start and end times for specific weekdays.
     */
    protected function handleWeekdayTimes(string $fieldID): void
    {
        $values = rgar($this->entry, $fieldID);

        if (! is_array($values) || 1 > count($values)) {
            return;
        }

        foreach (array_values($values) as $value) {
            $ordered = array_values($value);

            [$weekday, $startTime, $endTime] = $ordered + [null, null, null];

            if (! is_string($weekday) || ! is_string($startTime) || ! is_string($endTime)) {
                continue;
            }

            if (! in_array($weekday, self::VALID_WEEKDAYS, true)) {
                continue;
            }

            if ('' === $weekday || '' === $startTime || '' === $endTime) {
                continue;
            }

            $this->submissionData['dates'][0]["start_time_$weekday"] = $startTime;
            $this->submissionData['dates'][0]["end_time_$weekday"] = $endTime;
        }
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

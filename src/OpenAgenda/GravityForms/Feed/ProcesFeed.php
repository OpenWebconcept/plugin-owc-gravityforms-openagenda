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

    protected GFFeedAddOn $GFFeedAddOn;
    protected array $feed;
    protected array $entry;
    protected array $form;
    protected array $settings;
    protected array $fieldMap;
    protected array $submissionData = [
        'price_type' => 'fixed',
        'type' => 'specific',
    ];

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

    public function process()
    {
        try {
            $this->populateSubmissionData();
            $this->cleanupPossibleRedudantLocationData();
            $this->exportToOpenAgenda();
        } catch(Exception $e) {
            $this->GFFeedAddOn->add_feed_error($e->getMessage(), $this->feed, $this->entry, $this->form);
        }
    }

    protected function populateSubmissionData(): void
    {
        foreach ($this->fieldMap as $name => $fieldID) {
            if (in_array($name, ['start_date','end_date', 'start_time', 'end_time'])) {
                $this->submissionData['dates'][0][$name] = $this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID);
            } elseif (str_starts_with($name, 'tax_')) {
                $this->submissionData[$name] = wp_parse_list($this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID));
            } elseif ($name === 'thumbnail') {
                $this->submissionData[$name] = $this->urlToBase64($this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID));
            } elseif (in_array($name, ['media_files', 'images'])) {
                $this->submissionData[$name] = array_map(function ($value) {
                    return $this->urlToBase64($value);
                }, wp_parse_list($this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID)));
            } else {
                $this->submissionData[$name] = $this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID);
            }
        }

        // Maybe use array_filter in the future. For now all the field are required otherwise the API will throw errors.
        $this->submissionData = $this->submissionData;
    }

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

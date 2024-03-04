<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms\Feed;

use Exception;
use GFFeedAddOn;
use OWC\OpenAgenda\Http\Endpoints\ExportEvent;

class ProcesFeed
{
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
        $this->fieldMap = array_filter($this->GFFeedAddOn->get_field_map_fields($this->feed, 'mappedFields'));

        return $this;
    }

    public function process()
    {
        $this->populateSubmissionData();
        $this->exportToOpenAgenda();
    }

    protected function populateSubmissionData(): void
    {
        foreach ($this->fieldMap as $name => $fieldID) {
            if (in_array($name, ['start_date','end_date', 'start_time', 'end_time'])) {
                $this->submissionData['dates'][0][$name] = $this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID);
            } elseif (in_array($name, ['tax_thema', 'tax_wijk'], true)) {
                $this->submissionData[$name] = wp_parse_list($this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID));
            } else {
                $this->submissionData[ $name ] = $this->GFFeedAddOn->get_field_value($this->form, $this->entry, $fieldID);
            }
        }

        $this->submissionData = array_filter($this->submissionData);
    }

    protected function exportToOpenAgenda(): void
    {
        try {
            $result = (new ExportEvent($this->settings))->request('POST', $this->submissionData);
        } catch(Exception $exception) {
            $this->GFFeedAddOn->add_feed_error($exception->getMessage(), $this->feed, $this->entry, $this->form);

            return;
        }

        $this->GFFeedAddOn->add_note($this->entry['id'], $result['message']);
    }
}

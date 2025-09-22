<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms\Feed;

use Exception;

class FeedSettings
{
    public static function pluginSettingsField(): array
    {
        return [
            [
                'title' => esc_html__('OpenAgenda', 'owc-gravityforms-openagenda'),
                'fields' => [
                    [
                        'name' => 'rest_api_base_url',
                        'label' => esc_html__('REST API url', 'owc-gravityforms-openagenda'),
                        'type' => 'text',
                        'input_type' => 'url',
                        'required' => true,
                    ],
                    [
                        'name' => 'rest_api_username',
                        'label' => esc_html__('Gebruikersnaam', 'owc-gravityforms-openagenda'),
                        'type' => 'text',
                        'required' => true,
                    ],
                    [
                        'name' => 'rest_api_password',
                        'label' => esc_html__('Wachtwoord', 'owc-gravityforms-openagenda'),
                        'type' => 'text',
                        'input_type' => 'password',
                        'required' => true,
                    ],
                ],
            ],
        ];
    }

    public static function feedSettingsField(): array
    {
        return [
            [
                'title' => esc_html__('OpenAgenda instellingen', 'owc-gravityforms-openagenda'),
                'fields' => [
                    [
                        'name' => 'enabled',
                        'type' => 'toggle',
                        'label' => __('Exporteer evenement naar OpenAgenda', 'owc-gravityforms-openagenda'),
                    ],
                    [
                        'name' => 'mappedFields',
                        'label' => esc_html__('API mapping', 'owc-gravityforms-openagenda'),
                        'type' => 'field_map',
                        'dependency' => [
                            'live' => true,
                            'fields' => [
                                [
                                    'field' => 'enabled',
                                ],
                            ],
                        ],
                        'field_map' => array_merge([
                            [
                                'name' => 'title',
                                'label' => esc_html__('Titel', 'owc-gravityforms-openagenda'),
                                'required' => false,
                                'field_type' => [ 'text' ],
                            ],
                            [
                                'name' => 'teaser',
                                'label' => esc_html__('Teaser', 'owc-gravityforms-openagenda'),
                                'field_type' => 'text',
                                'required' => false,
                            ],
                            [
                                'name' => 'description',
                                'label' => esc_html__('Beschrijving', 'owc-gravityforms-openagenda'),
                                'required' => false,
                                'field_type' => 'textarea',
                                'required' => false,
                            ],
                            [
                                'name' => 'recurring_description',
                                'label' => esc_html__('Terugkerend evenement', 'owc-gravityforms-openagenda'),
                                'required' => false,
                                'field_type' => ['text', 'textarea'],
                                'required' => false,
                            ],
                            [
                                'name' => 'organizer',
                                'label' => esc_html__('Organisator', 'owc-gravityforms-openagenda'),
                                'field_type' => 'text',
                                'required' => false,
                            ],
                            [
                                'name' => 'contact_person',
                                'label' => esc_html__('Contactpersoon', 'owc-gravityforms-openagenda'),
                                'field_type' => ['text', 'name'],
                                'required' => false,
                            ],
                            [
                                'name' => 'phone_number',
                                'label' => esc_html__('Telefoonnummer', 'owc-gravityforms-openagenda'),
                                'field_type' => 'phone',
                                'required' => false,
                            ],
                            [
                                'name' => 'email_address',
                                'label' => esc_html__('E-mail', 'owc-gravityforms-openagenda'),
                                'field_type' => 'email',
                                'required' => false,
                            ],
                            [
                                'name' => 'location',
                                'label' => esc_html__('Locatie', 'owc-gravityforms-openagenda'),
                                'field_type' => ['select', 'multiselect'],
                                'required' => false,
                            ],
                            [
                                'name' => 'language',
                                'label' => esc_html__('Taal', 'owc-gravityforms-openagenda'),
                                'field_type' => ['select'],
                                'required' => false,
                            ],
                            [
                                'name' => 'location_description',
                                'label' => esc_html__('Locatie omschrijving', 'owc-gravityforms-openagenda'),
                                'field_type' => ['text', 'address'],
                                'required' => false,
                            ],
                            [
                                'name' => 'location_address',
                                'label' => esc_html__('Adres', 'owc-gravityforms-openagenda'),
                                'field_type' => ['text', 'address'],
                                'required' => false,
                            ],
                            [
                                'name' => 'location_zipcode',
                                'label' => esc_html__('Postcode', 'owc-gravityforms-openagenda'),
                                'field_type' => ['text', 'address'],
                                'required' => false,
                            ],
                            [
                                'name' => 'location_city',
                                'label' => esc_html__('Stad', 'owc-gravityforms-openagenda'),
                                'field_type' => ['text', 'address'],
                                'required' => false,
                            ],
                            [
                                'name' => 'accessibility',
                                'label' => esc_html__('Toegankelijkheid', 'owc-gravityforms-openagenda'),
                                'field_type' => ['text', 'textarea'],
                                'required' => false,
                            ],
                            [
                                'name' => 'price_type',
                                'label' => esc_html__('Prijs soort', 'owc-gravityforms-openagenda'),
                                'field_type' => ['select', 'radio'],
                                'required' => false,
                            ],
                            [
                                'name' => 'fixed_price',
                                'label' => esc_html__('Prijs', 'owc-gravityforms-openagenda'),
                                'field_type' => 'number',
                                'required' => false,
                            ],
                            [
                                'name' => 'min_price',
                                'label' => esc_html__('Minimum prijs', 'owc-gravityforms-openagenda'),
                                'field_type' => 'number',
                                'required' => false,
                            ],
                            [
                                'name' => 'max_price',
                                'label' => esc_html__('Maximum prijs', 'owc-gravityforms-openagenda'),
                                'field_type' => 'number',
                                'required' => false,
                            ],
                            [
                                'name' => 'website_url',
                                'label' => esc_html__('Website evenement', 'owc-gravityforms-openagenda'),
                                'field_type' => 'website',
                                'required' => false,
                            ],
                            [
                                'name' => 'ticket_website_url',
                                'label' => esc_html__('Website tickets', 'owc-gravityforms-openagenda'),
                                'field_type' => 'website',
                                'required' => false,
                            ],
                            [
                                'name' => 'video_url',
                                'label' => esc_html__('Video URL', 'owc-gravityforms-openagenda'),
                                'field_type' => 'website',
                                'required' => false,
                            ],
                            [
                                'name' => 'dates_type',
                                'label' => esc_html__('Type datum', 'owc-gravityforms-openagenda'),
                                'field_type' => ['select', 'radio'],
                                'required' => false,
                            ],
                            [
                                'name' => 'specific_dates_and_times',
                                'label' => esc_html__('Specifieke data en tijden', 'owc-gravityforms-openagenda'),
                                'field_type' => 'owc-date-time-repeater',
                                'required' => false,
                            ],
                            // Fields for 'dates_types' => 'specific'
                            [
                                'name'        => 'dates_every_year',
                                'label'       => esc_html__('Jaarlijks herhalen?', 'owc-gravityforms-openagenda'),
                                'field_type'  => ['select', 'radio'],
                                'required'    => false,
                            ],
                            // End of 'specific' fields

                            // Fields for 'dates_types' => 'complex'
                            [
                                'name'        => 'period-weekdays',
                                'label'       => esc_html__('Weekdagen en tijden (periode)', 'owc-gravityforms-openagenda'),
                                'field_type'  => ['owc-period-repeater'],
                                'required'    => false,
                            ],
                            [
                                'name'        => 'dates_recurring_description',
                                'label'       => esc_html__('Terugkerende datum omschrijving', 'owc-gravityforms-openagenda'),
                                'field_type'  => ['text', 'textarea'],
                                'required'    => false,
                            ],
                            // End of 'complex' fields

                            // Fields common to all 'dates_types' values
                            [
                                'name'        => 'repeating_exclude_date',
                                'label'       => esc_html__('Uitsluiten van herhaling', 'owc-gravityforms-openagenda'),
                                'field_type'  => ['owc-date-repeater'],
                                'required'    => false,
                            ],
                            // End of 'dates_types' fields
                            [
                                'name' => 'media_files',
                                'label' => esc_html__('Bestanden', 'owc-gravityforms-openagenda'),
                                'field_type' => 'fileupload',
                                'required' => false,
                            ],
                            [
                                'name' => 'images',
                                'label' => esc_html__('Afbeeldingen', 'owc-gravityforms-openagenda'),
                                'field_type' => 'fileupload',
                                'required' => false,
                            ],
                            [
                                'name' => 'thumbnail',
                                'label' => esc_html__('Uitgelichte afbeelding', 'owc-gravityforms-openagenda'),
                                'field_type' => 'fileupload',
                                'required' => false,
                            ],
                        ], self::getEventTaxonomies()),
                    ],
                ],
            ],
        ];
    }

    /**
     * Retrieve event taxonomies from external source.
     */
    private static function getEventTaxonomies(): array
    {
        $eventTaxonomies = [];

        try {
            $taxonomies = (new \OWC\OpenAgenda\Http\Endpoints\GetEventTaxonomies())->request('GET');
        } catch (Exception $e) {
            return [];
        }

        foreach ($taxonomies as $taxonomy) {
            $eventTaxonomies[] = [
                'name' => sprintf('tax_%s', $taxonomy['rest_base']),
                'label' => sprintf('%s (Taxonomie)', $taxonomy['name']),
                'field_type' => ['select', 'multiselect', 'radio', 'checkbox'],
                'required' => false,
            ];
        }

        return $eventTaxonomies;
    }
}

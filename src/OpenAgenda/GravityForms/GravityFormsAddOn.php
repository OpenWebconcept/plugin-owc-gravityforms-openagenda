<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use GFFeedAddOn;

class GravityFormsAddon extends GFFeedAddOn
{
    protected $_version = OWC_GF_OPENAGENDA_VERSION;
    protected $_min_gravityforms_version = '2.6';
    protected $_slug = 'owc-openagenda';
    protected $_path = 'owc-gravityforms-openagenda/owc-gravityforms-openagenda.php';
    protected $_full_path = __FILE__;
    protected $_title = 'OpenAgenda integratie';
    protected $_short_title = 'OpenAgenda';
    protected $_multiple_feeds = false;

    /**
     * @var object|null If available, contains an instance of this class.
     */
    private static $_instance = null;

    /**
     * Returns an instance of this class, and stores it in the $_instance property.
     *
     * @return object $_instance An instance of this class.
     */
    public static function get_instance()
    {
        if (null == self::$_instance) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function get_menu_icon()
    {
        return 'gform-icon--date';
    }

    public function plugin_settings_fields()
    {
        //TODO: verplaatsen naar eigen class
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

    public function feed_settings_fields()
    {
        //TODO: verplaatsen naar eigen class
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
                        'field_map' => [
                            [
                                'name' => 'post_title',
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
                                'name' => 'location_title',
                                'label' => esc_html__('Locatie', 'owc-gravityforms-openagenda'),
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
                                'name' => 'event_website_url',
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
                                'name' => 'type',
                                'label' => esc_html__('Type', 'owc-gravityforms-openagenda'),
                                'field_type' => ['select', 'radio'],
                                'required' => false,
                            ],
                            [
                                'name' => 'start_date',
                                'label' => esc_html__('Startdatum', 'owc-gravityforms-openagenda'),
                                'field_type' => 'date',
                                'required' => false,
                            ],
                            [
                                'name' => 'end_date',
                                'label' => esc_html__('Einddatum', 'owc-gravityforms-openagenda'),
                                'field_type' => 'date',
                                'required' => false,
                            ],
                            [
                                'name' => 'start_time',
                                'label' => esc_html__('Starttijd', 'owc-gravityforms-openagenda'),
                                'field_type' => 'time',
                                'required' => false,
                            ],
                            [
                                'name' => 'end_time',
                                'label' => esc_html__('Eindtijd', 'owc-gravityforms-openagenda'),
                                'field_type' => 'time',
                                'required' => false,
                            ],
                            [
                                'name' => 'tax_thema',
                                'label' => esc_html__('Thema', 'owc-gravityforms-openagenda'),
                                'field_type' => ['multiselect', 'checkbox'],
                                'required' => false,
                            ],
                            [
                                'name' => 'tax_wijk',
                                'label' => esc_html__('Wijk', 'owc-gravityforms-openagenda'),
                                'field_type' => ['select', 'radio'],
                                'required' => false,
                            ],
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
                        ],
                    ],
                ],
            ],
        ];
    }

    public function process_feed($feed, $entry, $form)
    {
        if (! (bool) rgars($feed, 'meta/enabled')) {
            return;
        }


        $settings = $this->get_plugin_settings();
        $field_map = $this->get_field_map_fields($feed, 'mappedFields');
        $field_map = array_filter($field_map);

        // Default waardes
        $submission_data = [
            'price_type' => 'fixed',
            'type' => 'specific',
        ];


        foreach ($field_map as $name => $field_id) {
            if (in_array($name, ['start_date','end_date', 'start_time', 'end_time'])) {
                $submission_data['dates'][0][$name] = $this->get_field_value($form, $entry, $field_id);
            } elseif (in_array($name, ['tax_thema', 'tax_wijk'], true)) {
                $submission_data[$name] = wp_parse_list($this->get_field_value($form, $entry, $field_id));
            } else {
                $submission_data[ $name ] = $this->get_field_value($form, $entry, $field_id);
            }
        }
        $submission_data = array_filter($submission_data);

        $api_url = trailingslashit($settings['rest_api_base_url']) . 'owc/openagenda/v1/items';

        $response = wp_safe_remote_post(
            $api_url,
            [
                'headers' => [
                    'Authorization' => 'Basic ' . base64_encode($settings['rest_api_username'] . ':' . $settings['rest_api_password']),
                    'Content-Type' => 'application/json',
                ],
                'body' => wp_json_encode($submission_data),
            ]
        );

        if (is_wp_error($response)) {
            $this->add_feed_error($response->get_error_message(), $feed, $entry, $form);

            return;
        }

        $response_code = wp_remote_retrieve_response_code($response);

        if (! in_array($response_code, [ \WP_Http::OK, \WP_Http::CREATED], true)) {
            $this->add_feed_error(wp_remote_retrieve_response_message($response), $feed, $entry, $form);

            return;
        }

        $body = wp_remote_retrieve_body($response);
        $body = json_decode($body, true);

        $this->add_note($entry['id'], $body['message']);

        return;
    }

    public function can_create_feed()
    {
        $settings = $this->get_plugin_settings();

        return ! empty($settings['rest_api_base_url']) && ! empty($settings['rest_api_username']) && ! empty($settings['rest_api_password']);

    }

}

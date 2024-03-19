<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

use GFFeedAddOn;
use OWC\OpenAgenda\GravityForms\Feed\FeedSettings;
use OWC\OpenAgenda\GravityForms\Feed\ProcesFeed;

class GravityFormsAddOn extends GFFeedAddOn
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
        return FeedSettings::pluginSettingsField();
    }

    public function feed_settings_fields()
    {
        return FeedSettings::feedSettingsField();
    }

    public function process_feed($feed, $entry, $form)
    {
        if (! (bool) rgars($feed, 'meta/enabled')) {
            return;
        }

        (new ProcesFeed($this, $feed, $entry, $form))->prepare()->process();
    }

    public function can_create_feed()
    {
        $settings = $this->get_plugin_settings();

        return ! empty($settings['rest_api_base_url']) && ! empty($settings['rest_api_username']) && ! empty($settings['rest_api_password']);

    }

}

<?php

declare(strict_types=1);

/**
 * Plugin Name:       Yard | GravityForms OpenAgenda
 * Plugin URI:        https://www.openwebconcept.nl/
 * Description:       This plug-in enables users to submit events via an embedded Gravity Forms form. After submitting, an event is created and is also publicly visible using the REST API.
 * Version:           0.0.6
 * Author:            Yard | Digital Agency
 * Author URI:        https://www.yard.nl/
 * License:           EUPL-1.2
 * License URI:       https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * Text Domain:       owc-gravityforms-openagenda
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 */
if (! defined('WPINC')) {
    die;
}

/**
 * Some necessary constants.
 */
define('OWC_GF_OPENAGENDA_VERSION', '0.0.6');
define('OWC_GF_OPENAGENDA_DIR', basename(__DIR__));
define('OWC_GF_OPENAGENDA_ROOT_PATH', __DIR__);
define('OWC_GF_OPENAGENDA_PLUGIN_SLUG', 'owc-gravityforms-openagenda');

/**
 * Manual loaded file: the autoloader.
 */
require_once __DIR__ . '/autoloader.php';
$autoloader = new \OWC\OpenAgenda\Autoloader();

if (file_exists(__DIR__ . '/vendor/autoload.php')) {
    require_once(__DIR__ . '/vendor/autoload.php');
}

/**
 * Begin execution of the plugin
 *
 * This hook is called once any activated plugins have been loaded. Is generally used for immediate filter setup, or
 * plugin overrides. The plugins_loaded action hook fires early, and precedes the setup_theme, after_setup_theme, init
 * and wp_loaded action hooks.
 */
\add_action('plugins_loaded', function () {
    \OWC\OpenAgenda\Foundation\Plugin::getInstance(__DIR__)->boot();
}, 1);

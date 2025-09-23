<?php

declare(strict_types=1);

/**
 * Plugin Name:       Yard | GravityForms OpenAgenda
 * Plugin URI:        https://www.openwebconcept.nl/
 * Description:       This plug-in enables users to submit events via an embedded Gravity Forms form. After submitting, an event is created and is also publicly visible using the REST API.
 * Version:           0.0.9
 * Author:            Yard | Digital Agency
 * Author URI:        https://www.yard.nl/
 * License:           EUPL-1.2
 * License URI:       https://joinup.ec.europa.eu/collection/eupl/eupl-text-eupl-12
 * Text Domain:       owc-gravityforms-openagenda
 * Domain Path:       /languages
 * Requires Plugins:  gravityforms
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
define('OWC_GF_OPENAGENDA_VERSION', '0.0.9');
define('OWC_GF_OPENAGENDA_DIR', basename(__DIR__));
define('OWC_GF_OPENAGENDA_ROOT_PATH', __DIR__);
define('OWC_GF_OPENAGENDA_PLUGIN_SLUG', 'owc-gravityforms-openagenda');

/**
 * Not all the members of the OpenWebconcept are using composer in the root of their project.
 * Therefore they are required to run a composer install inside this plugin directory.
 * In this case the composer autoload file needs to be required.
 */
$composerAutoload = __DIR__ . '/vendor/autoload.php';

if (file_exists($composerAutoload)) {
    require_once $composerAutoload;
} else {
    require_once __DIR__ . '/autoloader.php';
    $autoloader = new \OWC\OpenAgenda\Autoloader();
}

/**
 * Begin execution of the plugin
 *
 * This hook is called once any activated plugins have been loaded. Is generally used for immediate filter setup, or
 * plugin overrides. The plugins_loaded action hook fires early, and precedes the setup_theme, after_setup_theme, init
 * and wp_loaded action hooks.
 */
add_action('plugins_loaded', function () {
    $plugin = \OWC\OpenAgenda\Foundation\Plugin::getInstance(__DIR__);

    add_action('after_setup_theme', function () use ($plugin) {
        $plugin->boot();
    });
}, 10);

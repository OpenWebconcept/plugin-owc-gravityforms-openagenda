<?php

/**
 * Plugin Name:       Yard | GravityForms OpenAgenda
 * Plugin URI:        https://www.openwebconcept.nl/
 * Description:       TODO:
 * Version:           0.0.1
 * Author:            Yard | Digital Agency
 * Author URI:        https://www.yard.nl/
 * License:           GPL-3.0
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       owc-gravityforms-openagenda
 * Domain Path:       /languages
 */

/**
 * If this file is called directly, abort.
 */
if (! defined('WPINC')) {
    die;
}

define('OWC_GF_OPENAGENDA_VERSION', '0.0.1');
define('OWC_GF_OPENAGENDA_DIR', basename(__DIR__));
define('OWC_GF_OPENAGENDA_ROOT_PATH', __DIR__);
define('OWC_GF_OPENAGENDA_PLUGIN_SLUG', 'owc-gravityforms-openagenda');


require_once __DIR__ . '/autoloader.php';
$autoloader = new \OWC\OpenAgenda\Autoloader();

\add_action('plugins_loaded', function () {
    $plugin = (new \OWC\OpenAgenda\Foundation\Plugin(__DIR__))->boot();
}, 1);

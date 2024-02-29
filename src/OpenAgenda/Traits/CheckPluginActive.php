<?php

namespace OWC\OpenAgenda\Traits;

trait CheckPluginActive
{
    /**
     * @param string $file // example: 'pdc-internal-products/pdc-internal-products.php'
     */
    public function isPluginActive(string $file = ''): bool
    {
        if (empty($file)) {
            return false;
        }

        if (! function_exists('is_plugin_active')) {
            include_once ABSPATH . 'wp-admin/includes/plugin.php';
        }

        return \is_plugin_active($file);
    }
}

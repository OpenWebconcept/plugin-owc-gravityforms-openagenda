<?php

declare(strict_types=1);

use DI\Container;

return [
    'gf.addon.openagenda_api_base_url' => function (Container $container) {
        return $container->make('gf.setting', ['rest_api_base_url']);
    },
    'gf.addon.openagenda_api_username' => function (Container $container) {
        return $container->make('gf.setting', ['rest_api_username']);
    },
    'gf.addon.openagenda_api_password' => function (Container $container) {
        return $container->make('gf.setting', ['rest_api_password']);
    },

    /**
     * Utilize with $container->make('gf.setting', ['setting-name-here']);
     */
    'gf.setting' => function (Container $container, string $type, string $name) {
        return OWC\OpenAgenda\GravityForms\GravityFormsSettings::make()->get($name);
    },
];

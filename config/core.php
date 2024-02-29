<?php

return [

    /**
     * Service Providers.
     */
    'providers' => [
        /** Global providers */
        \OWC\OpenAgenda\GravityForms\GravityFormsServiceProvider::class,

        /** Providers specific to the admin */
        'admin' => []
    ],

    /**
     * Dependencies upon which the plugin relies.
     *
     * Required: type, label
     * Optional: message
     *
     * Type: plugin
     * - Required: file
     * - Optional: version
     *
     * Type: class
     * - Required: name
     */
    'dependencies' => [
        [
            'type' => 'plugin',
            'label' => 'Gravity Forms',
            'version' => '2.7.0',
            'file' => 'gravityforms/gravityforms.php'
        ]
    ]
];

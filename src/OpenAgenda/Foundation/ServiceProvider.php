<?php

namespace OWC\OpenAgenda\Foundation;

abstract class ServiceProvider
{
    protected Plugin $plugin;

    public function __construct(Plugin $plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * Register the service provider.
     */
    abstract public function register();
}

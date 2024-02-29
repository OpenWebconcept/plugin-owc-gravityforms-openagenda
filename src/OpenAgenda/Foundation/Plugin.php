<?php

namespace OWC\OpenAgenda\Foundation;

use Exception;

class Plugin
{
    public const NAME = \OWC_GF_OPENAGENDA_PLUGIN_SLUG;
    public const VERSION = \OWC_GF_OPENAGENDA_VERSION;

    public Config $config;
    public Loader $loader;
    protected static Plugin $instance;

    protected string $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        load_plugin_textdomain($this->getName(), false, $this->getName() . '/languages/');

        $this->loader = new Loader();
        $this->config = new Config($this->rootPath . '/config');
        $this->config->setProtectedNodes(['core']);
        $this->config->boot();
    }

    public function boot(): bool
    {
        $dependencyChecker = new DependencyChecker($this->config->get('core.dependencies'));

        if ($dependencyChecker->failed()) {
            $dependencyChecker->notify();
            \deactivate_plugins(plugin_basename($this->rootPath . '/' . $this->getName() . '.php'));

            return false;
        }

        // Set up service providers
        $this->callServiceProviders('register');

        if (\is_admin()) {
            $this->callServiceProviders('register', 'admin');
            $this->callServiceProviders('boot', 'admin');
        }

        $this->callServiceProviders('boot');

        // Register the Hook loader.
        $this->loader->register();

        return true;
    }

    /**
     * Call method on service providers.
     *
     * @throws Exception
     */
    public function callServiceProviders(string $method, string $key = ''): void
    {
        $offset = $key ? "core.providers.{$key}" : 'core.providers';
        $services = $this->config->get($offset);

        foreach ($services as $service) {
            if (is_array($service)) {
                continue;
            }

            $service = new $service($this);

            if (! $service instanceof ServiceProvider) {
                throw new Exception('Provider must be an instance of ServiceProvider.');
            }

            if (method_exists($service, $method)) {
                $service->$method();
            }
        }
    }

    /**
     * Get the name of the plugin.
     */
    public function getName(): string
    {
        return static::NAME;
    }

    /**
     * Get the version of the plugin.
     */
    public function getVersion(): string
    {
        return static::VERSION;
    }

    /**
     * Return root path of plugin.
     */
    public function getRootPath(): string
    {
        return $this->rootPath;
    }

    /**
     * Return root url of plugin.
     */
    public function getPluginUrl(): string
    {
        return \plugins_url($this->getName());
    }
}

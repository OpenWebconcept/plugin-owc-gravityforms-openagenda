<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Foundation;

use CMDISP\MonologMicrosoftTeams\TeamsFormatter;
use DI\Container;
use DI\ContainerBuilder;
use Exception;

class Plugin
{
    public const NAME = \OWC_GF_OPENAGENDA_PLUGIN_SLUG;
    public const VERSION = \OWC_GF_OPENAGENDA_VERSION;

    public Config $config;
    public Loader $loader;
    protected Container $container;
    protected static $instance;

    protected string $rootPath;

    public function __construct(string $rootPath)
    {
        $this->rootPath = $rootPath;
        load_plugin_textdomain($this->getName(), false, $this->getName() . '/languages/');

        require_once __DIR__ . '/Helpers.php';
        $this->buildContainer();
    }

    /**
     * Return the Plugin instance
     */
    public static function getInstance($rootPath = ''): self
    {
        if (null == static::$instance) {
            static::$instance = new static($rootPath);
        }

        return static::$instance;
    }

    protected function buildContainer(): void
    {
        $builder = new ContainerBuilder();
        $builder->addDefinitions([
            'app' => $this,
            self::class => $this,
            'config' => function () {
                return new Config($this->rootPath . '/config');
            },
            'loader' => Loader::getInstance(),
            'teams' => function () {
                $logger = new \Monolog\Logger('microsoft-teams-logger');

                if (true === filter_var($_ENV['MS_TEAMS_DISABLE_LOGGING_OPEN_AGENDA'] ?? true, FILTER_VALIDATE_BOOLEAN)) {
                    return $logger->pushHandler(new \Monolog\Handler\NullHandler());
                }

                return $logger->pushHandler(new \CMDISP\MonologMicrosoftTeams\TeamsLogHandler(
                    $_ENV['MS_TEAMS_WEBHOOK_URL'] ?? '',
                    \Monolog\Logger::INFO,
                    true,
                    new TeamsFormatter()
                ));
            },

        ]);
        $builder->addDefinitions($this->rootPath . '/config/container.php');
        $this->container = $builder->build();
    }

    public function getContainer(): Container
    {
        return $this->container;
    }

    public function boot(): bool
    {
        $this->config = resolve('config');
        $this->config->boot();

        $this->loader = resolve('loader');

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

        $this->config->setProtectedNodes(['core']);
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
        $services = $this->config->get($offset, []);

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

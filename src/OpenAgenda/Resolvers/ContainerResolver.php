<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Resolvers;

use DI\Container;
use OWC\OpenAgenda\Foundation\Plugin;

class ContainerResolver
{
    protected Container $container;

    final private function __construct()
    {
        $this->container = Plugin::getInstance()->getContainer();
    }

    public static function make(): self
    {
        return new static();
    }

    public function get(string $key)
    {
        return $this->container->get($key) ?? null;
    }
}

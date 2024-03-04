<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Foundation;

function app(): Plugin
{
    return resolve('app');
}

function make(string $name, $container)
{
    return Plugin::getInstance()->getContainer()->set($name, $container);
}

function resolve($container, $arguments = [])
{
    return Plugin::getInstance()->getContainer()->get($container, $arguments);
}

/**
 * Get a config entry.
 */
function config(string $setting = '', $default = '')
{
    return resolve('config')->get($setting, $default);
}

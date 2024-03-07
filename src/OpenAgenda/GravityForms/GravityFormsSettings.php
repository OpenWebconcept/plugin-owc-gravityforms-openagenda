<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\GravityForms;

class GravityFormsSettings
{
    protected string $name = 'gravityformsaddon_owc-openagenda_settings';
    protected array $options = [];

    final private function __construct()
    {
        $this->options = get_option($this->name, []);
    }

    /**
     * Static constructor.
     */
    public static function make(): self
    {
        return new static();
    }

    /**
     * Get the value from the database.
     */
    public function get(string $key): string
    {
        return $this->options[$key] ?? '';
    }
}

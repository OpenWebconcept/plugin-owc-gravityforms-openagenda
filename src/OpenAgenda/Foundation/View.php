<?php

declare(strict_types=1);

namespace OWC\OpenAgenda\Foundation;

class View
{
    protected string $templateDirectory = OWC_GF_OPENAGENDA_ROOT_PATH . '/resources/views/';
    protected array $vars = [];
    protected array $bindings = []; // Associative array of variables that will be accessible from the template.

    public function __construct($templateDirectory = null)
    {
        if (null !== $templateDirectory) {
            // Check here whether this directory really exists
            $this->templateDirectory = $templateDirectory;
        }
    }

    public function exists(string $templateFile = ''): bool
    {
        return is_file($this->templateDirectory . $templateFile);
    }

    /**
     * Render the view
     */
    public function render(string $templateFile = '', array $vars = []): string
    {
        if (! is_file($this->templateDirectory . $templateFile)) {
            return '';
        }

        $this->bindAll($vars);
        ob_start();
        include($this->templateDirectory . $templateFile);
        $data = trim(ob_get_clean());

        return $this->parseTemplate($data, $this->bindings);
    }

    /**
     * Search and replace of variables.
     * Searching for {{VARIABLE}}.
     */
    protected function parseTemplate(string $template, array $bindings = []): string
    {
        return preg_replace_callback(
            '#{{\s?(.*?)\s?}}#',
            function ($match) use ($bindings) {
                $match[1] = trim($match[1], '');

                return $bindings[$match[1]] ?? '';
            },
            $template
        );
    }

    /**
     * Bind a single variable that will be accessible when the view is rendered.
     */
    public function bind(string $parameter, $value)
    {
        $this->bindings[$parameter] = $value;
    }

    /**
     * Bind multiple parameters at once.
     *
     * @see View:bind()
     *
     * @param array $bindings
     */
    public function bindAll(array $bindings)
    {
        foreach ($bindings as $parameter => $value) {
            $this->bind($parameter, $value);
        }
    }

    public function __set($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function __get($name)
    {
        return $this->vars[$name];
    }
}

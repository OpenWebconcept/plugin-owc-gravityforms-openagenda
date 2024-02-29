<?php

namespace OWC\OpenAgenda\Foundation;

/**
 * Provider which handles the hooks in the WordPress ecosystem.
 */
class Loader
{
    /**
     * The array of actions registered with WordPress.
     */
    protected array $actions = [];

    /**
     * The array of filters registered with WordPress.
     */
    protected array $filters = [];

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @since    2.0.0
     *
     * @param    string $hook          The name of the WordPress action that is being registered.
     * @param    object $component     A reference to the instance of the object on which the action is defined.
     * @param    string $callback      The name of the function definition on the $component.
     * @param    int    $priority      Optional. he priority at which the function should be fired. Default is 10.
     * @param    int    $acceptedArgs  Optional. The number of arguments that should be passed to the $callback.
     *                                 Default is 1.
     */
    public function addAction($hook, $component, $callback, $priority = 10, $acceptedArgs = 1): void
    {
        $this->actions = $this->add($this->actions, $hook, $component, $callback, $priority, $acceptedArgs);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @since    2.0.0
     *
     * @param    string $hook          The name of the WordPress filter that is being registered.
     * @param    object $component     A reference to the instance of the object on which the filter is defined.
     * @param    string $callback      The name of the function definition on the $component.
     * @param    int    $priority      Optional. he priority at which the function should be fired. Default is 10.
     * @param    int    $acceptedArgs  Optional. The number of arguments that should be passed to the $callback.
     *                                 Default is 1
     */
    public function addFilter($hook, $component, $callback, $priority = 10, $acceptedArgs = 1): void
    {
        $this->filters = $this->add($this->filters, $hook, $component, $callback, $priority, $acceptedArgs);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @since    2.0.0
     *
     * @param    array  $hooks        The collection of hooks that is being registered (that is, actions or filters).
     * @param    string $hook         The name of the WordPress filter that is being registered.
     * @param    object $component    A reference to the instance of the object on which the filter is defined.
     * @param    string $callback     The name of the function definition on the $component.
     * @param    int    $priority     The priority at which the function should be fired.
     * @param    int    $acceptedArgs The number of arguments that should be passed to the $callback.
     */
    protected function add($hooks, $hook, $component, $callback, $priority, $acceptedArgs): array
    {
        $hooks[] = [
            'hook' => $hook,
            'component' => $component,
            'callback' => $callback,
            'priority' => $priority,
            'accepted_args' => $acceptedArgs
        ];

        return $hooks;
    }

    /**
     * Register the filters and actions with WordPress.
     */
    public function register(): void
    {
        foreach ($this->filters as $hook) {
            \add_filter(
                $hook['hook'],
                [ $hook['component'], $hook['callback'] ],
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach ($this->actions as $hook) {
            \add_action(
                $hook['hook'],
                [ $hook['component'], $hook['callback'] ],
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }
}

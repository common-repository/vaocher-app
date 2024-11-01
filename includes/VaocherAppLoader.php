<?php

class VaocherAppLoader
{
    /**
     * The array of actions registered with WordPress.
     *
     * @var array
     */
    protected $actions = [];

    /**
     * The array of filters registered with WordPress.
     *
     * @var array
     */
    protected $filters = [];

    /**
     * Register the filters and actions with WordPress.
     */
    public function boot()
    {
        foreach ($this->filters as $hook) {
            add_filter(
                $hook['hook'],
                $hook['callable'],
                $hook['priority'],
                $hook['accepted_args']
            );
        }

        foreach ($this->actions as $hook) {
            add_action(
                $hook['hook'],
                $hook['callable'],
                $hook['priority'],
                $hook['accepted_args']
            );
        }
    }

    /**
     * Add a new action to the collection to be registered with WordPress.
     *
     * @param  string  $hook  The name of the WordPress action that is being registered.
     * @param  string|callable|array  $callable  A callable component to trigger.
     * @param  int  $priority  Optional. The priority at which the function should be fired. Default is 10.
     * @param  int  $accepted_args  Optional. The number of arguments that should be passed to the $callback. Default is 1.
     */
    public function addAction($hook, $callable, $priority = 10, $accepted_args = 1)
    {
        $this->actions = $this->add($this->actions, $hook, $callable, $priority, $accepted_args);
    }

    /**
     * Add a new filter to the collection to be registered with WordPress.
     *
     * @param  string  $hook  The name of the WordPress filter that is being registered.
     * @param  string|callable|array  $callable  A callable component to trigger.
     * @param  int  $priority  Optional. The priority at which the function should be fired. Default is 10.
     * @param  int  $accepted_args  Optional. The number of arguments that should be passed to the $callback. Default is 1
     */
    public function addFilter($hook, $callable, $priority = 10, $accepted_args = 1)
    {
        $this->filters = $this->add($this->filters, $hook, $callable, $priority, $accepted_args);
    }

    /**
     * A utility function that is used to register the actions and hooks into a single
     * collection.
     *
     * @param  array  $hooks  The collection of hooks that is being registered (that is, actions or filters).
     * @param  string  $hook  The name of the WordPress filter that is being registered.
     * @param  string|callable|array  $callable  A callable component to trigger.
     * is defined.
     * @param  int  $priority  The priority at which the function should be fired.
     * @param  int  $accepted_args  The number of arguments that should be passed to the $callback.
     * @return   array                                  The collection of actions and filters registered with WordPress.
     */
    private function add($hooks, $hook, $callable, $priority, $accepted_args)
    {
        $hooks[] = [
            'hook' => $hook,
            'callable' => $callable,
            'priority' => $priority,
            'accepted_args' => $accepted_args,
        ];

        return $hooks;
    }
}
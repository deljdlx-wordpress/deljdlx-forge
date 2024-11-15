<?php
namespace Deljdlx\WPForge;

use Illuminate\Container\Container as LaravelContainer;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;

class Container extends LaravelContainer
{
    /**
     * The Laravel framework version.
     *
     * @var string
     */
    const VERSION = '9.48.0';

    /**
     * The array of terminating callbacks.
     *
     * @var callable[]
     */
    protected $terminatingCallbacks = [];

    /**
     * Create a new Illuminate application instance.
     *
     * @return void
     */
    public function __construct()
    {
        static::setInstance($this);
        $this->instance('app', $this);


        foreach ([
            'view' => [\Illuminate\View\Factory::class, \Illuminate\Contracts\View\Factory::class],
        ] as $key => $aliases) {
            foreach ($aliases as $alias) {
                $this->alias($key, $alias);
            }
        }

        $this->bindIf('files', function () {
            return new Filesystem;
        }, true);

        $this->bindIf('events', function () {
            return new Dispatcher();
        }, true);

    }

    /**
     * Get the version number of the application.
     *
     * @return string
     */
    public function version()
    {
        return static::VERSION;
    }

    /**
     * Register a terminating callback with the application.
     *
     * @param  callable|string  $callback
     * @return $this
     */
    public function terminating($callback)
    {
        $this->terminatingCallbacks[] = $callback;

        return $this;
    }
}

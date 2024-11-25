<?php
namespace Deljdlx\WPForge;

use Illuminate\Config\Repository;
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

    public function bindOrMerge(string $name, $callback) {

        if($this->has($name)) {

            $config = $this->get($name);
            if($config instanceof Repository) {
                $newConfig = $callback();
                // merge recusively $newConfig  with $config
                foreach($newConfig->all() as $key => $value) {
                    if(is_array($value)) {
                        $config->set($key, array_merge_recursive($config->get($key, []), $value));
                    } else {
                        $config->set($key, $value);
                    }
                }

                $newCallback = function() use ($config) {
                    return $config;
                };
                return $this->bind($name, $newCallback);
            }
        }

        return $this->bind($name, $callback);
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

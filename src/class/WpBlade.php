<?php
namespace Deljdlx\WPForge;

use Illuminate\Config\Repository;
use Illuminate\Contracts\View\View;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Facade;
use Illuminate\View\ViewServiceProvider;

class WpBlade
{

    public static $instance;

    public $factory;


    private $compiler;

    private Container $container;


    public static function getInstance(Container $container)
    {
        if(!static::$instance) {
            static::$instance = new static($container);
        }
        return static::$instance;
    }


    public function getCompiler()
    {
        return $this->compiler;
    }

    public function compileString($template)
    {
        return $this->compiler->compileString($template);
    }


    public function __construct(Container $container)
    {
        $this->container = $container;

        // Facade::setFacadeApplication($this->container);
        (new ViewServiceProvider($this->container))->register();

        $this->factory = $this->container->get('view');
        $this->compiler = $this->container->get('blade.compiler');
    }


    public function make($view, $data = [], $mergeData = []): View
    {
        $this->factory->getFinder()->setPaths([]);
        foreach($this->container->config->get('view')['paths'] as $path) {
            $this->factory->getFinder()->addLocation($path);
        }

        return $this->factory->make($view, $data, $mergeData);


    }

    public function directive(string $name, $callback)
    {
        return $this->compiler->directive($name, $callback);
    }

    public function component(string $name, string $className)
    {
        $this->compiler->component($name, $className);
    }

}


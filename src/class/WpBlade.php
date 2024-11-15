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

    public $factory;


    private $compiler;

    private Container $container;

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
        return $this->factory->make($view, $data, $mergeData);
    }

    public function directive(string $name, $callback)
    {
        return $this->compiler->directive($name, $callback);
        // return Blade::directive($name, $callback);
    }

    public function component(string $name, string $className)
    {
        return $this->compiler->component($name, $className);
        // return Blade::component($name, $className);
    }

    /*
    public function __call(string $method, array $params)
    {
        return call_user_func_array([$this->factory, $method], $params);
    }
    */
}


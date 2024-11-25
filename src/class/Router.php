<?php

namespace Deljdlx\WPForge;

use Illuminate\Http\Request;

class Router
{

    public static $instance;

    private $routes = [];
    private string $rootPath;
    private static ?Request $request = null;

    public static function getInstance()
    {
        if(!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }


    public static function getRequest(): Request
    {
        if(!static::$request) {
            static::$request = Request::createFromGlobals();
        }
        return static::$request;
    }

    public function __construct($rootPath = '')
    {
        $this->rootPath = $rootPath;
    }

    public function setBaseUri($rootPath)
    {
        $this->rootPath = $rootPath;
    }

    public function get($path, $callback)
    {
        $this->addRoute('GET', $path, $callback);
    }

    public function post($path, $callback)
    {
        $this->addRoute('POST', $path, $callback);
    }

    public function addRoute($methods, $path, $callback, string $name = null)
    {
        if (!is_array($methods)) {
            $methods = [$methods];
        }

        if(!$name) {
            $name = $path;
        }

        $this->routes[$name] = [
            'methods' => $methods,
            'path' => $path,
            'callback' => $callback,
        ];

    }

    public function route()
    {
        $uri = $_SERVER['REQUEST_URI'];
        $method = $_SERVER['REQUEST_METHOD'];

        foreach ($this->routes as $route) {

            foreach($route['methods'] as $routeMethod) {
                if($method == $routeMethod && preg_match('`'.$route['path'].'`', $uri)) {
                    return $route['callback'](static::getRequest());
                }
            }
        }

        return false;
    }

    // JDLX_TODO handle parameters
    public function getUrlByRouteName(string $routeName, $parameters = [])
    {
        if(!isset($this->routes[$routeName])) {
            return '';
        }
        $path = $this->routes[$routeName]['path'];

        return $this->rootPath . $path;
    }
}




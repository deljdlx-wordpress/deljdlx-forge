<?php

use Deljdlx\WPForge\Application;
use Deljdlx\WPForge\Plugin;
use Illuminate\Container\Container;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Auth\Factory as AuthFactory;
use Illuminate\Contracts\Broadcasting\Factory as BroadcastFactory;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Cookie\Factory as CookieFactory;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Contracts\Support\Responsable;
use Illuminate\Contracts\Validation\Factory as ValidationFactory;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\Foundation\Bus\PendingClosureDispatch;
use Illuminate\Foundation\Bus\PendingDispatch;
use Illuminate\Foundation\Mix;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Queue\CallQueuedClosure;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\HtmlString;
use Symfony\Component\HttpFoundation\Response;

if(!function_exists('rglob')) {
    function rglob(string $path, string $pattern = '*') {
        $files = [];

        $list = glob(rtrim($path, '/') . '/*');

        foreach ($list as $file) {
            if($file === $path || $file == '.' || $file == '..') {
                continue;
            }

            if (is_dir($file)) {
                $files = array_merge($files, rglob($file, $pattern));
            } else {
                if (fnmatch($pattern, $file)) {
                    $files[] = $file;
                }
            }
        }
        return $files;
    }
}


if (!function_exists('app')) {
    /**
     * Get the available container instance.
     *
     * @param  string|null  $abstract
     * @param  array  $parameters
     * @return mixed|\Illuminate\Contracts\Foundation\Application
     */
    function app($abstract = null, array $parameters = [])
    {

        // if (is_null($abstract)) {
        //     return Container::getInstance();
        // }
        // return Container::getInstance()->make($abstract, $parameters);


        if (is_null($abstract)) {
            return Application::getInstance();
        }
        return Application::getInstance()->make($abstract, $parameters);
    }
}


if (!function_exists('view')) {
    /**
     * Get the evaluated view contents for the given view.
     *
     * @param  string|null  $view
     * @param  \Illuminate\Contracts\Support\Arrayable|array  $data
     * @param  array  $mergeData
     * @return \Illuminate\Contracts\View\View|\Illuminate\Contracts\View\Factory
     */
    function view($view = null, $data = [], $mergeData = [])
    {
        $factory = app(ViewFactory::class);


        if (func_num_args() === 0) {
            return $factory;
        }

        return $factory->make($view, $data, $mergeData);
    }
}

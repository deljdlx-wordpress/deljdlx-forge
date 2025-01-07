<?php
namespace Deljdlx\WPForge\Controllers;

use Deljdlx\WPForge\Container;
use Deljdlx\WPForge\Router;
use Deljdlx\WPForge\Theme\Theme;
use Deljdlx\WPForge\View;
use Illuminate\Http\Request;

class BaseController
{

    public static $prependJs = [];
    public static $appendJs = [];

    public static $prependCss = [];
    public static $appendCss = [];

    protected Container $container;
    protected View $view;
    protected Theme $theme;

    protected ?Request $request = null;

    public function __construct(Container $container)
    {
        $this->container = $container;
        $this->view = $container->get(View::class);
        $this->theme = $container->get(Theme::class);
    }

    public function addJs(array|string $js, $prepend = false)
    {
        $this->container->get(Theme::class)->addJs($js, $prepend);
    }

    public function addcss(array|string $js, $prepend = false)
    {
        $this->container->get(Theme::class)->addCss($js, $prepend);
    }

    public function getCurrentUserId()
    {
        return get_current_user_id();
    }

    public function getRequest(): Request
    {
        if(!$this->request) {
            $this->request = Router::getRequest();
        }

        return $this->request;
    }


    public function renderTemplate($templateName, $variables = [])
    {

        // get all parent classes
        $classes = [];
        $class = get_class($this);

        $prependJs = [];
        $appendJs = [];

        $prependCss = [];
        $appendCss = [];

        while($class) {
            $classes[] = $class;
            $class = get_parent_class($class);

            if($class) {
                $prependJs = array_merge($prependJs, $class::$prependJs);
                $appendJs = array_merge($appendJs, $class::$appendJs);
                $prependCss = array_merge($prependCss, $class::$prependCss);
                $appendCss = array_merge($appendCss, $class::$appendCss);
            }
        }

        $prependJs = array_merge($prependJs, static::$prependJs);
        $appendJs = array_merge($appendJs, static::$appendJs);
        $prependCss = array_merge($prependCss, static::$prependCss);
        $appendCss = array_merge($appendCss, static::$appendCss);

        $this->addJs($prependJs, true);
        $this->addJs($appendJs, false);
        $this->addCss($prependCss, true);
        $this->addCss($appendCss, false);

        // $this->addJs(static::$prependJs, true);
        // $this->addJs(static::$appendJs, false);
        // $this->addCss(static::$prependCss, true);
        // $this->addCss(static::$appendCss, false);

        return $this->view->render($templateName, $variables);
    }

}


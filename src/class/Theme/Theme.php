<?php

namespace Deljdlx\WPForge\Theme;

use Deljdlx\WPForge\Container;
use Deljdlx\WPForge\Router;
use Deljdlx\WPForge\Session;
use Deljdlx\WPForge\View;
use Illuminate\Config\Repository;

class Theme
{
    public static $instance;

    public readonly View $view;
    public readonly Loop $loop;
    public readonly Model $model;
    public readonly Menu $menu;
    public readonly Customizer $customizer;
    public readonly Admin $admin;
    public readonly Sidebar $sidebar;
    public readonly User $user;
    public readonly Session $session;
    public readonly Router $router;


    private string $name;
    private array $css = [];
    private array $js = [];

    private $mandatoryJs = [];
    private $mandatoryCss = [];

    private array $adminCss = [];
    private array $adminJs = [];

    private array  $supports = [];

    private string $id;



    public static function getInstance(Container $container)
    {
        if(!static::$instance) {
            static::$instance = new static($container);
        }
        return static::$instance;
    }



    public function __construct(Container $pluginContainer)
    {

        $this->id = uniqid();

        $this->view = $pluginContainer->get(View::class);
        $this->loop = new Loop();
        $this->model = new Model();
        $this->menu = new Menu();
        $this->customizer = new Customizer();
        $this->admin = new Admin();
        $this->user = new User();
        $this->session = new Session();

        $this->sidebar = new Sidebar();
        $this->router = new Router();

        $this->registerHooks();

    }

    public function addCsrfToken() {
        if (!isset($_SESSION['csrf_token'])) {
           $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
        }
    }

    public function getCsrfField()
    {
        $this->addCsrfToken();
        return '<input type="hidden" name="csrf_token" value="' . $_SESSION['csrf_token'] . '">';
    }

    public function validateCsrf_token() {
        if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
           return false;
        }

        return true;
     }

    private function registerHooks()
    {
        add_action(
            'init',
            [$this, 'addCsrfToken'],
        );

        add_action(
            'wp_enqueue_scripts', // hook name
            [$this, 'loadCss'],
        );

        // loading assets (js/css)
        add_action(
            'wp_enqueue_scripts',
            [$this, 'loadJs'],
        );

        add_action(
            'after_setup_theme', // hook name
            [$this, 'loadSupports'],
        );

        add_action('admin_enqueue_scripts',
            [$this, 'loadAdminCss'],
        );
    }

    public function addCssFromFolder(string $folder)
    {
        $cssComponentsFolder = get_theme_file_path($folder);
        $files = glob($cssComponentsFolder . '/*.css');
        foreach($files as $file) {
            $this->addCss([$folder.'/'.basename($file)]);
        }
    }

    public function addMandatoryCss(array|string $css, $prepend = false)
    {
        if(is_string($css)) {
            $css = [$css];
        }
        if($prepend) {
            $this->mandatoryCss = array_merge($css, $this->mandatoryCss);
        }
        else {
            $this->mandatoryCss = array_merge($this->mandatoryCss, $css);
        }
        $this->mandatoryCss = array_unique($this->mandatoryCss);

        return $this;
    }

    public function prependCss(array|string $css)
    {
        return $this->addCss($css, true);
    }

    public function addCss(array|string $css, $prepend = false)
    {
        if(is_string($css)) {
            $css = [$css];
        }
        if($prepend) {
            $this->css = array_merge($css, $this->css);
        }
        else {
            $this->css = array_merge($this->css, $css);
        }
        $this->css = array_unique($this->css);

        return $this;
    }

    public function addMandatoryJs(array|string $js, $prepend = false)
    {
        if(is_string($js)) {
            $js = [$js];
        }
        if($prepend) {
            $js = array_reverse($js);
            $this->mandatoryJs = array_merge($js, $this->mandatoryJs);
        }
        else {
            $this->mandatoryJs = array_merge($this->mandatoryJs, $js);
        }
        $this->mandatoryJs = array_unique($this->mandatoryJs);

        return $this;
    }

    public function prependJs(array|string $js)
    {
        return $this->addJs($js, true);
    }

    public function addJs(array|string $js, $prepend = false)
    {
        if(is_array($js)) {
            if(array_key_exists('url', $js)) {
                if($prepend) {
                    array_unshift($this->js, $js);
                }
                else {
                    $this->js[] = $js;
                }
            }
            else {
                if($prepend) {
                    $js = array_reverse($js);
                }
                foreach($js as $descriptor) {
                    if($prepend) {
                        array_unshift($this->js, $descriptor);
                    }
                    else {
                        $this->js[] = $descriptor;
                    }
                }
            }
        }
        else {
            if($prepend) {
                array_unshift($this->js, $js);
            }
            else {
                $this->js[] = $js;
            }
        }

        $unduplicates = [];

        foreach($this->js as $descriptor) {
            if(is_string($descriptor)) {
                $unduplicates[$descriptor] = $descriptor;
            }
            else {
                $unduplicates[$descriptor['url']] = $descriptor;
            }
        }

        $this->js = $unduplicates;

        return $this;
    }

    public function addAdminCss(array $css)
    {
        $this->adminCss = array_merge($this->adminCss, $css);
        return $this;
    }

    public function addAdminJs(array $js)
    {
        $this->adminJs = array_merge($this->adminJs, $js);
        return $this;
    }

    public function addSupports(array $supports)
    {
        $this->supports = array_merge($this->supports, $supports);
    }

    public function loadCss()
    {
        foreach($this->mandatoryCss as $index => $url) {
            $cssUrl = $this->computeUrl($url);
            wp_enqueue_style(
                'forge-mandatory-css-' . $index,
                $cssUrl,
            );
        }

        foreach ($this->css as $index => $url) {
            $cssUrl = $this->computeUrl($url);
            wp_enqueue_style(
                'forge-css-' . $index,
                $cssUrl,
            );
        }
    }

    public function loadAdminCss()
    {
        foreach($this->adminCss as $index => $url) {
            $cssUrl = $this->computeUrl($url);

            wp_enqueue_style(
                'forge-admin-css-' . $index,
                $cssUrl,
            );
        }
    }

    public function loadJs()
    {

        foreach ($this->mandatoryJs as $index => $descriptor) {

            if(is_string($descriptor)) {
                $url = $descriptor;
            }
            else {
                $url = $descriptor['url'];
            }

            $url = $this->computeUrl($url);
            wp_enqueue_script(
                'forge-mandatory-js-' . $index,   // js unique name
                $url,
                [], // handle dependencies
                '1.0.0', // javascript file version
                array_merge([
                    'in_footer' => true,
                ], $descriptor['options'] ?? [])
            );
        }

        foreach ($this->js as $index => $descriptor) {
            if(is_string($descriptor)) {
                $url = $descriptor;
            }
            else {
                $url = $descriptor['url'];
            }

            $url = $this->computeUrl($url);


            wp_enqueue_script(
                'forge-js-' . $index,   // js unique name
                $url,
                [], // handle dependencies
                '1.0.0', // javascript file version
                array_merge([
                    'in_footer' => true,
                ], $descriptor['options'] ?? [])
            );
        }
    }


    public function computeUrl(string $source)
    {
        if(strpos($source, 'http') === 0) {
            $url = $source;
        }
        elseif(strpos($source, '//') === 0) {
            $url = $source;
        }
        elseif(strpos($source, 'plugin://') === 0) {
            $pluginName = preg_replace('`plugin://(.*?)/.*`', '$1', $source);
            $path = preg_replace('`plugin://.*?/(.*)`', '$1', $source);
            $url = WP_CONTENT_URL . '/plugins/' . $pluginName . '/' . $path;
        }
        else {
            $url = get_theme_file_uri($source);
        }

        return $url;
    }

    public function loadSupports()
    {
        // DOC WP add_theme_support https://developer.wordpress.org/reference/functions/add_theme_support/
        foreach ($this->supports as $feature) {
            add_theme_support($feature);
        }
    }

    public function getPageUrl(string $slug, $data = []): string
    {
        $page = get_page_by_path($slug);
        if($slug) {
            $url =  get_permalink($page);
            if(count($data)) {
                $url .= '?' . http_build_query($data);
            }

            return $url;
        }

        return false;
    }

    public function getJs(): array
    {
        return $this->js;
    }

}


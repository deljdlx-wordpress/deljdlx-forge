<?php
namespace Deljdlx\WPForge;

use Deljdlx\WPForge\Theme\Theme;
use Illuminate\Config\Repository;

class Application extends Container
{
    private string $cachePath;

    public static function getInstance()
    {
        if(!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public function __construct()
    {
        parent::__construct();
        $this->initialize();

        $contentPath = defined('WP_CONTENT_DIR') ? WP_CONTENT_DIR : ABSPATH . 'wp-content/';
        $this->cachePath = $contentPath . '/deljdlx-forge-cache';
        if(!is_dir($contentPath)) {
            mkdir($contentPath, 0764, true);
        }
    }


    protected function initialize()
    {
        $this->bind(Router::class, function() {
            $router = Router::getInstance();
            return $router;
        }, true);

        $this->bind(Theme::class, function() {
            $theme = Theme::getInstance($this);
            return $theme;
        }, true);

        $this->bindOrMerge('config', function() {
            return new Repository([
                'view' => [
                    'paths' => [
                        get_template_directory() . '/templates',
                    ],
                    'compiled' => $this->cachePath,
                ]
            ]);
        }, true);

        $this->bind(View::class, function() {
            $view = View::getInstance($this,);

            // $view->loadComponentsFromFolder(
            //     __DIR__ . '/../deljdlx-forge/src/class/Components/',
            //     'Deljdlx\WPForge\Components',
            // );

            // $view->loadComponentsFromFolder(
            //     __DIR__ . '/src/class/Components/',
            //     'Deljdlx\WPTaverne\Components',
            // );

            return $view;
        }, true);
    }

    public function loadComponentsFromFolder(string $componentFolder, string $namespace = 'Deljdlx\WPForge\Components')
    {
        $view = $this->get(View::class);
        $view->loadComponentsFromFolder(
            $componentFolder,
            $namespace,
        );
    }

    public function addTemplatePath(string $path)
    {
        $this->bindOrMerge('config', function() use ($path){
            return new Repository([
                'view' => [
                    'paths' => [
                        $path
                    ],
                ]
            ]);
        }, true);
    }

    public function version()
    {
        return 1;
    }


}
<?php
namespace Deljdlx\WPForge;

use Analog\Analog;
use Deljdlx\WPForge\Theme\Theme;
use Illuminate\Config\Repository;
use Analog\Handler\PDO as HandlerPDO;
use PDO;

class Application extends Container
{
    private string $cachePath;
    private array $templatePathes = [];

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

        $this->bind(PDO::class, function() {
            $dsn = 'mysql:host='.DB_HOST.';dbname='.DB_NAME;
            $username = DB_USER;
            $password = DB_PASSWORD;
            $pdo = new PDO($dsn, $username, $password);
            return $pdo;
        }, true);


        $this->bind(Router::class, function() {
            $router = Router::getInstance();
            return $router;
        }, true);

        $this->bind(Theme::class, function() {
            $theme = Theme::getInstance($this);
            return $theme;
        }, true);

        $this->addTemplatePath(get_template_directory() . '/templates', 1000);

        // $this->bindOrMerge('config', function() {
        //     return new Repository([
        //         'view' => [
        //             'paths' => [
        //                 get_template_directory() . '/templates',
        //             ],
        //             'compiled' => $this->cachePath,
        //         ]
        //     ]);
        // }, true);

        $this->bind(View::class, function() {
            $view = View::getInstance($this,);
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

    public function addTemplatePath(string $path, ?int $prority = null)
    {

        $this->templatePathes[] = [
            'path' => $path,
            'priority' => $prority,
        ];

        $this->bind('config', function() {

            $templatePathes = $this->templatePathes;
            // sort by priority
            usort($templatePathes, function($a, $b) {
                // return $a['priority'] <=> $b['priority'];
                return $b['priority'] <=> $a['priority'];
            });

            $paths = [];
            foreach($templatePathes as $templatePath) {
                $paths[] = $templatePath['path'];
            }

            return new Repository([
                'view' => [
                    'paths' => $paths,
                    'compiled' => $this->cachePath,
                ]
            ]);
        }, true);
    }

    public function version()
    {
        return 1;
    }


}
<?php

namespace Deljdlx\WPForge;

use Deljdlx\WPForge\Theme\Theme;
use Deljdlx\WPForge\WpBlade;


class View
{

    public static $instance;

    public readonly ?Theme $theme;
    public readonly WpBlade $blade;

    private Container $container;




    public static function getInstance($container)
    {
        if(!static::$instance) {
            static::$instance = new static($container);
        }
        return static::$instance;
    }




    public function __construct(Container $container, Theme $theme = null)
    {
        $this->container = $container;

        $this->theme = $theme;
        $this->blade = new WpBlade(
            $this->container
        );

        $this->blade->directive('datetime', function ($expression) {
            return "<?php echo with({$expression})->format('F d, Y g:i a'); ?>";
        });

        $this->blade->directive('wp_head', function () {
            return "<?php wp_head(); ?>";
        });

        $this->blade->directive('wp_footer', function () {

            return "
                <?php wp_footer(); ?>
            ";
        });

        $this->blade->directive('themeUri', function ($expression) {
            return "<?php echo get_theme_file_uri({$expression});?>";
        });

        $this->blade->directive('termLink', function ($expression) {
            return "<a href=\"<?php echo get_term_link($expression);?>\" class=\"forge-wp-term wp-term\"><?php echo {$expression}->name;?></a>";
        });
    }

    public function templateExists($templateName)
    {
        /** @var \Illuminate\Config\Repository $config */
        $config = $this->container->get('config');
        $pathes = $config->get('view')['paths'];
        $templatePath = str_replace('.', '/', $templateName);

        foreach($pathes as $path) {
            $fullPath = $path . '/' . $templatePath . '.blade.php';
            if(file_exists($fullPath)) {
                return true;
            }
        }
        return false;
    }

    public function getTemplatePathes() {
        $config = $this->container->get('config');
        return $config->get('view')['paths'];
    }

    public function compileTemplateString(string $template, $variables = [])
    {
        $compiled =  $this->blade->compileString($template);

        $render = function($compiled, $variables) {
            ob_start();
            extract($variables, EXTR_SKIP);
            eval('?>' . $compiled);
            return ob_get_clean();
        };

        return $render($compiled, $variables);
    }

    public function loadComponentsFromFolder(string $componentFolder, string $namespace = 'Deljdlx\WPForge\Components')
    {
        $files = rglob($componentFolder, '*.php');
        foreach ($files as $file) {
            $relativePath = str_replace($componentFolder, '', $file);
            $className = str_replace('.php', '', $relativePath);
            $className = str_replace('/', '\\', $className);
            $fqcn = $namespace .'\\' . $className;

            if (!class_exists($fqcn)) {
                error_log("Component class not found : $fqcn");
                continue;
            }

            $componentTag = class_basename($className);
            $componentTag = preg_replace('/(?<!^)[A-Z](?=[a-z])/', '-$0', $componentTag);
            $componentTag = strtolower($componentTag);



            $this->blade->component($componentTag, $fqcn);
        }
    }

    function class_basename($class)
    {
        $class = is_object($class) ? get_class($class) : $class;
        return basename(str_replace('\\', '/', $class));
    }


    public function component(string $name, $callback)
    {
        return $this->blade->component($name, $callback);
    }

    public function getUri($path)
    {
        return get_theme_file_uri($path);
    }

    public function render($template, $variables = [])
    {
        return $this->blade->make($template, $variables)->render();
    }
}



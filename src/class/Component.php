<?php
namespace Deljdlx\WPForge;

use Deljdlx\WPForge\Theme\Theme;
use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\View\Component as ViewComponent;

abstract class Component extends ViewComponent
{

    protected $js = [];
    protected $css = [];

    public function render()
    {
        $theme = Theme::getInstance(Container::getInstance());
        foreach ($this->css as $css) {
            $theme->prependCss($css);
        }

        foreach ($this->js as $js) {
            $theme->prependJs($js);
        }
    }

}


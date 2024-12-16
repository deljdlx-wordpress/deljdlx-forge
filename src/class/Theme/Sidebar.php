<?php

namespace Deljdlx\WPForge\Theme;

class Sidebar
{
    private $sidebars = [];

    public function __construct()
    {
    }

    public function register($name, $label, $description = '')
    {

        $this->sidebars[] = $name;


        add_action( 'widgets_init', function() use($name, $label, $description) {
            register_sidebar( array(
                'name'          => $label,
                'id'            => $name,
                'description'   => $description,
                'before_widget' => '<section id="%1$s" class="widget forge-widget %2$s">',
                'after_widget'  => '</section>',

                // 'before_title'  => '<h2 class="widget-title">',
                // 'after_title'   => '</h2>',

            ));
        });
    }

    public function render($id)
    {
        dynamic_sidebar($id);
    }
}

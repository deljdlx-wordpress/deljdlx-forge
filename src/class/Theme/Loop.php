<?php

namespace Deljdlx\WPForge\Theme;

use Deljdlx\WPForge\Models\Post;

class Loop
{
    public function __construct()
    {
    }


    public function getPost($className = null)
    {
        global $wp_query;
        if ($className) {
            $postInstance = new $className();
        } else {
            $postInstance = new Post();
        }
        $postInstance->loadFromWpPost($wp_query->get_queried_object());
        return $postInstance;
    }

    public function getPosts()
    {

        global $posts;

        foreach ($posts as $post) {
            the_post();
            $postInstance = new Post();
            $postInstance->loadFromWpPost($post);
            yield $postInstance;
        }
    }

}

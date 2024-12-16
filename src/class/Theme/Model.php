<?php

namespace Deljdlx\WPForge\Theme;

use Deljdlx\WPForge\Models\Post;

class Model
{
    private $corcel;

    public function __construct()
    {
        $this->initializeCorcel();
    }

    public function getPosts($postType = 'post', $limit = 10)
    {
        $posts = get_posts([
            'post_type' => $postType,
            'posts_per_page' => $limit,
            'orderby' => 'date',
            'order' => 'DESC',
        ]);

        $models = [];
        foreach ($posts as $post) {
            $model = new Post();
            $model->loadFromWpPost($post);
            $models[] = $model;
        }

        return $models;
    }

    private function initializeCorcel()
    {
        global $table_prefix;
        $params = [
            'host'      => DB_HOST,
            'database'  => DB_NAME,
            'username'  => DB_USER,
            'password'  => DB_PASSWORD,
            'prefix'    => $table_prefix,
        ];
        $this->corcel = \Corcel\Database::connect($params);
    }

}
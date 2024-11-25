<?php
namespace Deljdlx\WPForge\Controllers;

use Deljdlx\WPForge\Theme;

class BaseController
{
    protected Theme $theme;
    protected $session;

    public function __construct()
    {
        $this->session = wp_forge()->session;
    }


    public function setFlash(string $name, mixed $content)
    {
        $this->session->set($name, $content, true);
    }

    public function getFlash(string $name, $default = null)
    {
        return $this->session->get($name, $default);
    }

    public function getPageUrl(string $slug): string
    {
        $page = get_page_by_path($slug);
        if($slug) {
            return get_permalink($page);
        }

        return false;
    }


    public function redirect($uri)
    {
        $url = home_url($uri);
        wp_redirect($url);
        exit;
    }


    public function redirectBySlug(string $slug, $options = [])
    {
        $url = $this->getPageUrl($slug);
        if($url) {
            wp_redirect(
                add_query_arg(
                    $options,
                    $url
                ),
            );
            exit;

            // wp_redirect(get_permalink($page));
        }
    }

    public function getCurrentUserId()
    {
        return wp_forge()->user->getId();
    }

    public function hasFiles()
    {
        return !empty($_FILES);
    }

    public function hasGet()
    {
        return !empty($_GET);
    }


    public function hasPost()
    {
        return !empty($_POST);
    }


    public function inputFile(string $name = null)
    {
        if($name === null) {
            return $_FILES;
        }

        return isset($_FILES[$name]) ? $_FILES[$name] : null;
    }

    public function inputGet(string $name = null)
    {
        if($name === null) {
            return $_GET;
        }

        return isset($_GET[$name]) ? $_GET[$name] : null;
    }

    public function inputPost(string $name = null)
    {
        if($name === null) {
            return $_POST;
        }

        return isset($_POST[$name]) ? $_POST[$name] : null;
    }

    public function getAction()
    {
        return $this->inputGet('action');
    }



    public function view(string $layout, array $data = []): string
    {
        return wp_forge()->view->render($layout, $data);
    }

}

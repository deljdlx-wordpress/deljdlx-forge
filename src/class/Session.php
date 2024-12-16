<?php

namespace Deljdlx\WPForge;

use Deljdlx\WPForge\Models\Post;

class Session
{
    public function __construct()
    {
    }

    public function set($key, $value, $isFlash = false)
    {
        $_SESSION[$key] = [
            'value' => $value,
            'is_flash' => $isFlash,
        ];
    }

    public function get($key, $default = null)
    {
        if(isset($_SESSION[$key])) {
            $value = $_SESSION[$key]['value'];
            if($_SESSION[$key]['is_flash']) {
                unset($_SESSION[$key]);
            }
            return $value;
        }
        return $default;
    }
}

<?php
namespace Deljdlx\WPForge;

use Illuminate\Contracts\View\Factory as ViewFactory;
use Illuminate\View\Component as ViewComponent;

abstract class Component extends ViewComponent
{
    public $id;

    public function createId() {
        $this->id = $this->generateUniqueId();
    }

    protected function generateUniqueId($length = 10)
    {
        return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
    }
}


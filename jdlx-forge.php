<?php
/**
 * @package JDLX_Forge
 * @version 1
 */

use Deljdlx\WPForge\Application;

/*
Plugin Name: JDLX Forge
Version: 1
*/

if(!defined('ABSPATH')){
    exit;
}


require 'composer/autoload.php';
define('JDLX_FORGE_PLUGIN_DIR', __DIR__);
define('JDLX_FORGE_ENABLED', true);


$container = Application::getInstance();
$container->addTemplatePath(__DIR__ . '/templates');

require_once __DIR__ . '/embedded-plugins/kirki/kirki.php';

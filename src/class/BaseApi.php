<?php
namespace Deljdlx\WPForge;

use Pecule;
use WP_REST_Request;
use WP_User;
use WpPecule\PeculeApi\Client;
use WpPecule\Controllers\ApiProxy;
use WpPecule\Models\Customer;

abstract class BaseApi
{
    /**
     * @var string
     */
    protected $baseURI;
    protected $container;
    protected $apiRoot = 'jdlx-force/v1';

    public function __construct($container)
    {
        $this->baseURI = dirname($_SERVER['SCRIPT_NAME']);
        add_action('rest_api_init', [$this, 'initialize']);
        $this->container = $container;

    }

    abstract function initialize();
}



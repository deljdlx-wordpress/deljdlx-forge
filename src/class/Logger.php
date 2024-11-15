<?php
namespace Deljdlx\WPForge;

use Analog\Analog;
use Deljdlx\WPForge\Models\User;

class Logger
{
    public static function log($message, $type = null, $level = null)
    {

        $log = [];

        $remoteAddress = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $currentUser = User::getCurrentByCookie();

        $log['type'] = $type;
        $log['remote_address'] = $remoteAddress;

        if($currentUser) {
            $log['user_id'] = $currentUser->ID;
            $log['user_email'] = $currentUser->user_email;
        }

        $log['data'] = $message;

        Analog::log(
            json_encode($log, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT),
            $level,
        );
    }

    public static function urgent($message, $type = null)
    {
        self::log($message, $type, Analog::URGENT);
    }

    public static function alert($message, $type = null)
    {
        self::log($message, $type, Analog::ALERT);
    }

    public static function critical($message, $type = null)
    {
        Analog::log($message, $type, Analog::CRITICAL);
    }

    public static function error($message, $type = null)
    {
        Analog::log($message, $type, Analog::ERROR);
    }

    public static function warning($message, $type = null)
    {
        Analog::log($message, $type, Analog::WARNING);
    }

    public static function notice($message, $type = null)
    {
        Analog::log($message, $type, Analog::NOTICE);
    }

    public static function info($message, $type = null)
    {
        Analog::log($message, $type, Analog::INFO);
    }

    public static function debug($message, $type = null)
    {
        Analog::log($message, $type, Analog::DEBUG);
    }
}

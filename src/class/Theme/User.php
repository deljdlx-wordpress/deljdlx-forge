<?php

namespace Deljdlx\WPForge\Theme;

class User
{
    public function __construct()
    {

    }

    public function isConnected()
    {
        $user = wp_get_current_user();
        if ($user->ID) {
            return true;
        }

        return false;
    }

    public function getId()
    {
        $user = wp_get_current_user();

        return $user->ID;
    }

    public function getLogoutUrl(string $returnUrl = null)
    {
        if($returnUrl === null) {
            $returnUrl = get_home_url();
        }

        return wp_logout_url($returnUrl);
    }

    public function getDisplayName()
    {
        $user = wp_get_current_user();
        if ($user->ID) {
            return $user->display_name;
        }

        return null;
    }

    public function getBirthDate()
    {
        // get acf field bitrhdate
        $user = wp_get_current_user();
        if ($user->ID) {
            return get_field('birthdate', 'user_' . $user->ID);
        }

        return null;
    }


    public function getFirstName()
    {
        $user = wp_get_current_user();
        if ($user->ID) {
            return $user->first_name;
        }

        return null;
    }

    public function getLastName()
    {
        $user = wp_get_current_user();
        if ($user->ID) {
            return $user->last_name;
        }

        return null;
    }

    public function getEmail()
    {
        $user = wp_get_current_user();
        if ($user->ID) {
            return $user->user_email;
        }

        return null;
    }
}

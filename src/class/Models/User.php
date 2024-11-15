<?php

namespace Deljdlx\WPForge\Models;

use Corcel\Model\User as CorcelUser;

class User extends CorcelUser
{


    public static function getCurrent()
    {
        $userId = get_current_user_id();
        $user = static::find($userId);
        if(!$user) {
            return new static();
        }

        return $user;
    }

    public function checkPassword(string $password)
    {
        // dump($password);
        // dump($this);
        // dump(wp_check_password($password, $this->user_pass, $this->ID));
        // echo __FILE__.':'.__LINE__; exit();
        return wp_check_password($password, $this->user_pass, $this->ID);
    }

    public function setPassword(string $password)
    {
        return wp_set_password($password, $this->ID);
    }

    public function isConnected()
    {
        return is_user_logged_in();
    }

    public function hasRole(string $role): bool
    {
        $roles = wp_get_current_user()->roles;
        return in_array($role, $roles);
    }

    public function getAcfFields()
    {
        return get_fields('user_'.$this->getId());
    }


    public function loadById(string $id)
    {
        $user = CorcelUser::find($id);

        foreach ($user as $attribute => $value) {
            $this->$attribute = $value;
        }
    }

    public function getId()
    {
        return $this->ID;
    }

    public function getAcfField($fieldName)
    {
        return get_field($fieldName, 'user_'.$this->ID);
    }

    public static function getCurrentByCookie()
    {
        $currentUserId = get_current_user_id();
        if(!array_key_exists(LOGGED_IN_COOKIE, $_COOKIE)) {
            return new static();
        }

        $userId = \wp_validate_auth_cookie($_COOKIE[LOGGED_IN_COOKIE], 'logged_in');
        $user = static::find($userId);
        if(!$user) {
            return new static();
        }

        return $user;
    }

    public function getRoles()
    {
        $user = new \WP_User($this->ID);
        return $user->roles;
    }
}


<?php

namespace Deljdlx\WPForge\Theme;

class Admin
{
    public function __construct()
    {
    }

    public function addPage( $name, $menuEntry, $callback, $priority = 100, $authorizations = 'manage_options', $icon = 'dashicons-admin-tools')
    {
        add_action('admin_menu', function() use($menuEntry, $name, $callback, $authorizations, $priority, $icon) {
            add_menu_page(
                $menuEntry,
                $menuEntry,
                $authorizations,
                $name,
                $callback,
                $icon,
                $priority
            );
        });

        return $this;
    }
}

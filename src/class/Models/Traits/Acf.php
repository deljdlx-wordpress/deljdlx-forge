<?php

namespace Deljdlx\WPForge\Models\Traits;

Trait Acf
{
    public function getField(string $fieldName)
    {
        return get_field($fieldName, $this->wpPost->ID);
    }
}

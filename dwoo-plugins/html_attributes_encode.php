<?php

function Dwoo_Plugin_html_attributes_encode(Dwoo_Core $dwoo, $array, $prefix = null)
{
    $attributes = array();

    foreach ($array AS $key => $value) {
        if ($value === false || $value === null) {
            continue;
        }

        $attribute = ($prefix ?: '') . $key;

        if ($value !== true) {
            $attribute .= '="' . htmlspecialchars(
                (is_string($value) || is_int($value)) ? $value : json_encode($value)
            ). '"';
        }

        $attributes[] = $attribute;
    }

    return implode(' ', $attributes);
}

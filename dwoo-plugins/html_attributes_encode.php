<?php

function Dwoo_Plugin_html_attributes_encode(Dwoo_Core $dwoo, $array, $prefix = null)
{
    $attributes = array();

    foreach ($array AS $key => $value) {
        if ($value === false) {
            continue;
        }

        $attribute = ($prefix ?: '') . $key;

        if ($value !== true) {
            $attribute .= '="' . htmlspecialchars($value) . '"';
        }

        $attributes[] = $attribute;
    }

    return implode(' ', $attributes);
}

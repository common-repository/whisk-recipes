<?php

use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Helper;
use Whisk\Recipes\Vendor\Carbon_Fields\Helper\Color;
if (!\function_exists('whisk_carbon_field_exists')) {
    function whisk_carbon_field_exists($name, $container_type, $container_id = '')
    {
        return Helper::get_field($container_type, $container_id, $name) !== null;
    }
}
if (!\function_exists('whisk_carbon_get')) {
    function whisk_carbon_get($object_id, $name, $container_type, $container_id = '')
    {
        return Helper::get_value($object_id, $container_type, $container_id, $name);
    }
}
if (!\function_exists('whisk_carbon_set')) {
    function whisk_carbon_set($object_id, $name, $value, $container_type, $container_id = '')
    {
        Helper::set_value($object_id, $container_type, $container_id, $name, $value);
    }
}
if (!\function_exists('whisk_carbon_get_the_post_meta')) {
    function whisk_carbon_get_the_post_meta($name, $container_id = '')
    {
        return Helper::get_the_post_meta($name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_post_meta')) {
    function whisk_carbon_get_post_meta($id, $name, $container_id = '')
    {
        return Helper::get_post_meta($id, $name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_set_post_meta')) {
    function whisk_carbon_set_post_meta($id, $name, $value, $container_id = '')
    {
        Helper::set_post_meta($id, $name, $value, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_theme_option')) {
    function whisk_carbon_get_theme_option($name, $container_id = '')
    {
        return Helper::get_theme_option($name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_set_theme_option')) {
    function whisk_carbon_set_theme_option($name, $value, $container_id = '')
    {
        Helper::set_theme_option($name, $value, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_the_network_option')) {
    function whisk_carbon_get_the_network_option($name, $container_id = '')
    {
        return Helper::get_the_network_option($name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_network_option')) {
    function whisk_carbon_get_network_option($id, $name, $container_id = '')
    {
        return Helper::get_network_option($id, $name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_set_network_option')) {
    function whisk_carbon_set_network_option($id, $name, $value, $container_id = '')
    {
        Helper::set_network_option($id, $name, $value, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_term_meta')) {
    function whisk_carbon_get_term_meta($id, $name, $container_id = '')
    {
        return Helper::get_term_meta($id, $name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_set_term_meta')) {
    function whisk_carbon_set_term_meta($id, $name, $value, $container_id = '')
    {
        Helper::set_term_meta($id, $name, $value, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_user_meta')) {
    function whisk_carbon_get_user_meta($id, $name, $container_id = '')
    {
        return Helper::get_user_meta($id, $name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_set_user_meta')) {
    function whisk_carbon_set_user_meta($id, $name, $value, $container_id = '')
    {
        Helper::set_user_meta($id, $name, $value, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_comment_meta')) {
    function whisk_carbon_get_comment_meta($id, $name, $container_id = '')
    {
        return Helper::get_comment_meta($id, $name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_set_comment_meta')) {
    function whisk_carbon_set_comment_meta($id, $name, $value, $container_id = '')
    {
        Helper::set_comment_meta($id, $name, $value, $container_id);
    }
}
if (!\function_exists('whisk_carbon_get_nav_menu_item_meta')) {
    function whisk_carbon_get_nav_menu_item_meta($id, $name, $container_id = '')
    {
        return Helper::get_nav_menu_item_meta($id, $name, $container_id);
    }
}
if (!\function_exists('whisk_carbon_set_nav_menu_item_meta')) {
    function whisk_carbon_set_nav_menu_item_meta($id, $name, $value, $container_id = '')
    {
        Helper::set_nav_menu_item_meta($id, $name, $value, $container_id);
    }
}
if (!\function_exists('whisk_carbon_hex_to_rgba')) {
    function whisk_carbon_hex_to_rgba($hex)
    {
        return Color::hex_to_rgba($hex);
    }
}

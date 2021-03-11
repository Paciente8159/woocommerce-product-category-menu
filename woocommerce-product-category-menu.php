<?php

/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://github.com/Paciente8159
 * @since             1.0.0
 * @package           JCEM_Woocommerce_product_category_menu
 *
 * @wordpress-plugin
 * Plugin Name:       JCEM Woocommerce product category/subcategory menu
 * Plugin URI:        https://github.com/Paciente8159
 * Description:       Adds a dynamic category and subcategory product menu for woocommerce via shortcode.
 * Version:           1.0.0
 * Author:            Joao Martins
 * Author URI:        https://github.com/Paciente8159
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       woocommerce-category-menu
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if (!defined('ABSPATH')) {
    die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */

add_shortcode('product_category_menu', function ($atts) {

    $atts = shortcode_atts([
        'hide_empty' => '0',
        'parent_id' => '0',
        'show_count' => '0',
    ], $atts, 'product_category_menu');

    $show_count = intval($atts['show_count']);
    $hide_empty = intval($atts['hide_empty']);
    $parent_id = intval($atts['parent_id']);

    $args = array(
        'taxonomy'     => 'product_cat',
        'orderby'      => 'name',
        'hierarchical' => 1,
        'title_li'     => '',
        'hide_empty'   => $hide_empty,
        'child_of'     => 0,
        'parent'       => $parent_id,
    );

    $cats = get_categories($args);
    $content = "";
    if (!empty($cats)) {
        $content = ($parent_id) ? '<ul class="product-category-submenu">' : '<ul class="product-category-menu">';
        foreach ($cats as $cat) {
            $category_id = $cat->term_id;
            $nested_shortcode = sprintf('[product_category_menu show_count="%d" hide_empty="%d" parent_id="%d"]', $show_count, $hide_empty, $category_id);
            $cat_count = ($show_count) ? '<span>(' . $cat->count . ')</span>' : '';
            $cat_count = apply_filters('jcem_wc_show_product_count', $cat_count, $cat->count);
            $sub_menu = do_shortcode($nested_shortcode);
            $sub_menu_toggle_control = apply_filters('jcem_wc_toggle_menu_control', '<span class="product-category-submenu-toogle dashicons dashicons-arrow-down"></span>');
            $sub_menu_toggle_control = !empty($sub_menu) ? $sub_menu_toggle_control : '';
            $content .= '<li class="product-category-menu-item product-category-menu-item-collapsed"><div class="product-category-menu-item-control"><a href="' . get_term_link($cat->slug, 'product_cat') . '">' . $cat->name . $cat_count . '</a>'. $sub_menu_toggle_control .'</div>' . $sub_menu . '</li>';
        }
        $content .= '</ul>';
    }

    return $content;
});

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('dashicons');
    wp_enqueue_style('woocommerce-product-category-menu', plugin_dir_url(__FILE__) . 'assets/css/woocommerce-product-category-menu.css', array());
    wp_enqueue_script('woocommerce-product-category-menu', plugin_dir_url(__FILE__) . 'assets/js/woocommerce-product-category-menu.js', array(), true, true);
});
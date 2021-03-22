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
 * Version:           1.1.0
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
        'hide_empty' => '1',
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
            $content .= '<li class="product-category-menu-item product-category-menu-item-collapsed"><div class="product-category-menu-item-control"><a href="' . get_term_link($cat->slug, 'product_cat') . '">' . $cat->name . $cat_count . '</a>' . $sub_menu_toggle_control . '</div>' . $sub_menu . '</li>';
        }
        $content .= '</ul>';
    }

    return $content;
});

function jcem_wc_build_menu_content($top_id = 0, $top_menu_id = 0, $custom_args = array())
{
    $category_args = array(
        'taxonomy'     => 'product_cat',
        'orderby'      => 'name',
        'hierarchical' => 1,
        'title_li'     => '',
        'hide_empty'   => '1',
    );

    $category_args = wp_parse_args($custom_args, $category_args);
    $categories = get_categories($category_args);

    //starts populating the menu
    $nodes = [$top_id];
    $node_relations = [];
    $menu_items = array();
    for ($i = 0; $i < count($nodes); $i++) {
        $current_node = $nodes[$i];
        foreach ($categories as $cat) {
            if ($cat->parent == $current_node) {
                array_push($nodes, $cat->term_id);
                $new_item = array(
                    'ID' => intval($cat->term_id),
                    'db_id' => intval($cat->term_id),
                    'post_type' => "nav_menu_item",
                    'object_id' => intval($cat->term_id),
                    'object' => "product_cat",
                    'type' => "taxonomy",
                    'type_label' => __("Product Category", "textdomain"),
                    'title' => $cat->name,
                    'target' => '',
                    'xfn' => '',
                    'post_title' => $cat->name,
                    'post_content' => $cat->name,
                    'post_excerpt' => $cat->slug . '-' . strval($cat->term_id),
                    'url' => get_term_link($cat),
                    'classes' => array(),
                    'menu-item-title' =>  __($cat->name),
                    'menu-item-url' =>  get_term_link($cat->term_id),
                    'menu-item-status' => 'publish',
                    'menu-item-attr-title' => $cat->slug . '-' . strval($cat->term_id),
                );

                if ($cat->category_parent != $top_id) {
                    $new_item['menu-item-parent-id'] = $cat->category_parent;
                    $new_item['menu_item_parent'] = intval($cat->category_parent);
                    $new_item['post_parent'] = $top_id;
                } else {
                    $new_item['menu-item-parent-id'] = $top_menu_id;
                    $new_item['menu_item_parent'] = $top_menu_id;
                    $new_item['post_parent'] = $top_menu_id;
                }

                $node_relations[$cat->term_id] = $current_node;
                array_push($menu_items, (object)$new_item);
            }
        }
    }

    $menu_order = 0;
    // Set the order property
    foreach ($menu_items as &$menu_item) {
        $menu_order++;
        $menu_item->menu_order = $menu_order;
    }
    unset($menu_item);

    return $menu_items;
}

add_filter('wp_get_nav_menu_items', function ($items, $menu, $args) {

    $custom_menus = [['name' => 'jcem_wc_product_category_menu', 'taxonomy' => 'product_cat', 'root_id' => 0]];
    $custom_menus = apply_filters('jcem_wc_product_category_custom_menus', $custom_menus);
    $count = -1;

    //custom menus
    foreach ($custom_menus as $custom_menu) {
        if ($menu->slug === $custom_menu['name']) {
            //get all product categories
            $root_item_id = intval($custom_menu['root_id'], 10);
            $root_menu_item_id = intval(isset($custom_menu['root_menu_id']) ? $custom_menu['root_menu_id'] : $custom_menu['root_id'], 10);
            $args = isset($custom_menu['query_args']) ? $custom_menu['query_args'] : array();
            return jcem_wc_build_menu_content($root_item_id, $root_menu_item_id, $args);
        }
    }

    //automatic sub menu item population 
    foreach ($items as $item) {
        $autopopulate = apply_filters('jcem_wc_product_category_autopopulate', false, $item, $menu, $args);
        if ($autopopulate) {
            $items = array_merge($items, jcem_wc_build_menu_content(intval($item->object_id, 10), $item->ID));
        }
    }

    //reorder menu
    $menu_order = 0;
    // Set the order property
    foreach ($items as &$menu_item) {
        $menu_order++;
        $menu_item->menu_order = $menu_order;
    }
    unset($menu_item);

    return $items;
}, 10, 3);

add_filter('wp_get_nav_menu_object', function ($menu_obj, $menu) {
    if ($menu_obj === false) {
        $custom_menus = [['name' => 'jcem_wc_product_category_menu', 'taxonomy' => 'product_cat', 'root_id' => 0]];
        $custom_menus = apply_filters('jcem_wc_product_category_custom_menus', $custom_menus);
        $count = -1;
        foreach ($custom_menus as $custom_menu) {
            if ($menu === $count) {
                $dummy = [];
                $dummy['term_id'] = $count;
                $dummy['name'] = $custom_menu['name'];
                $dummy['slug'] = $custom_menu['name'];
                $dummy['term_group'] = 0;
                $dummy['term_taxonomy_id'] = $count;
                $dummy['taxonomy'] = 'nav_menu';
                $dummy['description'] = '';
                $dummy['parent'] = 0;
                $dummy['count'] = 0;
                $dummy['filter'] = 'raw';
                return new WP_Term((object)$dummy);
            }
            $count--;
        }
    }

    return $menu_obj;
}, 10, 2);

add_filter('get_terms', function ($terms, $taxonomies, $args, $term_query) {
    if (in_array('nav_menu', $taxonomies)) {
        $custom_menus = [['name' => 'jcem_wc_product_category_menu', 'taxonomy' => 'product_cat', 'root_id' => 0]];
        $custom_menus = apply_filters('jcem_wc_product_category_custom_menus', $custom_menus);
        $count = -1;
        foreach ($custom_menus as $custom_menu) {
            $dummy = [];
            $dummy['term_id'] = $count;
            $dummy['name'] = $custom_menu['name'];
            $dummy['slug'] = $custom_menu['name'];
            $dummy['term_group'] = 0;
            $dummy['term_taxonomy_id'] = $count;
            $dummy['taxonomy'] = 'nav_menu';
            $dummy['description'] = '';
            $dummy['parent'] = 0;
            $dummy['count'] = 0;
            $dummy['filter'] = 'raw';
            array_push($terms, new WP_Term((object)$dummy));
            $count--;
        }
    }

    return $terms;
}, 10, 4);

add_action('wp_enqueue_scripts', function () {
    wp_enqueue_style('dashicons');
    wp_enqueue_style('woocommerce-product-category-menu', plugin_dir_url(__FILE__) . 'assets/css/woocommerce-product-category-menu.css', array());
    wp_enqueue_script('woocommerce-product-category-menu', plugin_dir_url(__FILE__) . 'assets/js/woocommerce-product-category-menu.js', array(), true, true);
});

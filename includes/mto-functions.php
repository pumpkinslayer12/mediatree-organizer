<?php
/**
 * The core plugin functionality.
 *
 * @link https://github.com/pumpkinslayer12/mediatree-organizer/
 * @since 1.0.0
 *
 * @package MediaTree_Organizer
 * @subpackage MediaTree_Organizer/includes
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

function mto_register_custom_taxonomy()
{
    $labels = array(
        'name' => _x('Categories', 'taxonomy general name', 'mediatree-organizer'),
        'singular_name' => _x('Category', 'taxonomy singular name', 'mediatree-organizer'),
        'search_items' => __('Search Categories', 'mediatree-organizer'),
        'all_items' => __('All Categories', 'mediatree-organizer'),
        'parent_item' => __('Parent Category', 'mediatree-organizer'),
        'parent_item_colon' => __('Parent Category:', 'mediatree-organizer'),
        'edit_item' => __('Edit Category', 'mediatree-organizer'),
        'update_item' => __('Update Category', 'mediatree-organizer'),
        'add_new_item' => __('Add New Category', 'mediatree-organizer'),
        'new_item_name' => __('New Category Name', 'mediatree-organizer'),
        'menu_name' => __('Category', 'mediatree-organizer'),
    );

    $args = array(
        'hierarchical' => true,
        // make it hierarchical so it behaves like categories
        'labels' => $labels,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'media-category'),
        'show_in_rest' => true,
    );

    register_taxonomy('mto_category', array('attachment'), $args);
}

add_action('init', 'mto_register_custom_taxonomy');
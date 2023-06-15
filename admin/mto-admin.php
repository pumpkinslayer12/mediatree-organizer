<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link https://github.com/pumpkinslayer12/mediatree-organizer/
 * @since 1.0.0
 *
 * @package MediaTree_Organizer
 * @subpackage MediaTree_Organizer/admin
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

function mto_enqueue_admin_scripts($hook)
{

    if ('upload.php' !== $hook) {
        return;
    }

    $jstree_path = plugin_dir_url(__FILE__) . '../includes/';

    wp_enqueue_script('jstree', $jstree_path . 'js/mto-jstree.min.js', array('jquery'), '3.3.8', true);
    wp_enqueue_script('mto-admin-js', plugin_dir_url(__FILE__) . 'js/mto_admin.js', array('jstree'), '1.0.0', true);

    wp_enqueue_style('jstree-style', $jstree_path . 'css/mto-jstree-style.min.css');
    wp_enqueue_style('mto-admin-style', plugin_dir_url(__FILE__) . 'css/mto-admin-style.css');

    $categories = mto_get_all_categories();
    $nodes = mto_convert_categories_to_jstree_nodes($categories);

    // Pass data to the JavaScript file
    wp_localize_script('mto-admin-js', 'mtoData', array(
        'nodes' => $nodes,
    ));
}
add_action('admin_enqueue_scripts', 'mto_enqueue_admin_scripts');

function mto_get_all_categories($parent = 0, $level = 0) {
    $args = array(
        'taxonomy' => 'mto_category',
        'parent'   => $parent,
        'hide_empty' => false,
    );
    
    $categories = get_terms($args);
    $children = array();

    if ($categories) {
        foreach ($categories as $category) {
            $category->level = $level;
            $category->children = mto_get_all_categories($category->term_id, $level + 1);
            $children[] = $category;
        }
    }

    return $children;
}

function mto_convert_categories_to_jstree_nodes($categories) {
    $nodes = array();

    foreach ($categories as $category) {
        $node = array(
            'id' => $category->term_id,
            'parent' => ($category->parent == 0) ? '#' : $category->parent,
            'text' => str_repeat('-', $category->level) . $category->name,
        );
        $nodes[] = $node;
        
        if (!empty($category->children)) {
            $child_nodes = mto_convert_categories_to_jstree_nodes($category->children);
            $nodes = array_merge($nodes, $child_nodes);
        }
    }

    return $nodes;
}



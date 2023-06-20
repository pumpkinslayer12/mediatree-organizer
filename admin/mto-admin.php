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

function mto_setup_admin_media($hook)
{

    if ('upload.php' !== $hook) {
        return;
    }

    $jstree_path = plugin_dir_url(__FILE__) . '../includes/';

    wp_enqueue_script('jstree', $jstree_path . 'js/mto-jstree.min.js', array('jquery'), '3.3.8', true);
    wp_enqueue_script('mto-admin-js', plugin_dir_url(__FILE__) . 'js/mto_admin.js', array('jstree'), '1.0.0', true);

    wp_enqueue_style('jstree-style', $jstree_path . 'css/mto-jstree.min.css');
    wp_enqueue_style('mto-admin-style', plugin_dir_url(__FILE__) . 'css/mto-admin.css');

    $categories = mto_get_categories_for_folders();
    wp_localize_script(
        'mto-admin-js',
        'mtoData',
        array(
            'categories_json' => $categories,
        )
    );
}
add_action('admin_enqueue_scripts', 'mto_setup_admin_media');


// Recursive function to fetch and build an array of categories and subcategories to build admin folders
function mto_get_categories_for_folders($parent = 0)
{
    $args = array(
        'taxonomy' => 'mto_category',
        'parent' => $parent,
        'hide_empty' => false,
    );

    $terms = get_terms($args);
    $categories = [];

    // Check if any term exists
    if (!empty($terms) && is_array($terms)) {
        foreach ($terms as $term) {
            $category = [
                'id' => $term->term_id,
                'text' => $term->name,
                'children' => [],
                'a_attr' => [
                    'href' => get_term_link($term->term_id)
                ]
            ];

            // If the term has children, get them
            if (get_term_children($term->term_id, 'mto_category')) {
                $category['children'] = mto_get_categories_for_folders($term->term_id);
            }

            $categories[] = $category;
        }
    }

    return $categories;
}
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
            'ajax_url' => admin_url('admin-ajax.php')
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

function mto_fetch_media_items()
{

    // Check if the category ID is set and valid.
    if (isset($_GET['categoryId']) && is_numeric($_GET['categoryId'])) {
        $categoryId = intval($_GET['categoryId']);

        // Use WP_Query to fetch the media items associated with the category.
        $attachments = get_posts([
            'post_type' => 'attachment',
            'tax_query' => [
                [
                    'taxonomy' => 'mto_category',
                    'field' => 'term_id',
                    'terms' => $categoryId,
                    'include_children' => false // Exclude attachments from child terms
                ],
            ],
            'numberposts' => -1, // Get all attachments
        ]);

        // Check if the query has posts.
        if (count($attachments) > 0) {
            // Return the posts as a JSON response.
            wp_send_json_success($attachments);
        } else {
            // If no posts were found, return an error message.
            wp_send_json_error('No media items found for this category.');
        }
    } else {
        // If the category ID is not set or invalid, return an error message.
        wp_send_json_error('Invalid category ID.');
    }

    // Always die in functions echoing AJAX content.
    die();
}
add_action('wp_ajax_mto_fetch_media_items', 'mto_fetch_media_items');

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
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

    // If the category ID GET parameter is set, add a filter to the WP_Query that gets the media items.
    if (isset($_GET['categoryId']) && is_numeric($_GET['categoryId'])) {
        $categoryId = intval($_GET['categoryId']);

        add_filter('ajax_query_attachments_args', function ($query) use ($categoryId) {
            $query['tax_query'] = [
                [
                    'taxonomy' => 'mto_category',
                    'field' => 'term_id',
                    'terms' => $categoryId,
                    'include_children' => false,
                ],
            ];
            return $query;
        });
    }

    $jstree_path = plugin_dir_url(__FILE__) . '../includes/';

    wp_enqueue_script('jstree', $jstree_path . 'js/mto-jstree.min.js', array('jquery'), '3.3.8', true);
    wp_enqueue_script('mto-admin-js', plugin_dir_url(__FILE__) . 'js/mto_admin.js', array('jstree'), '1.0.0', true);
    wp_enqueue_script('jquery-ui-resizable', '', array('jquery'), '', true);


    wp_enqueue_style('jstree-style', $jstree_path . 'css/mto-jstree.min.css');
    wp_enqueue_style('mto-admin-style', plugin_dir_url(__FILE__) . 'css/mto-admin.css');

    wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    wp_localize_script(
        'mto-admin-js',
        'mtoData',
        array(
            'categories_json' => mto_get_categories_for_folders(),
        )
    );

}
add_action('admin_enqueue_scripts', 'mto_setup_admin_media');


// Recursive function to fetch and build an array of categories and subcategories to build admin folders
function mto_get_categories_for_folders($parent = 0)
{
    $taxonomy = 'mto_category';
    $args = array(
        'taxonomy' => $taxonomy,
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
                'text' => $term->name . '– (' . count_attachments_in_taxonomy($taxonomy, $term->slug) . ')',
                'children' => [],
                'slug' => $term->slug,
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

function count_attachments_in_taxonomy($taxonomy, $term)
{
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'field' => 'slug',
                'terms' => $term,
            ),
        ),
    );

    $query = new WP_Query($args);

    return $query->found_posts;
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
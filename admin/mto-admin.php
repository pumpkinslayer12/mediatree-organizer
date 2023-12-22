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
    wp_enqueue_script('jquery-ui-resizable', '', array('jquery'), '', true);
    wp_enqueue_script('jquery-ui-draggable', '', array('jquery'), '', true);
    wp_enqueue_script('jquery-ui-droppable', '', array('jquery'), '', true);


    wp_enqueue_style('jstree-style', $jstree_path . 'css/mto-jstree.min.css');
    wp_enqueue_style('mto-admin-style', plugin_dir_url(__FILE__) . 'css/mto-admin.css');

    wp_enqueue_style('jquery-ui', 'https://code.jquery.com/ui/1.12.1/themes/base/jquery-ui.css');

    wp_localize_script(
        'mto-admin-js',
        'mtoData',
        array(
            'categoriesJson' => mto_get_categories_for_folders(
                0,
                mto_get_category_counts(mto_custom_taxonomy_slug())
            ),
            'ajaxURL' => admin_url('admin-ajax.php'),
            'ajaxNonce' => wp_create_nonce('mto_ajax_nonce'),
        )
    );

}
add_action('admin_enqueue_scripts', 'mto_setup_admin_media');

function mto_custom_taxonomy_slug()
{
    return 'mto_category';
}

// Recursive function to fetch and build an array of categories and subcategories to build admin folders
function mto_get_categories_for_folders($parent = 0, $categoryCounts = [])
{
    $taxonomy = mto_custom_taxonomy_slug();
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

            if (array_key_exists($term->slug, $categoryCounts)) {
                $categoryCount = $categoryCounts[$term->slug];
            } else {
                $categoryCount = 0;
            }

            $category = [
                'id' => "mto-" . $term->term_id,
                'text' => $term->name,
                'children' => [],
                'slug' => $term->slug,
                'a_attr' => ['data-count' => $categoryCount]
            ];

            // If the term has children, get them
            if (get_term_children($term->term_id, mto_custom_taxonomy_slug())) {
                $category['children'] = mto_get_categories_for_folders($term->term_id, $categoryCounts);
            }

            $categories[] = $category;
        }
    }

    return $categories;
}

function mto_get_category_counts($taxonomy)
{
    // Get all the terms for the specified taxonomy
    $terms = get_terms(
        array(
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
        )
    );

    // Initialize the result array with zero counts
    $result = array();
    foreach ($terms as $term) {
        $result[$term->slug] = 0;
    }

    // Query all attachments associated with the taxonomy
    $args = array(
        'post_type' => 'attachment',
        'post_status' => 'inherit',
        'tax_query' => array(
            array(
                'taxonomy' => $taxonomy,
                'operator' => 'EXISTS',
            ),
        ),
        'nopaging' => true, // Get all posts without pagination
    );

    $query = new WP_Query($args);

    // Loop through attachments and increase the count for each term
    foreach ($query->posts as $post) {
        $post_terms = wp_get_post_terms($post->ID, $taxonomy);
        foreach ($post_terms as $post_term) {
            $result[$post_term->slug]++;
        }
    }

    return $result;
}

function mto_admin_jstree_ajax_handler()
{
    if (
        !isset($_POST['nonce']) ||
        !wp_verify_nonce($_POST['nonce'], 'mto_ajax_nonce') &&
        !current_user_can('manage_categories')
    ) {
        echo "Nonce verification failed.";
        wp_die();
    }

    $response = [];
    $nodeAction = $_POST['nodeAction'];
    if ($nodeAction === "rename") {
        $response = mto_rename_category(
            $_POST['nodeID'],
            $_POST['newName']
        );
    } elseif ($nodeAction === "delete") {
        $response = mto_delete_category(
            $_POST['nodeID'],
        );
    } elseif ($nodeAction === "create") {
        $response = mto_create_category(
            $_POST['nodeID'],
            $_POST['parentID'],
            $_POST['positionInHiearchy']
        );
    } elseif ($nodeAction === "dragAndDrop") {
        $response = mto_assign_media_to_category(
            $_POST['nodeID'],
            $_POST['mediaID'],
        );

    } elseif ($nodeAction === "moveNode") {
        $response = mto_move_category(
            $_POST['nodeID'],
            $_POST['parentID'],
            $_POST['positionInHiearchy']
        );

    }
    wp_send_json($response);
}

add_action('wp_ajax_admin_jstree_ajax_handler', 'mto_admin_jstree_ajax_handler');

function mto_rename_category($nodeID, $newName)
{
    $nodeID = (int) trim($nodeID, 'mto-');
    $newName = sanitize_text_field($newName);
    $slug = sanitize_title($newName);

    $args = [
        "name" => $newName,
        "slug" => $slug
    ];

    $term = wp_update_term(
        $nodeID,
        mto_custom_taxonomy_slug(),
        $args
    );

    if (is_wp_error($term)) {
        return $term->get_error_message();
    } else {
        return [
            "systemGeneratedNodeID" => "mto-" . $term['term_id'],
            "systemGeneratedSlug" => $slug
        ];
    }

    return $response;
}

function mto_delete_category($nodeID)
{
    $nodeID = (int) trim($nodeID, 'mto-');
    $status = wp_delete_term($nodeID, mto_custom_taxonomy_slug());
    if (is_wp_error($status)) {
        return $status->get_error_message();
    } else {
        return [
            "status" => $status,
        ];
    }
}

function mto_create_category($nodeID, $parentID, $positionInHiearchy)
{
    // This will always be a random jstree id.
    $nodeID = sanitize_text_field($nodeID);
    $parentID = (int) trim($parentID, 'mto-');
    $positionInHiearchy = (int) $positionInHiearchy;

    $termName = "New Folder " . $nodeID;

    $args = [];

    if ($parentID) {
        $args = ['parent' => $parentID];
    }
    $term = wp_insert_term(
        $termName,
        mto_custom_taxonomy_slug(),
        $args
    );

    if (is_wp_error($term)) {
        return $term->get_error_message();
    } else {
        return [
            "systemGeneratedNodeID" => "mto-" . $term['term_id'],
            "originalNodeID" => $nodeID
        ];
    }
}

function mto_move_category($nodeID, $parentID, $positionInHiearchy)
{
    $nodeID = (int) trim($nodeID, 'mto-');
    $parentID = (int) trim($parentID, 'mto-');
    $positionInHiearchy = (int) $positionInHiearchy;
    $args = ['parent' => $parentID];
    $status = wp_update_term(
        $nodeID,
        mto_custom_taxonomy_slug(),
        $args
    );
    if (is_wp_error($status)) {
        return $status->get_error_message();
    } else {
        return [
            "status" => "success",
        ];
    }

}
function mto_assign_media_to_category($nodeID, $mediaID)
{
    $nodeID = (int) trim($nodeID, 'mto-');
    $mediaID = (int) trim($mediaID, 'post-');

    // Get the first old category id, if available

    $previousTerms = get_the_terms($mediaID, mto_custom_taxonomy_slug());

    $previousCategory = !empty($previousTerms) ? 'mto-' . $previousTerms[0]->term_id : "";

    $status = wp_set_object_terms($mediaID, $nodeID, mto_custom_taxonomy_slug());

    if (is_wp_error($status)) {
        return $status->get_error_message();
    } else {


        return [
            "status" => "success",
            "previousCategory" => $previousCategory
        ];
    }


}

function mto_filter_media_library_by_category($query)
{
    // Check if we are in the admin area and it's the main query
    if (
        is_admin() &&
        $query->is_main_query() &&
        $query->get('post_type') == 'attachment'
    ) {
        // Check if the query is for the 'attachment' post type (media items)

        // Check if taxonomy and term are present in the URL
        if (isset($_GET['taxonomy']) && isset($_GET['term'])) {

            if ($_GET['taxonomy'] === 'mto_category') {
                // Get the term slug from the URL
                if (isset($_GET['nodeId']) && (int) $_GET['nodeId'] === -1) {

                    $tax_query = array(
                        array(
                            'taxonomy' => 'mto_category',
                            'operator' => 'NOT EXISTS'
                        ),
                    );


                } else {
                    $term_slug = sanitize_text_field($_GET['term']);

                    // Set the taxonomy query for the custom category
                    $tax_query = array(
                        array(
                            'taxonomy' => 'mto_category',
                            'field' => 'slug',
                            'terms' => $term_slug,
                            'include_children' => false
                        ),
                    );

                    // Modify the query to include media items belonging to the specified category

                }
                $query->set('tax_query', $tax_query);
            }

        }
    }
}

// Hook the function into the pre_get_posts action
add_action('pre_get_posts', 'mto_filter_media_library_by_category');
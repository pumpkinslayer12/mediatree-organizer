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
            'categoriesJson' => mto_get_categories_for_folders(),
            'ajaxURL' => admin_url('admin-ajax.php'),
            'ajaxNonce' => wp_create_nonce('mto_ajax_nonce')
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
                'id' => "mto-" . $term->term_id,
                'text' => $term->name . ' - (' . mto_count_attachments_in_taxonomy($taxonomy, $term->slug) . ')',
                'children' => [],
                'slug' => $term->slug
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

function mto_count_attachments_in_taxonomy($taxonomy, $term)
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
            $_POST['newName'],
            $_POST['previousName']
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

    }
    wp_send_json($response);
}

add_action('wp_ajax_admin_jstree_ajax_handler', 'mto_admin_jstree_ajax_handler');

function mto_rename_category($nodeID, $newName, $previousName)
{
    $nodeID = (int) trim($nodeID, 'mto-');
    $newName = sanitize_text_field($newName);
    //$previousName = sanitize_text_field($previousName);
    $slug = sanitize_title($newName);

    $args = [
        "name" => $newName,
        "slug" => $slug
    ];

    $term = wp_update_term(
        $nodeID,
        'mto_category',
        $args
    );

    if (is_wp_error($term)) {
        return $term->get_error_message();
    } else {
        return [
            "systemGeneratedNodeID" => "mto-" . $term['term_id']
        ];
    }

    return $response;
}

function mto_delete_category($nodeID)
{
    $nodeID = (int) trim($nodeID, 'mto-');
    $status = wp_delete_term($nodeID, 'mto_category');
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

    $termName = "New Folder " . $nodeID;

    $args = [];

    if ($parentID) {
        $args = ['parent' => $parentID];
    }
    $term = wp_insert_term(
        $termName,
        'mto_category',
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
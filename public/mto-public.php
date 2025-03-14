<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link https://github.com/pumpkinslayer12/mediatree-organizer/
 * @since 1.0.0
 *
 * @package MediaTree_Organizer
 * @subpackage MediaTree_Organizer/public
 */
// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

function mto_build_directory_structure($categoryReference = 0)
{
    $taxonomy = 'mto_category';
    $referenceCategoryID = '';

    // Determine the reference category ID
    if (!is_numeric($categoryReference)) {
        $referenceTermByName = get_term_by('name', sanitize_text_field($categoryReference), $taxonomy);
        if ($referenceTermByName) {
            $referenceCategoryID = $referenceTermByName->term_id;
        }
    } else {
        $referenceCategoryID = (int) $categoryReference;
    }

    // Fetch and add media items for the given category
    $categories = add_media_items_to_category($referenceCategoryID, $taxonomy);

    // Build structure for child categories
    $child_categories = build_structure_and_add_media($referenceCategoryID, $taxonomy);
    if (!empty($child_categories)) {
        $categories = array_merge($categories, $child_categories);
    }

    return $categories;
}

// Separate function to fetch and add media items
function add_media_items_to_category($term_id, $taxonomy) {
    $media_items = [];
    $attachments = get_posts([
        'post_type' => 'attachment',
        'tax_query' => [
            [
                'taxonomy' => $taxonomy,
                'field' => 'term_id',
                'terms' => $term_id,
                'include_children' => false
            ],
        ],
        'numberposts' => -1,
    ]);

    foreach ($attachments as $attachment) {
        $media_items[] = [
            'id' => $attachment->ID,
            'text' => esc_html($attachment->post_title),
            'type' => esc_attr(mto_get_icon_class_by_mime_type($attachment->post_mime_type)),
            'icon' => esc_attr(mto_get_icon_class_by_mime_type($attachment->post_mime_type)),
            'a_attr' => [
                'href' => esc_url(wp_get_attachment_url($attachment->ID)),
                'data-last-modified' => "Last Modified: " . esc_attr(get_post_modified_time('F j, Y', false, $attachment->ID)),
                'class' => 'jstree-media-item'
            ]
        ];
    }

    return $media_items;
}

// Separate function to recursively build structure and add media
function build_structure_and_add_media($term_id, $taxonomy) {
    $structure = [];

    $args = array(
        'taxonomy' => $taxonomy,
        'parent' => $term_id,
        'hide_empty' => false,
    );

    $terms = get_terms($args);

    foreach ($terms as $term) {
        $category = [
            'id' => $term->term_id,
            'text' => esc_html($term->name),
            'type' => 'default',
            'icon' => 'folder-icon',
            'children' => add_media_items_to_category($term->term_id, $taxonomy)
        ];

        $child_categories = build_structure_and_add_media($term->term_id, $taxonomy);
        if (!empty($child_categories)) {
            $category['children'] = array_merge($category['children'], $child_categories);
        }

        $structure[] = $category;
    }

    return $structure;
}

function mto_get_icon_class_by_mime_type($mime_type)
{
    if (strstr($mime_type, "video/")) {
        return 'video-icon';
    } elseif (strstr($mime_type, "image/")) {
        return 'image-icon';
    } elseif (strstr($mime_type, "application/pdf")) {
        return 'pdf-icon';
    } else {
        return 'file-icon';
    }
}

// Shortcode to display categories

function mto_display_directory_shortcode($atts)
{

    // Define default values for the attributes
    $atts = shortcode_atts(
        array(
            'category_name' => 0, // Default value if no category name is provided
        ),
        $atts
    );

    // Extract the category_name attribute
    $category_name = $atts['category_name'];
    $jstree_path = plugin_dir_url(__FILE__) . '../includes/';

    wp_enqueue_script('jstree', $jstree_path . 'js/mto-jstree.min.js', array('jquery'), '3.3.8', true);
    wp_enqueue_script('mto-public-js', plugin_dir_url(__FILE__) . 'js/mto_public.js', array('jstree'), '1.0.0', true);

    wp_enqueue_style('jstree-style', $jstree_path . 'css/mto-jstree.min.css');
    wp_enqueue_style('mto-public-style', plugin_dir_url(__FILE__) . 'css/mto-public.css');

    wp_localize_script(
        'mto-public-js',
        'mtoData',
        array(
            'directory_structure' => mto_build_directory_structure($category_name),
        )
    );

    ob_start();
    ?>

    <div class="mto-tree-wrapper">
        <div>
            <input id="search-input" class="mediaviewer-search-input" placeholder="File search" />
        </div>

        <div id="mto-admin-tree">

        </div>

    </div>

    <?php
    return ob_get_clean();
}

add_shortcode('mto_display_folders', 'mto_display_directory_shortcode');
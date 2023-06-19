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

function mto_build_directory_structure($parent = 0)
{
    $args = array(
        'taxonomy' => 'mto_category',
        'parent' => $parent,
        'hide_empty' => false, // Change to 'true' if you want to hide empty categories
    );

    $terms = get_terms($args);
    $categories = [];

    // Check if any term exists
    if (!empty($terms) && is_array($terms)) {
        foreach ($terms as $term) {
            $category = [
                'id' => $term->term_id,
                'text' => $term->name,
                'type' => 'default',
                // Added a 'type' property to specify this is a folder
                'icon' => 'folder-icon',
                // Assign a folder icon class to the folder
                'children' => [],
                'a_attr' => [
                    'href' => get_term_link($term->term_id)
                ]
            ];

            // Query for attachments associated with this term
            $attachments = get_posts([
                'post_type' => 'attachment',
                'tax_query' => [
                    [
                        'taxonomy' => 'mto_category',
                        'field' => 'term_id',
                        'terms' => $term->term_id,
                        'include_children' => false // Exclude attachments from child terms
                    ],
                ],
                'numberposts' => -1, // Get all attachments
            ]);

            // If the term has attachments, add them
            if (!empty($attachments)) {
                foreach ($attachments as $attachment) {
                    $file = [
                        'id' => $attachment->ID,
                        'text' => $attachment->post_title,
                        'type' => mto_get_icon_class_by_mime_type($attachment->post_mime_type),
                        // Use the function to determine the file type
                        'icon' => mto_get_icon_class_by_mime_type($attachment->post_mime_type),
                        // Assign a file icon class to the file based on MIME type
                        'a_attr' => [
                            'href' => wp_get_attachment_url($attachment->ID)
                        ]
                    ];
                    $category['children'][] = $file;
                }
            }

            // If the term has children, get them
            if (get_term_children($term->term_id, 'mto_category')) {
                $child_categories = mto_build_directory_structure($term->term_id);
                // Merge attachments and child categories
                $category['children'] = array_merge($category['children'], $child_categories);
            }

            $categories[] = $category;
        }
    }

    return $categories;
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

function mto_display_directory_shortcode()
{
    $jstree_path = plugin_dir_url(__FILE__) . '../includes/';

    wp_enqueue_script('jstree', $jstree_path . 'js/mto-jstree.min.js', array('jquery'), '3.3.8', true);
    wp_enqueue_script('mto-public-js', plugin_dir_url(__FILE__) . 'js/mto_public.js', array('jstree'), '1.0.0', true);

    wp_enqueue_style('jstree-style', $jstree_path . 'css/mto-jstree.min.css');
    wp_enqueue_style('mto-public-style', plugin_dir_url(__FILE__) . 'css/mto-public.css');

    wp_localize_script(
        'mto-public-js',
        'mtoData',
        array(
            'directory_structure' => mto_build_directory_structure(),
        )
    );

    $searchForm = '<div><input id="search-input" class="mediaviewer-search-input" placeholder="File search"/> </div>';

    return $searchForm . '<div id="mto-admin-tree"></div>';
    ;
}

add_shortcode('mto_display_folders', 'mto_display_directory_shortcode');
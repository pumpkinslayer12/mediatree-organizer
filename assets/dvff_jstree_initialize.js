/**
 * This script initializes and manages the jsTree instance for displaying the directory structure
 * generated by the Directory Viewer for Filebird plugin in WordPress.
 *
 * The directory tree structure and the id for the target HTML element are localized from PHP to JavaScript,
 * allowing the plugin to display different directory trees on different pages or elements.
 */

// Wrap everything inside a jQuery function to prevent conflicts with other scripts.
jQuery(function ($) {

    // Parse the directory tree data from the localized script.
    var tree_data = JSON.parse(dvff_jstree_initialize.tree_data);

    // Initialize the jsTree instance.
    $('#' + dvff_jstree_initialize.id).jstree({

        // Core configuration.
        'core': {
            'dblclick_toggle': false, // Prevent nodes from toggling open/close on double click.
            'themes': {
                'dots': false, // Remove connecting dots between nodes.
                'stripes': true, // Add alternating stripes to the treeview for readability.
            },
            'data': tree_data // Set the tree data to be the parsed directory tree.
        },

        // Node types configuration.
        "types": {
            "default": {
                "icon": "folder-icon" // Use a custom icon for folders.
            },
            "file": {
                "icon": "file-icon" // Use a custom icon for files.
            }
        },

        // Enabled plugins.
        'plugins': ["search", "types"],

        // Search plugin configuration.
        "search": {
            "case_sensitive": false, // Make search case-insensitive.
            "show_only_matches": true // Only show nodes that match the search query.
        }
    });

    // Event handler for when a node is activated (clicked).
    $('#' + dvff_jstree_initialize.id).on('activate_node.jstree', function (e, data) {

        var node = data.node; // The clicked node.

        // Determine whether the clicked node is a folder.
        var isFolder = data.instance.is_parent(node);

        if (isFolder) {
            // Prevent folders from being opened in a new tab.
            e.preventDefault();
        } else {
            // If the node is a file and has a URL, open the URL in a new tab.
            if (node.a_attr && node.a_attr.href) {
                window.open(node.a_attr.href, '_blank');
            }
        }

        // Toggle the open/closed state of the clicked node.
        $('#' + dvff_jstree_initialize.id).jstree('toggle_node', node);
    });

    // Event handler for when a key is pressed in the search input field.
    $(document).ready(function () {
        $(".search-input").keyup(function () {

            // Get the current search string.
            var searchString = $(this).val();

            // Perform a search in the jsTree instance.
            $('#' + dvff_jstree_initialize.id).jstree('search', searchString);
        });
    });

});

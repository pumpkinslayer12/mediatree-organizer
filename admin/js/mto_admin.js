jQuery(document).ready(function ($) {
    if ($('body').hasClass('upload-php')) {
        // Parse the directory tree data from the localized script.
        var tree_data = mtoData.categories_json;
        var tree_anchor_id = "mto-admin-tree";
        var tree_wrapper = $('<div class="mto-tree-wrapper"></div>');
        var tree_container = $('<div id="' + tree_anchor_id + '"></div>');
        var search_field_class = 'mto-jstree-search ';

        var search_field = $('<div class="mto-jstree-search-wrapper"> <input id="search-input" class="' + search_field_class + '" placeholder="Folder search"/> </div>')

        tree_wrapper.prepend(search_field, tree_container);

        $('#wpbody').prepend(tree_wrapper);
        // Initialize the jsTree instance.
        $('#' + tree_anchor_id).jstree({

            // Core configuration.
            'core': {
                'dblclick_toggle': false, // Prevent nodes from toggling open/close on double click.
                'themes': {
                    'dots': false, // Remove connecting dots between nodes.
                    'stripes': false, // Add alternating stripes to the treeview for readability.
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
        $('#' + tree_anchor_id).on('activate_node.jstree', function (e, data) {

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
            $('#' + tree_anchor_id).jstree('toggle_node', node);
        });

        // Event handler for when a key is pressed in the search input field.
        $(document).ready(function () {
            $("." + search_field_class).keyup(function () {

                // Get the current search string.
                var searchString = $(this).val();

                // Perform a search in the jsTree instance.
                $('#' + tree_anchor_id).jstree('search', searchString);
            });
        });
    }

});


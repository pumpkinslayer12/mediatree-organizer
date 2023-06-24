jQuery(document).ready(function ($) {
    // Check if the body has the 'upload-php' class
    if (!$('body').hasClass('upload-php')) return;

    initializeTree();
    setupEventHandlers();


    function initializeTree() {
        const tree_data = mtoData.categories_json;
        const tree_anchor_id = "mto-admin-tree";
        const search_field_class = 'mto-jstree-search ';

        // Create the tree wrapper and container
        const tree_wrapper = $('<div class="mto-tree-wrapper"></div>');
        const tree_container = $('<div id="' + tree_anchor_id + '"></div>');
        const search_field = $('<div class="mto-jstree-search-wrapper"> <input id="search-input" class="' + search_field_class + '" placeholder="Folder search"/> </div>');

        tree_wrapper.prepend(search_field, tree_container);
        $('#wpbody').prepend(tree_wrapper);

        // Initialize the jsTree instance.
        $('#' + tree_anchor_id).jstree({
            'core': {
                'dblclick_toggle': false,
                'themes': {
                    'dots': false,
                    'stripes': false,
                },
                'data': tree_data
            },
            "types": {
                "default": {
                    "icon": "folder-icon"
                },
                "file": {
                    "icon": "file-icon"
                }
            },
            'plugins': ["search", "types"],
            "search": {
                "case_sensitive": false,
                "show_only_matches": true
            }
        });
    }

    function setupEventHandlers() {
        const tree_anchor_id = "mto-admin-tree";
        const search_field_class = 'mto-jstree-search ';

        // Event handler for when a node is activated (clicked).
        $('#' + tree_anchor_id).on('activate_node.jstree', function (e, data) {
            const nodeId = data.node.id; // Get the category ID associated with the clicked node.

            console.log('Node ID:', nodeId); // Log the node ID to the console.

            // Make an AJAX request to the server to fetch the media items associated with this category.
            $.ajax({
                url: mtoData.ajax_url, // Replace with your server endpoint.
                type: 'GET',
                data: {
                    action: 'mto_fetch_media_items',
                    categoryId: nodeId
                },
                success: function (response) {
                    console.log('Media items:', response); // Log the response to the console.

                    // Here you can process the response and update your UI accordingly.
                },
                error: function (error) {
                    console.error('Error fetching media items:', error);
                }
            });
            // Toggle the open/closed state of the clicked node.
            $('#' + tree_anchor_id).jstree('toggle_node', data.node);
        });

        // Event handler for when a key is pressed in the search input field.
        $("." + search_field_class).keyup(function () {
            // Get the current search string.
            const searchString = $(this).val();

            // Perform a search in the jsTree instance.
            $('#' + tree_anchor_id).jstree('search', searchString);
        });
    }
});
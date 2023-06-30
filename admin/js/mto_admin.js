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
        const resizable_wrapper = $('<div class="resizable-wrapper"></div>');

        resizable_wrapper.append(tree_wrapper);
        $('#wpbody').prepend(resizable_wrapper);

        // Initialize the jsTree instance.
        const tree = $('#' + tree_anchor_id).jstree({
            'core': {
                'dblclick_toggle': false,
                "check_callback": true,
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
            'plugins': ["search", "types", "dnd", "contextmenu", "wholerow"],
            "search": {
                "case_sensitive": false,
                "show_only_matches": true
            }
        });

        // Read the node ID from the URL.
        const urlParams = new URLSearchParams(window.location.search);
        const nodeId = urlParams.get('nodeId');

        // When the tree is ready, select the node.
        tree.on('ready.jstree', function () {
            tree.jstree('select_node', nodeId);
            // Add counts to all nodes

        });

    }

    function setupEventHandlers() {
        const tree_anchor_id = "mto-admin-tree";
        const search_field_class = 'mto-jstree-search ';

        // Event handler for when a node is activated (clicked).
        $('#' + tree_anchor_id).on('activate_node.jstree', function (e, data) {
            // Check if the action was triggered by a left-click
            if (data.event && data.event.which === 1) {
                const nodeId = data.node.id; // Get the category ID associated with the clicked node.
                const nodeSlug = data.node.original.slug; // Get the category slug associated with the clicked node.
                // Redirect to a new URL with the category slug as a parameter.
                window.location.href = 'upload.php?taxonomy=mto_category&term=' + nodeSlug + '&nodeId=' + nodeId;
            }
        });

        // Event handler for when a key is pressed in the search input field.
        $("." + search_field_class).keyup(function () {
            // Get the current search string.
            const searchString = $(this).val();

            // Perform a search in the jsTree instance.
            $('#' + tree_anchor_id).jstree('search', searchString);
        });

        $('.resizable-wrapper').resizable({
            handles: 'e',
            minWidth: 250,
            maxWidth: 475,

            resize: function (event, ui) {
                var remainingSpace = $(this).parent().width() - $(this).outerWidth(true),
                    divTwo = $(this).next(),
                    divTwoWidth = remainingSpace - 10; // Subtract the margin

                divTwo.width(divTwoWidth);
            }
        });

        $(window).on('load resize', function () {
            var wpbodyHeight = $('#wpbody').height();
            $('.resizable-wrapper').height(wpbodyHeight);
        });


    }

});
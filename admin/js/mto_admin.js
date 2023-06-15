jQuery(document).ready(function ($) {
    if ($('body').hasClass('upload-php')) {
        var treeContainer = $('<div id="mto-admin-tree"></div>');

        $('#wpbody').prepend(treeContainer);

        // Initialize the jstree
        $('#mto-admin-tree').jstree({
            'core': {
                'data': mtoData.categories_json
            }
        });
    }
});


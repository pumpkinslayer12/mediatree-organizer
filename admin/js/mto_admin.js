jQuery(document).ready(function ($) {
    // Check if the body has the 'upload-php' class
    if (!$('body').hasClass('upload-php')) return;

    let treeAnchorID = "mto-admin-tree";
    let searchClass = 'mto-jstree-search';
    let treeFeature = setupTreeFeature(mtoData.categories_json, treeAnchorID, searchClass);

    let urlParams = new URLSearchParams(window.location.search);
    let lastVisit = urlParams.get('nodeId');

    let baseTree = $(treeFeature).find("#" + treeAnchorID);
    setupTreeNodeSelectSubmission(baseTree);
    selectLastVisit(baseTree, lastVisit);
    setupResizeFeature($('#wpbody'), treeFeature);

    setupDragAndDrop($('#the-list tr'), baseTree);

    function setupTreeFeature(treeData, treeAnchorID, searchClass) {

        let treeWrapper = $('<div class="mto-tree-wrapper"></div>');
        let searchComponent = setupSearch(searchClass);
        let treeComponent = setupTree(treeData, treeAnchorID);
        bindSearchToTree(treeComponent, searchComponent, searchClass);

        treeWrapper.prepend(searchComponent, treeComponent);
        return treeWrapper;

    }
    function setupTree(treeData, treeAnchorID) {
        let treeContainer = $('<div id="' + treeAnchorID + '"></div>');
        treeContainer.jstree({
            'core': {
                'dblclick_toggle': false,
                "check_callback": true,
                'themes': {
                    'dots': false,
                    'stripes': false,
                },
                'data': treeData
            },
            "types": {
                "default": {
                    "icon": "folder-icon"
                },
                "file": {
                    "icon": "file-icon"
                }
            },
            'plugins': ["search", "types", "contextmenu", "wholerow"],
            "search": {
                "case_sensitive": false,
                "show_only_matches": true
            }
        });
        return treeContainer;
    }

    function bindSearchToTree(treeComponent, searchComponent, searchClass) {
        $(searchComponent).find("." + searchClass).keyup(function () {
            // Get the current search string.
            const searchString = $(this).val();

            // Perform a search in the jsTree instance.
            treeComponent.jstree('search', searchString);
        });
    }

    function setupTreeNodeSelectSubmission(treeData) {
        treeData.on('activate_node.jstree', function (e, data) {
            // Check if the action was triggered by a left-click
            if (data.event && data.event.which === 1) {
                const nodeId = data.node.id; // Get the category ID associated with the clicked node.
                const nodeSlug = data.node.original.slug; // Get the category slug associated with the clicked node.
                // Redirect to a new URL with the category slug as a parameter.
                window.location.href = 'upload.php?taxonomy=mto_category&term=' + nodeSlug + '&nodeId=' + nodeId;
            }
        });
    }

    function selectLastVisit(treeData, visitCategory) {

        treeData.on('ready.jstree', function () {
            treeData.jstree('select_node', visitCategory);
        });
    }

    function setupSearch(searchClass) {
        return $('<div class="mto-jstree-search-wrapper"> <input id="search-input" class="' + searchClass + '" placeholder="Folder search"/> </div>');
    }

    function setupResizeFeature(attachingElement, containedElement) {

        let resizeWrapperID = "resizable-wrapper";
        let resizeWrapper = $('<div id="' + resizeWrapperID + '"></div>');
        resizeWrapper.append(containedElement);
        attachingElement.prepend(resizeWrapper);

        resizeWrapper.resizable({
            handles: 'e',
            minWidth: 250,
            maxWidth: 475,

            resize: function (event, ui) {
                let remainingSpace = $(this).parent().width() - $(this).outerWidth(true),
                    divTwo = $(this).next(),
                    divTwoWidth = remainingSpace - 10; // Subtract the margin

                divTwo.width(divTwoWidth);
            }
        });

        $(window).on('load resize', function () {
            let bodyHeight = resizeWrapper.height();
            $('#' + resizeWrapperID).height(bodyHeight);
        });
    }

    function setupDragAndDrop(dragTarget, dropTarget) {

        setupDragFeature(dragTarget);
        setupDropFeature(dropTarget);

    }

    function setupDragFeature(dragTarget) {

        $(dragTarget).draggable({
            helper: function () {
                return $("<div></div>").text('Practice').attr('id', $(this).attr('id')).addClass('drag-helper');
            },
            revert: 'invalid',
            appendTo: 'body'
        });

    }

    function setupDropFeature(dropTarget) {
        dropTarget.on('model.jstree', function (e, data) {
            $.each(data.nodes, function (index, nodeID) {
                var checkExist = setInterval(function () {
                    if ($('#' + nodeID + ' a').length) {
                        clearInterval(checkExist);
                        makeElementDroppable(nodeID);
                    }
                }, 100);
            });
        });
    }

    function makeElementDroppable(elementID) {

        $('#' + elementID + ' a').droppable({
            tolerance: 'pointer',
            drop: function (event, ui) {
                var nodeId = $(this).parent().attr('id');
                var rowID = ui.draggable.attr('id');
                console.log("Row " + rowID + " was placed in tree folder node " + nodeId);
            }
        });

    }

});
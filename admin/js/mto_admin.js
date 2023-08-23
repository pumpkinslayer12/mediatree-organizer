jQuery(document).ready(function ($) {
    // Check if the body has the 'upload-php' class
    if (!$('body').hasClass('upload-php')) return;

    let treeAnchorID = "mto-admin-tree";
    let searchClass = 'mto-jstree-search';
    let treeFeature = setupTreeFeature(mtoData.categoriesJson, treeAnchorID, searchClass);

    let urlParams = new URLSearchParams(window.location.search);
    let lastVisit = urlParams.get('nodeId');

    let baseTree = $(treeFeature).find("#" + treeAnchorID);
    setupTreeNodeSelectSubmission(baseTree);
    selectLastVisit(baseTree, lastVisit);
    setupResizeFeature($('#wpbody'), treeFeature);

    setupDragAndDrop($('#the-list tr'), baseTree);

    const ajaxurl = mtoData.ajaxURL;

    const ajaxSecurityData = {
        action: 'admin_jstree_ajax_handler',
        nonce: mtoData.ajaxNonce
    }

    baseTree
        .on('delete_node.jstree', function (e, data) {
            adminAjaxHandler({ ...ajaxSecurityData, ...deleteNodeDataHandler(data) }, ajaxurl);
        })
        .on('create_node.jstree', function (e, data) {
            adminAjaxHandler({ ...ajaxSecurityData, ...createNodeDataHandler(data) }, ajaxurl)
                .then(response => {
                    baseTree.jstree(true).set_id(data.node, response.systemGeneratedNodeID);
                }).catch(error => {
                    console.error(error);
                });
        })
        .on('rename_node.jstree', function (e, data) {
            adminAjaxHandler({ ...ajaxSecurityData, ...renameNodeDataHandler(data) }, ajaxurl)
                .then(response => {
                    data.node.original.slug = response.systemGeneratedSlug;

                }).catch(error => {
                    console.error(error);
                });
        })
        .on('move_node.jstree', function (e, data) {
            console.log("Move Node Response: ", adminAjaxHandler({ ...ajaxSecurityData, ...moveNodeDataHandler(data) }, ajaxurl));
        });

    $(".jstree-anchor").append("<span>Happy</span>");

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
            'plugins': ["search", "types", "contextmenu3", "wholerow", "dnd"],
            "search": {
                "case_sensitive": false,
                "show_only_matches": true
            },
            'contextmenu': {
                'items': function (node) {
                    var defaultItems = $.jstree.defaults.contextmenu.items();
                    defaultItems.create.label = "Add Folder";
                    defaultItems.rename.label = "Rename Folder";
                    defaultItems.remove.label = "Delete Folder";

                    delete defaultItems.ccp;

                    return defaultItems;
                }
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
                changeUrl(nodeSlug, nodeId);
            }
        });
    }

    function changeUrl(nodeSlug, nodeId) {
        window.location.href = 'upload.php?taxonomy=mto_category&term=' + nodeSlug + '&nodeId=' + nodeId;
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
                return $("<div></div>").text('Media Item').attr('id', $(this).attr('id')).addClass('drag-helper');
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
                let nodeId = $(this).parent().attr('id');
                let rowID = ui.draggable.attr('id');
                console.log("Drop Data in Ajax Request: ", dragAndDropNodeDataHandler(nodeId, rowID));
                adminAjaxHandler({ ...ajaxSecurityData, ...dragAndDropNodeDataHandler(nodeId, rowID) }, ajaxurl)
                    .then(response => {
                        let activationNode = baseTree.jstree(true).get_node(nodeId);
                        let nodeSlug = activationNode.original.slug;
                        changeUrl(nodeSlug, nodeId);
                    }).catch(error => {
                        console.error(error);
                    });
            }
        });

    }

    function createNodeDataHandler(nodeData) {

        return {
            nodeID: nodeData.node.id,
            parentID: nodeData.parent,
            positionInHiearchy: nodeData.position,
            nodeAction: 'create'
        }
    };

    function deleteNodeDataHandler(nodeData) {
        return {
            nodeID: nodeData.node.id,
            parentID: nodeData.parent,
            nodeAction: 'delete'
        }
    }

    function renameNodeDataHandler(nodeData) {
        return {
            nodeID: nodeData.node.id,
            newName: nodeData.text,
            previousName: nodeData.old,
            nodeAction: 'rename'
        }
    }

    function dragAndDropNodeDataHandler(nodeID, mediaID) {
        return {
            nodeID: nodeID,
            mediaID: mediaID,
            nodeAction: "dragAndDrop"
        }

    }

    function moveNodeDataHandler(nodeData) {
        return {
            nodeID: nodeData.node.id,
            parentID: nodeData.parent,
            positionInHiearchy: nodeData.position,
            nodeAction: 'moveNode'
        }
    }
    function adminAjaxHandler(ajaxData, ajaxURL) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: ajaxURL,
                method: 'POST',
                data: ajaxData,
                success: function (response) {
                    console.log('Response:', response); // Debugging line
                    resolve(response);
                },
                error: function () {
                    reject("Error sending response");
                }
            });
        });
    }


});
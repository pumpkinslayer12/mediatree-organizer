jQuery(document).ready(function ($) {
    // Check if the body has the 'upload-php' class
    if (!$('body').hasClass('upload-php')) return;

    let treeAnchorID = "mto-admin-tree";
    let searchClass = 'mto-jstree-search';
    let treeFeature = setupTreeFeature(mtoData.categoriesJson, treeAnchorID, searchClass);

    let urlParams = new URLSearchParams(window.location.search);
    let lastVisit = urlParams.get('nodeId');

    let baseTree = $(treeFeature).find("#" + treeAnchorID);

    let mediaTableView = 'the-list';
    let mediaTableDragTarget = '#the-list tr';
    let mediaGalleryView = 'wp-media-grid';
    let mediaGalleryDragTarget = ".attachment";

    if (document.getElementById(mediaTableView)) {
        setupTreeNodeSelectSubmission(baseTree, 'list');
        setupDragFeature(mediaTableDragTarget);
        setupDropFeature(baseTree, 'id');
        setupRemovalEvents("table");
    }

    else if (document.getElementById(mediaGalleryView)) {
        setupTreeNodeSelectSubmission(baseTree, 'grid');
        setupDragFeature(mediaGalleryDragTarget);
        setupDropFeature(baseTree, 'data-id', 'post-');
        setupRemovalEvents("gallery");
    }

    else {
        console.log("We cannot find the media library on the page");
        return;
    }

    selectLastVisit(baseTree, lastVisit);
    setupResizeFeature($('#wpbody'), treeFeature);

    const ajaxurl = mtoData.ajaxURL;

    const ajaxSecurityData = {
        action: 'admin_jstree_ajax_handler',
        nonce: mtoData.ajaxNonce
    }

    setupTreeNodeEvents(baseTree);

    function setupTreeFeature(treeData, treeAnchorID, searchClass) {

        let treeWrapper = $('<div class="mto-tree-wrapper"></div>');
        let uncategorizedButton = setupButton("uncatagorized-media-items-btn");
        let searchComponent = $('<div class="mto-jstree-search-wrapper"> <input id="search-input" class="' + searchClass + '" placeholder="Folder search"/> </div>');
        let treeComponent = setupTree(treeData, treeAnchorID);
        bindSearchToTree(treeComponent, searchComponent, searchClass);
        treeWrapper.prepend(uncategorizedButton, searchComponent, treeComponent);
        return treeWrapper;

    }

    function setupButton(buttonID) {
        let uncategorizedButton = $('<div class="uncategorized-button"><button id="' + buttonID + '">Uncategorized Media</button></div>');

        uncategorizedButton.click(function () {
            // Call the changeURL function with the parameters you specified
            changeUrl('', -1);
        });

        return uncategorizedButton;

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
            'plugins': ["search", "types", "contextmenu", "wholerow", "dnd"],
            "search": {
                "case_sensitive": false,
                "show_only_matches": true
            },
            'contextmenu': {
                'items': function (node) {
                    let defaultItems = $.jstree.defaults.contextmenu.items();
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

    function setupTreeNodeSelectSubmission(treeData, galleryMode) {
        treeData.on('activate_node.jstree', function (e, data) {
            // Check if the action was triggered by a left-click
            if (galleryMode === 'grid') {
                filterMediaGrid(data)
            }
            else if (galleryMode === 'list') {
                filterTableGrid(data);
            }

        });
    }

    function filterMediaGrid(data) {
        /* Future work*/
    }



    function filterTableGrid(data) {

        if (data.event && data.event.which === 1) {
            const nodeId = data.node.id; // Get the category ID associated with the clicked node.
            const nodeSlug = data.node.original.slug; // Get the category slug associated with the clicked node.
            // Redirect to a new URL with the category slug as a parameter.
            changeUrl(nodeSlug, nodeId);
        }

    }

    function changeUrl(nodeSlug, nodeId) {
        window.location.href = 'upload.php?taxonomy=mto_category&term=' + nodeSlug + '&nodeId=' + nodeId;
    }

    function selectLastVisit(treeData, visitCategory) {

        treeData.on('ready.jstree', function () {
            treeData.jstree('select_node', visitCategory);
        });
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

    function setupDragFeature(dragTarget) {

        // Function to apply draggable functionality
        function applyDraggable(element) {
            element.draggable({
                helper: function () {
                    return $("<div></div>").text('Media Item').addClass('drag-helper');
                },
                revert: 'invalid',
                appendTo: 'body'
            });
        }

        // Apply draggable to existing elements
        $(dragTarget).each(function () {
            applyDraggable($(this));
        });

        // MutationObserver to observe body for added nodes
        let observer = new MutationObserver(function (mutations) {
            mutations.forEach(function (mutation) {
                mutation.addedNodes.forEach(function (node) {
                    let element = $(node);
                    if (element.is(dragTarget)) {
                        applyDraggable(element);
                    }
                });
            });
        });

        // Observer configuration
        let config = { childList: true, subtree: true };

        // Start observing the body for added nodes
        observer.observe(document.body, config);

    }

    function setupDropFeature(dropTarget, attributeValue, attributePrefix = "") {

        dropTarget.on('model.jstree', function (e, data) {
            $.each(data.nodes, function (index, nodeID) {
                let checkExist = setInterval(function () {
                    if ($('#' + nodeID + ' a').length) {
                        clearInterval(checkExist);
                        $('#' + nodeID + ' a').droppable({
                            tolerance: 'pointer',
                            drop: function (event, ui) {
                                // Tree ID
                                let dropItemId = $(this).parent().attr('id');
                                let dragItemId = ui.draggable.attr(attributeValue)
                                let formattedDragItemID = attributePrefix + dragItemId;
                                adminAjaxHandler({ ...ajaxSecurityData, ...dragAndDropNodeDataHandler(dropItemId, formattedDragItemID) }, ajaxurl)
                                    .then(response => {
                                        updateTreeCounts(dropItemId, response.previousCategory);

                                        let mediaRemovalEvent = new CustomEvent('mediaRemovalEvent', {
                                            detail: { mediaitem: dragItemId },
                                            bubbles: true
                                        });

                                        document.body.dispatchEvent(mediaRemovalEvent);

                                    }).catch(error => {
                                        console.error(error);
                                    });
                            }
                        });
                    }
                }, 100);
            });
        });

    }

    function setupRemovalEvents(removalType) {

        if (removalType === 'table') {
            document.body.addEventListener('mediaRemovalEvent', function (event) {
                removeTableMediaItem(event.detail.mediaitem);
            });
        }
        else if (removalType === 'gallery') {
            document.body.addEventListener('mediaRemovalEvent', function (event) {
                removeGalleryMediaItem(event.detail.mediaitem);
            });
        }

    }

    function removeTableMediaItem(mediaItemId) {

        $('#' + mediaItemId).remove();

    }

    function removeGalleryMediaItem(mediaItemId) {
        // Retrieve the media item's Backbone view.
        // This part may vary depending on how the media items are structured in your Backbone application.
        let mediaItemView = wp.media.frame.content.get().collection.get(mediaItemId);

        if (mediaItemView) {
            // Remove the view from its parent collection.
            wp.media.frame.content.get().collection.remove(mediaItemView);
        } else {
            console.error("Media item view not found for ID:", mediaItemId);
        }

    }

    function setupTreeNodeEvents(tree) {

        tree.on('delete_node.jstree', function (e, data) {
            adminAjaxHandler({ ...ajaxSecurityData, ...deleteNodeDataHandler(data) }, ajaxurl);
        })
            .on('create_node.jstree', function (e, data) {
                adminAjaxHandler({ ...ajaxSecurityData, ...createNodeDataHandler(data) }, ajaxurl)
                    .then(response => {
                        tree.jstree(true).set_id(data.node, response.systemGeneratedNodeID);
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
                adminAjaxHandler({ ...ajaxSecurityData, ...moveNodeDataHandler(data) }, ajaxurl);
            });

    }

    function updateTreeCounts(currentCategoryNodeID, previousCategoryNodeID) {

        // Decrement the count for the previous category
        if (previousCategoryNodeID) {
            let previousCategoryNode = $('#' + previousCategoryNodeID + ' a.jstree-anchor');
            let previousCount = parseInt(previousCategoryNode.attr('data-count')) || 0;
            previousCategoryNode.attr('data-count', Math.max(0, previousCount - 1)); // Ensure count doesn't go below 0
        }

        // Increment the count for the current category
        activationNode = $('#' + currentCategoryNodeID + ' a.jstree-anchor');
        let currentCount = parseInt(activationNode.attr('data-count')) || 0;
        activationNode.attr('data-count', currentCount + 1);

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
                    resolve(response);
                },
                error: function () {
                    reject("Error sending response");
                }
            });
        });
    }

});
(function ($) {

    'use strict';

    var ElementPackEditor = {

        init: function () {
            elementor.channels.editor.on('section:activated', ElementPackEditor.onAnimatedBoxSectionActivated);

            window.elementor.on('preview:loaded', function () {
                elementor.$preview[0].contentWindow.ElementPackEditor = ElementPackEditor;
                ElementPackEditor.onPreviewLoaded();
            });
        },


        onPreviewLoaded: function () {
            var elementorFrontend = $('#elementor-preview-iframe')[0].contentWindow.elementorFrontend;

            elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
                // $scope.find('.bdt-elementor-template-edit-link').on('click', function (event) {
                //     window.open($(this).attr('href'));
                // });
            });
        }
    };

    $(window).on('elementor:init', ElementPackEditor.init);

    window.ElementPackEditor = ElementPackEditor;


    elementor.hooks.addFilter("panel/elements/regionViews", function (panel) {

        jQuery(document).ready(function () {
            jQuery('body').append(`<style>.bdt-pro-unlock-icon:after{right: auto !important; left: 5px !important;}</style>`);
        });

        if (ElementPackConfigPromotional.pro_installed || ElementPackConfigPromotional.promotional_widgets <= 0) return panel;

        var promotionalWidgetHandler,
            promotionalWidgets = ElementPackConfigPromotional.promotional_widgets,
            elementsCollection = panel.elements.options.collection,
            categories = panel.categories.options.collection,
            categoriesView = panel.categories.view,
            elementsView = panel.elements.view,
            freeCategoryIndex, proWidgets = [];

        _.each(promotionalWidgets, function (widget, index) {
            elementsCollection.add({
                name: widget.name,
                title: widget.title,
                icon: widget.icon,
                categories: widget.categories,
                editable: false
            })
        });

        elementsCollection.each(function (widget) {
            "element-pack-pro" === widget.get("categories")[0] && proWidgets.push(widget)
        });

        freeCategoryIndex = categories.findIndex({
            name: "element-pack"
        });

        freeCategoryIndex && categories.add({
            name: "element-pack-pro",
            title: "Element Pack Pro",
            defaultActive: !1,
            items: proWidgets
        }, {
            at: freeCategoryIndex + 1
        });

        promotionalWidgetHandler = {

            getWedgetOption: function (name) {
                return promotionalWidgets.find(function (item) {
                    return item.name == name;
                });
            },

            className: function () {
                var className = 'elementor-element-wrapper';

                if (!this.isEditable()) {
                    className += ' elementor-element--promotion';
                }
                return className;
            },

            onMouseDown: function () {
                void this.constructor.__super__.onMouseDown.call(this);
                var promotion = this.getWedgetOption(this.model.get("name"));
                elementor.promotion.showDialog({
                    title: sprintf(wp.i18n.__('%s', 'elementor'), this.model.get("title")),
                    content: sprintf(wp.i18n.__('Use %s widget and dozens more pro features to extend your toolbox and build sites faster and better.', 'elementor'), this.model.get("title")),
                    targetElement: this.el,
                    position: {
                        blockStart: '-7'
                    },
                    actionButton: {
                        url: promotion.action_button.url,
                        text: promotion.action_button.text,
                        classes: promotion.action_button.classes || ['elementor-button', 'elementor-button-success']
                    }
                })
            }
        }

        panel.elements.view = elementsView.extend({
            childView: elementsView.prototype.childView.extend(promotionalWidgetHandler)
        });

        panel.categories.view = categoriesView.extend({
            childView: categoriesView.prototype.childView.extend({
                childView: categoriesView.prototype.childView.prototype.childView.extend(promotionalWidgetHandler)
            })
        });

        return panel;
    })

    // Advanced Google Map - Initialize when document is ready
    $(document).ready(function() {
        initLocationSearch();
    });
    
    // Initialize when widget panel opens (for dynamically created elements)
    if (typeof elementor !== "undefined") {
        elementor.hooks.addAction("panel/open_editor/widget", function() {
            setTimeout(initLocationSearch, 300);
        });
    }
    
    function initLocationSearch() {
        // Remove any existing handlers first to prevent duplicates
        $(document).off("click", ".ep-location-search-btn");
        $(document).off("click", ".ep-modal-close");
        $(document).off("click", ".ep-search-address-button");
        $(document).off("click", ".ep-select-location-button");
        $(document).off("click", ".ep-search-result-item");
        
        // Button click handler
        $(document).on("click", ".ep-location-search-btn", function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            var $btn = $(this);
            var $modal = $btn.next(".ep-location-search-modal");
            var $latField = $btn.closest(".elementor-repeater-row-controls").find("input[data-setting=\"marker_lat\"]");
            var $lngField = $btn.closest(".elementor-repeater-row-controls").find("input[data-setting=\"marker_lng\"]");
            
            console.log("Button clicked", $btn);
            console.log("Modal found", $modal.length);
            console.log("Lat field found", $latField.length);
            console.log("Lng field found", $lngField.length);
            
            // Store references for later use
            $modal.data("latField", $latField);
            $modal.data("lngField", $lngField);
            
            // Clear any previous results
            $modal.find(".ep-search-results").empty();
            $modal.find(".ep-address-search").val("");
            $modal.find(".ep-select-location-button").hide();
            
            // Show the modal
            $modal.fadeIn(200);
            
            // Focus on search input
            $modal.find(".ep-address-search").focus();
        });
        
        // Close button handler
        $(document).on("click", ".ep-modal-close", function() {
            $(this).closest(".ep-location-search-modal").fadeOut(200);
        });
        
        // Close on click outside modal content
        $(document).on("click", ".ep-location-search-modal", function(e) {
            if ($(e.target).hasClass("ep-location-search-modal")) {
                $(this).fadeOut(200);
            }
        });
        
        // Search button handler
        $(document).on("click", ".ep-search-address-button", function() {
            var $modal = $(this).closest(".ep-location-search-modal");
            var address = $modal.find(".ep-address-search").val().trim();
            
            if (address) {
                performSearch(address, $modal);
            }
        });
        
        // Enter key in search input
        $(document).on("keypress", ".ep-address-search", function(e) {
            if (e.which === 13) {
                e.preventDefault();
                var $modal = $(this).closest(".ep-location-search-modal");
                var address = $(this).val().trim();
                
                if (address) {
                    performSearch(address, $modal);
                }
            }
        });
        
        // Select location button handler
        $(document).on("click", ".ep-select-location-button", function() {
            var $modal = $(this).closest(".ep-location-search-modal");
            var selectedLocation = $modal.data("selectedLocation");
            var $latField = $modal.data("latField");
            var $lngField = $modal.data("lngField");
            
            if (selectedLocation && $latField.length && $lngField.length) {
                $latField.val(selectedLocation.lat).trigger("input");
                $lngField.val(selectedLocation.lng).trigger("input");
                $modal.fadeOut(200);
            }
        });
    }
    
    function performSearch(address, $modal) {
        var $results = $modal.find(".ep-search-results");
        $results.html("<p>Searching...</p>");
        $modal.find(".ep-select-location-button").hide();
        
        if (typeof google === "undefined" || typeof google.maps === "undefined") {
            $results.html("<p>Google Maps API not loaded. Please check your API key.</p>");
            return;
        }
        
        var geocoder = new google.maps.Geocoder();
        
        geocoder.geocode({address: address}, function(results, status) {
            $results.empty();
            
            if (status === "OK") {
                if (results.length > 0) {
                    // Create results list
                    var $resultsList = $("<div class=\"ep-search-results-list\" style=\"max-height:200px; overflow-y:auto; margin-bottom:15px; border:1px solid #eee; border-radius:4px;\"></div>");
                    $results.append($resultsList);
                    
                    // Create map container
                    var mapId = "ep-location-preview-map-" + Date.now();
                    var $mapContainer = $("<div id=\"" + mapId + "\" style=\"height:300px; margin-top:15px; border:1px solid #ddd; border-radius:4px;\"></div>");
                    $results.append($mapContainer);
                    
                    // Initialize map
                    var tempMap = new google.maps.Map(document.getElementById(mapId), {
                        zoom: 14,
                        center: results[0].geometry.location
                    });
                    
                    // Add markers for each result
                    results.forEach(function(result, index) {
                        var location = {
                            lat: result.geometry.location.lat(),
                            lng: result.geometry.location.lng(),
                            address: result.formatted_address
                        };
                        
                        // Create result item
                        var $resultItem = $("<div class=\"ep-search-result-item\" style=\"padding:10px; border-bottom:1px solid #eee; cursor:pointer;\"></div>");
                        $resultItem.html("<strong>" + location.address + "</strong><br>" +
                                        "Lat: " + location.lat.toFixed(7) + ", Lng: " + location.lng.toFixed(7));
                        
                        // Add click handler
                        $resultItem.on("click", function() {
                            // Highlight selected item
                            $(".ep-search-result-item").css("background-color", "");
                            $(this).css("background-color", "#f0f0f0");
                            
                            // Store selected location
                            $modal.data("selectedLocation", location);
                            
                            // Center map on selected location
                            tempMap.setCenter(new google.maps.LatLng(location.lat, location.lng));
                            
                            // Show select button
                            $modal.find(".ep-select-location-button").show();
                        });
                        
                        $resultsList.append($resultItem);
                        
                        // Add marker to map
                        var marker = new google.maps.Marker({
                            position: new google.maps.LatLng(location.lat, location.lng),
                            map: tempMap,
                            title: location.address
                        });
                        
                        // Select first result by default
                        if (index === 0) {
                            $resultItem.trigger("click");
                        }
                    });
                } else {
                    $results.html("<p>No results found</p>");
                }
            } else {
                $results.html("<p>Geocode was not successful: " + status + "</p>");
            }
        });
    }

}(jQuery));

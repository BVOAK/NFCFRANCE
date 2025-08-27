;(function ($, elementor) {
    'use strict';

    var widgetAvdGoogleMap = function ($scope, $) {
        var $advancedGoogleMap = $scope.find('.bdt-advanced-gmap'),
            $GmapWrapper = $scope.find('.bdt-advanced-map'),
            map_settings = $advancedGoogleMap.data('map_settings'),
            markers = $advancedGoogleMap.data('map_markers'),
            map_lists = $scope.find('ul.bdt-gmap-lists div.bdt-gmap-list-item'),
            map_search_form = $scope.find('.bdt-search'),
            map_search_text_box = $scope.find('.bdt-search-input'),
            map_form = $scope.find('.bdt-gmap-search-wrapper > form');

        if (!$advancedGoogleMap.length) {
            return;
        }

        if (elementorFrontend.isEditMode()) {
            initMap($GmapWrapper, map_settings, markers, map_lists, map_search_form, map_search_text_box, map_form, $advancedGoogleMap);
        } else {
            window.addEventListener('load', function() {
                initMap($GmapWrapper, map_settings, markers, map_lists, map_search_form, map_search_text_box, map_form, $advancedGoogleMap);
            });
        }
    };

    function createMarkerContent(marker, markerImage) {
        var listMarker = markerImage !== '' ? `<div class="bdt-map-tooltip-top-image"><img class="bdt-map-image" src="${markerImage}" alt="" /></div>` : "";
        var markupWebsite = marker.website !== undefined ? `<a href="${marker.website}">${marker.website}</a>` : '';
        var markupPhone = marker.phone !== undefined ? `<a href="tel:${marker.phone}">${marker.phone}</a>` : '';
        var markupContent = marker.content !== undefined ? `<span class="bdt-tooltip-content">${marker.content}</span><br>` : '';
        var markupPlace = marker.place !== undefined ? `<h5 class="bdt-tooltip-place">${marker.place}</h5>` : '';
        var markupTitle = marker.title !== undefined ? `<h4 class="bdt-tooltip-title">${marker.title}</h4>` : '';
        return `<div class="bdt-map-tooltip-view">
                    <div class="bdt-map-tooltip-view-inner">
                        ${listMarker}
                        <div class="bdt-map-tooltip-bottom-footer">
                            ${markupTitle}
                            ${markupPlace}
                            ${markupContent}
                            ${markupWebsite}
                            ${markupPhone}
                        </div>
                    </div>
                </div>`;
    }

    var initMap = function ($GmapWrapper, map_settings, markers, map_lists, map_search_form, map_search_text_box, map_form, $advancedGoogleMap) {
        $GmapWrapper.removeAttr("style");
        
        // Convert map settings from GMaps format to Google Maps API format
        var mapOptions = {
            center: { lat: parseFloat(map_settings.lat), lng: parseFloat(map_settings.lng) },
            zoom: map_settings.zoom || 15,
            mapTypeId: google.maps.MapTypeId[map_settings.mapTypeId?.toUpperCase()] || google.maps.MapTypeId.ROADMAP,
            zoomControl: map_settings.zoomControl !== undefined ? map_settings.zoomControl : true,
            zoomControlOptions: {
                position: google.maps.ControlPosition.TOP_LEFT
            },
            mapTypeControl: map_settings.mapTypeControl !== undefined ? map_settings.mapTypeControl : true,
            streetViewControl: map_settings.streetViewControl !== undefined ? map_settings.streetViewControl : true,
            scrollwheel: map_settings.scrollwheel !== undefined ? map_settings.scrollwheel : true,
            fullscreenControl: true
        };
        
        // Create the map instance
        var mapEl = $advancedGoogleMap[0];
        var googleMap = new google.maps.Map(mapEl, mapOptions);
        
        // Add markers to the map
        var allMarkers = [];
        var infoWindow = new google.maps.InfoWindow();
        
        for (var i in markers) {
            var markerImage = markers[i].image !== undefined ? markers[i].image : "";
            var markerPosition = { 
                lat: parseFloat(markers[i].lat), 
                lng: parseFloat(markers[i].lng) 
            };
            
            var markerOptions = {
                position: markerPosition,
                map: googleMap,
                title: markers[i].title
            };
            
            if (markers[i].icon) {
                markerOptions.icon = markers[i].icon;
            }
            
            var marker = new google.maps.Marker(markerOptions);
            
            // Add info window to marker
            (function(marker, markerData, markerImage) {
                var content = createMarkerContent(markerData, markerImage);
                
                google.maps.event.addListener(marker, 'click', function() {
                    infoWindow.setContent(content);
                    infoWindow.open(googleMap, marker);
                });
            })(marker, markers[i], markerImage);
            
            allMarkers.push(marker);
        }
        
        // Handle map styles if defined
        if ($advancedGoogleMap.data('map_style')) {
            try {
                var styles = $advancedGoogleMap.data('map_style');
                
                // Check if the styles is already an object (jQuery's data method automatically parses JSON)
                if (typeof styles === 'string') {
                    styles = JSON.parse(styles);
                }
                
                googleMap.setOptions({ styles: styles });
            } catch (e) {
                console.error("Error parsing map styles:", e);
            }
        }
        
        // Geocoding search
        if ($advancedGoogleMap.data('map_geocode')) {
            $(map_form).on('submit', function (e) {
                e.preventDefault();
                var geocoder = new google.maps.Geocoder();
                var address = $(this).find('.bdt-search-input').val().trim();
                
                geocoder.geocode({ 'address': address }, function (results, status) {
                    if (status === 'OK') {
                        var location = results[0].geometry.location;
                        googleMap.setCenter(location);
                        
                        new google.maps.Marker({
                            map: googleMap,
                            position: location
                        });
                    }
                });
            });
        }
        
        // Map list items click handler
        $(map_lists).on("click", function (e) {
            var dataSettings = $(this).data("settings");
            if (!dataSettings) return;
            
            // Center map on the selected location
            var position = { 
                lat: parseFloat(dataSettings.lat), 
                lng: parseFloat(dataSettings.lng) 
            };
            
            googleMap.setCenter(position);
            googleMap.setZoom(map_settings.zoom);
            
            // Create a marker for the selected location
            var markerImage = dataSettings.image !== undefined ? dataSettings.image[0] : "";
            var listMarker = new google.maps.Marker({
                position: position,
                map: googleMap,
                title: dataSettings.title
            });
            
            if (dataSettings.icon) {
                listMarker.setIcon(dataSettings.icon);
            }
            
            // Open info window for the marker
            var content = createMarkerContent(dataSettings, markerImage);
            infoWindow.setContent(content);
            infoWindow.open(googleMap, listMarker);
            
            // Apply map styles to the new view if available
            if ($advancedGoogleMap.data('map_style')) {
                try {
                    var styles = $advancedGoogleMap.data('map_style');
                    
                    // Check if the styles is already an object (jQuery's data method automatically parses JSON)
                    if (typeof styles === 'string') {
                        styles = JSON.parse(styles);
                    }
                    
                    googleMap.setOptions({ styles: styles });
                } catch (e) {
                    console.error("Error parsing map styles:", e);
                }
            }
        });
        
        // Search functionality for lists
        $(map_search_form).on('submit', function (e) {
            e.preventDefault();
            let searchValue = $(map_search_text_box).val().toLowerCase();
            filterMapLists(map_lists, searchValue);
        });
        
        $(map_search_text_box).on('keyup', function () {
            let searchValue = $(this).val().toLowerCase();
            filterMapLists(map_lists, searchValue);
        });
        
        function filterMapLists(listItems, searchValue) {
            $(listItems).filter(function () {
                $(this).toggle($(this).text().toLowerCase().indexOf(searchValue) > -1);
            });
        }
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/bdt-advanced-gmap.default', widgetAvdGoogleMap);
    });
}(jQuery, window.elementorFrontend));

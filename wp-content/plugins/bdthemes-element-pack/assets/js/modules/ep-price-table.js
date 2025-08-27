/**
 * Start price table widget script
 */

( function( $, elementor ) {

	'use strict';

	var widgetPriceTable = function( $scope, $ ) {

		var $priceTable = $scope.find( '.bdt-price-table' ),
            $featuresList = $priceTable.find( '.bdt-price-table-feature-inner' ),
			$settings = $priceTable.data('settings');

        if ( ! $priceTable.length ) {
            return;
        }
		
		if ( $settings.read_more_toggle ) {

			var $read_more = $priceTable.find(".bdt-read-more-features");
			var default_load = $priceTable.find(".bdt-read-more-features").data("bdt-default-load");
			var $ul_listing = $priceTable.find(".bdt-price-table-features-list");

			// Hide list items beyond the default_load limit
			$ul_listing.each(function() {				   
				var $list = $(this); // Cache $(this)
				$list.find("li:gt("+default_load+")").hide();
			});

			$read_more.off("click").on("click", function(e) {
				e.preventDefault();
				var a = $(this),
					$priceTable = a.closest(".bdt-price-table"),
					$ul_listing = $priceTable.find(".bdt-price-table-features-list"),
					$items_to_toggle = $ul_listing.find("li:gt("+default_load+")"),
					$less_text = a.data("bdt-less"),
					$more_text = a.data("bdt-more");

				if (a.hasClass("bdt-more")) {
					// Show items with smooth animation
					$items_to_toggle.each(function(index) {
						var $item = $(this);
						setTimeout(function() {
							$item.slideDown(200);
						}, index * 50); // Staggered animation for each item
					});
					a.text($less_text).addClass("bdt-less").removeClass("bdt-more");
				} else if (a.hasClass("bdt-less")) {
					// Hide items with smooth animation
					$items_to_toggle.slideUp(300);
					a.text($more_text).addClass("bdt-more").removeClass("bdt-less");
				}
			});

		}
					
        var $tooltip = $featuresList.find('> .bdt-tippy-tooltip'),
        	widgetID = $scope.data('id');
		
		$tooltip.each( function( index ) {
			tippy( this, {
				allowHTML: true,
				theme: 'bdt-tippy-' + widgetID
			});				
		});
    };    

	jQuery(window).on('elementor/frontend/init', function() {
		elementorFrontend.hooks.addAction( 'frontend/element_ready/bdt-price-table.default', widgetPriceTable );
		elementorFrontend.hooks.addAction( 'frontend/element_ready/bdt-price-table.bdt-partait', widgetPriceTable );
		elementorFrontend.hooks.addAction( 'frontend/element_ready/bdt-price-table.bdt-erect', widgetPriceTable );
	});

}( jQuery, window.elementorFrontend ) );

/**
 * End price table widget script
 */


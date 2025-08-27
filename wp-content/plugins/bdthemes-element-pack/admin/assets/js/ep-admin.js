jQuery(document).ready(function ($) {

    if (jQuery('.wrap').hasClass('element-pack-dashboard')) {

        // modules
        var moduleUsedWidget = jQuery('#element_pack_active_modules_page').find('.ep-used-widget');
        var moduleUsedWidgetCount = jQuery('#element_pack_active_modules_page').find('.ep-options .ep-used').length;
        moduleUsedWidget.text(moduleUsedWidgetCount);
        var moduleUnusedWidget = jQuery('#element_pack_active_modules_page').find('.ep-unused-widget');
        var moduleUnusedWidgetCount = jQuery('#element_pack_active_modules_page').find('.ep-options .ep-unused').length;
        moduleUnusedWidget.text(moduleUnusedWidgetCount);

        // 3rd party
        var thirdPartyUsedWidget = jQuery('#element_pack_third_party_widget_page').find('.ep-used-widget');
        var thirdPartyUsedWidgetCount = jQuery('#element_pack_third_party_widget_page').find('.ep-options .ep-used').length;
        thirdPartyUsedWidget.text(thirdPartyUsedWidgetCount);
        var thirdPartyUnusedWidget = jQuery('#element_pack_third_party_widget_page').find('.ep-unused-widget');
        var thirdPartyUnusedWidgetCount = jQuery('#element_pack_third_party_widget_page').find('.ep-options .ep-unused').length;
        thirdPartyUnusedWidget.text(thirdPartyUnusedWidgetCount);
        
        // Add scroll-to-top functionality for all tab navigation clicks
        jQuery(document).on('click', '.bdt-dashboard-navigation a, .bdt-tab a, .bdt-tab-item, .ep-widget-filter a, .bdt-subnav a', function() {
            // Scroll to top smoothly when any tab or navigation link is clicked
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });
        
        // Handle WordPress admin sub menu clicks
        jQuery(document).on('click', '#adminmenu .wp-submenu a, .toplevel_page_element_pack_options .wp-submenu a', function() {
            var href = jQuery(this).attr('href');
            // Only scroll to top if it's an Element Pack related link
            if (href && (href.includes('element_pack') || href.includes('#'))) {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }
        });
        
        // Also handle hash change events to scroll to top
        jQuery(window).on('hashchange', function() {
            // Small delay to ensure tab content is loaded before scrolling
            setTimeout(function() {
                window.scrollTo({
                    top: 0,
                    behavior: 'smooth'
                });
            }, 100);
        });
    }

    jQuery('.element-pack-notice.notice-error img').css({
        'margin-right': '8px',
        'vertical-align': 'middle'
    });

    // Variations swatches
    const variationSwatchesBtn = jQuery(".ep-feature-option-parent");
    const variationDependentOptions = variationSwatchesBtn.length > 0 
        ? variationSwatchesBtn.closest(".ep-option-item").nextAll()
        : jQuery('.ep-option-item[class*="ep-ep_variation_swatches_"]');
    
    const toggleVariationOptions = function() {
        if (variationSwatchesBtn.length > 0 && variationSwatchesBtn.prop("checked")) {
            variationDependentOptions.fadeIn(250);
        } else {
            variationDependentOptions.hide();
        }
    };
    
    toggleVariationOptions();
    
    if (variationSwatchesBtn.length > 0) {
        variationSwatchesBtn.on("change", toggleVariationOptions);
    }
    
    jQuery("#bdt-element_pack_other_settings").on("click", toggleVariationOptions);

    //End Variations swatches

});
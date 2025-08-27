/**
 * Start Content Switcher widget script
 */

(function ($, elementor) {

    'use strict';

    var widgetContentSwitcher = function ($scope, $) {

        var $contentSwitcher = $scope.find('.bdt-content-switcher'),
            $settings = $contentSwitcher.data('settings'),
            $linkedSections = $contentSwitcher.data('linked-sections'),
            $linkedWidgets = $contentSwitcher.data('linked-widgets'),
            editMode = Boolean(elementorFrontend.isEditMode());

        if (!$contentSwitcher.length) {
            return;
        }

        // Handle linked sections if needed
        if ($linkedSections !== undefined && editMode === false) {
            const handleLinkedSections = () => {
                var $sections = $linkedSections.sections;
                
                // Process each linked section
                Object.entries($sections).forEach(([index, sectionId]) => {
                    var $switcherContainer = $contentSwitcher.find('.bdt-switcher-content').eq(index),
                        $sectionContent = $('.elementor').find('.elementor-element' + '#' + sectionId);
                    
                    if ($linkedSections.positionUnchanged !== true) {
                        if ($switcherContainer.length && $sectionContent.length) {
                            $($sectionContent).appendTo($switcherContainer.find('.bdt-switcher-item-content-section'));
                        }
                    } else {
                        // Handle position unchanged - similar to the switcher widget
                        var $activeClass = '';
                        if (index == 0 && $contentSwitcher.find('.bdt-primary').hasClass('bdt-active') ||
                            index > 0 && $contentSwitcher.find(`.bdt-switcher-content:eq(${index})`).hasClass('bdt-active')) {
                            $activeClass = 'bdt-active';
                        }
                        
                        if (!$(`#bdt-content-switcher-section-${$linkedSections.id}`).length) {
                            $sectionContent.parent().append(`<div id="bdt-content-switcher-section-${$linkedSections.id}" class="bdt-switcher bdt-switcher-section-content"></div>`);
                        }
                        
                        $($sectionContent).appendTo($(`#bdt-content-switcher-section-${$linkedSections.id}`));
                        $sectionContent.wrap(`<div class="bdt-switcher-section-content-inner ${$activeClass}"></div>`);
                    }
                });
            };
            
            handleLinkedSections();
        }

        // Handle linked widgets if needed
        if ($linkedWidgets !== undefined && editMode === false) {
            const handleLinkedWidgets = () => {
                var $widgets = $linkedWidgets.widgets;
                
                // Set initial visibility of widgets
                Object.entries($widgets).forEach(([index, widgetId]) => {
                    var $targetWidget = $('#' + widgetId),
                        isActive = false;
                        
                    if ('button' !== $settings.switcherStyle) {
                        if (index == 0 && $contentSwitcher.find('.bdt-primary').hasClass('bdt-active')) {
                            isActive = true;
                        } else if (index == 1 && $contentSwitcher.find('.bdt-secondary').hasClass('bdt-active')) {
                            isActive = true;
                        }
                    } else {
                        if ($contentSwitcher.find(`.bdt-switcher-content:eq(${index})`).hasClass('bdt-active')) {
                            isActive = true;
                        }
                    }
                    
                    $targetWidget.css({
                        'opacity': isActive ? 1 : 0,
                        'display': isActive ? 'block' : 'none',
                        'grid-row-start': 1,
                        'grid-column-start': 1
                    });
                    
                    $targetWidget.parent().css({
                        'display': 'grid'
                    });
                });
            };
            
            handleLinkedWidgets();
        }

        if ('button' !== $settings.switcherStyle) {

            // Content Switcher Checkbox
            var $checkbox = $contentSwitcher.find('input[type="checkbox"]');
            var primarySwitcher = $contentSwitcher.find('.bdt-primary-switcher');
            var secondarySwitcher = $contentSwitcher.find('.bdt-secondary-switcher');
            var primaryIcon = $contentSwitcher.find('.bdt-primary-icon');
            var secondaryIcon = $contentSwitcher.find('.bdt-secondary-icon');
            var primaryText = $contentSwitcher.find('.bdt-primary-text');
            var secondaryText = $contentSwitcher.find('.bdt-secondary-text');
            var primaryContent = $contentSwitcher.find('.bdt-switcher-content.bdt-primary');
            var secondaryContent = $contentSwitcher.find('.bdt-switcher-content.bdt-secondary');

            $checkbox.on('change', function () {
                if (this.checked) {
                    primarySwitcher.removeClass('bdt-active');
                    secondarySwitcher.addClass('bdt-active');
                    primaryIcon.removeClass('bdt-active');
                    secondaryIcon.addClass('bdt-active');
                    primaryText.removeClass('bdt-active');
                    secondaryText.addClass('bdt-active');
                    primaryContent.removeClass('bdt-active');
                    secondaryContent.addClass('bdt-active');
                    
                    // Update linked sections if position unchanged is true
                    if ($linkedSections && $linkedSections.positionUnchanged === true) {
                        $(`#bdt-content-switcher-section-${$linkedSections.id} .bdt-switcher-section-content-inner`).removeClass('bdt-active');
                        $(`#bdt-content-switcher-section-${$linkedSections.id} .bdt-switcher-section-content-inner`).eq(1).addClass('bdt-active');
                    }
                    
                    // Update linked widgets visibility
                    if ($linkedWidgets) {
                        Object.entries($linkedWidgets.widgets).forEach(([index, widgetId]) => {
                            var $targetWidget = $('#' + widgetId);
                            var isActive = index == 1; // Show second widget when checkbox is checked
                            
                            $targetWidget.css({
                                'opacity': isActive ? 1 : 0,
                                'display': isActive ? 'block' : 'none'
                            });
                        });
                    }
                } else {
                    primarySwitcher.addClass('bdt-active');
                    secondarySwitcher.removeClass('bdt-active');
                    primaryIcon.addClass('bdt-active');
                    secondaryIcon.removeClass('bdt-active');
                    primaryText.addClass('bdt-active');
                    secondaryText.removeClass('bdt-active');
                    primaryContent.addClass('bdt-active');
                    secondaryContent.removeClass('bdt-active');
                    
                    // Update linked sections if position unchanged is true
                    if ($linkedSections && $linkedSections.positionUnchanged === true) {
                        $(`#bdt-content-switcher-section-${$linkedSections.id} .bdt-switcher-section-content-inner`).removeClass('bdt-active');
                        $(`#bdt-content-switcher-section-${$linkedSections.id} .bdt-switcher-section-content-inner`).eq(0).addClass('bdt-active');
                    }
                    
                    // Update linked widgets visibility
                    if ($linkedWidgets) {
                        Object.entries($linkedWidgets.widgets).forEach(([index, widgetId]) => {
                            var $targetWidget = $('#' + widgetId);
                            var isActive = index == 0; // Show first widget when checkbox is unchecked
                            
                            $targetWidget.css({
                                'opacity': isActive ? 1 : 0,
                                'display': isActive ? 'block' : 'none'
                            });
                        });
                    }
                }
            });
        }        

        if ('button' == $settings.switcherStyle) {
            var $tab = $contentSwitcher.find('.bdt-content-switcher-tab');

            $tab.on('click', function () {
                var $this = $(this);
                var id = $this.attr('id');
                var $content = $contentSwitcher.find('.bdt-switcher-content[data-content-id="' + id + '"]');
                var index = $this.index();

                $this.siblings().removeClass('bdt-active');
                $this.addClass('bdt-active');

                $this.parent().next().children().removeClass('bdt-active');
                $content.addClass('bdt-active');
                
                // Update linked sections if position unchanged is true
                if ($linkedSections && $linkedSections.positionUnchanged === true) {
                    $(`#bdt-content-switcher-section-${$linkedSections.id} .bdt-switcher-section-content-inner`).removeClass('bdt-active');
                    $(`#bdt-content-switcher-section-${$linkedSections.id} .bdt-switcher-section-content-inner`).eq(index).addClass('bdt-active');
                }
                
                // Update linked widgets visibility
                if ($linkedWidgets) {
                    Object.entries($linkedWidgets.widgets).forEach(([widgetIndex, widgetId]) => {
                        var $targetWidget = $('#' + widgetId);
                        var isActive = parseInt(widgetIndex) === index;
                        
                        $targetWidget.css({
                            'opacity': isActive ? 1 : 0,
                            'display': isActive ? 'block' : 'none'
                        });
                    });
                }
            });            
        }
    }

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/bdt-content-switcher.default', widgetContentSwitcher);
    });

}(jQuery, window.elementorFrontend));

/**
 * End Content Switcher widget script
 */
;(function ($, elementor) {
    $(window).on('elementor/frontend/init', function () {
        var ModuleHandler = elementorModules.frontend.handlers.Base;

        var BackgroundImageParallaxHandler = ModuleHandler.extend({

            bindEvents: function () {
                this.run();
            },

            getDefaultSettings: function () {
                return {
                    orientation: 'left', // Default orientation
                };
            },

            onElementChange: debounce(function (prop) {
                if (prop.indexOf('ep_background_image_parallax_') !== -1) {
                    this.run();
                }
            }, 400),

            settings: function (key) {
                return this.getElementSettings('ep_background_image_parallax_' + key);
            },

            run: function () {
                var options = this.getDefaultSettings();
                var widgetID = this.$element.data('id');

                var images = document.querySelectorAll('.elementor-element-' + widgetID + '.bdt-background-image-parallax-yes img');

                // Update options if settings exist
                if (this.settings('orientation')) {
                    options.orientation = this.settings('orientation');
                }
                //scale 
                if (this.settings('scale.size')) {
                    options.scale = this.settings('scale.size');
                }
                if (this.settings('delay.size')) {
                    options.delay = this.settings('delay.size');
                }
                //transition
                // if (this.settings('transition')) {
                //     options.transition = this.settings('transition') || 'cubic-bezier(0,0,0,1)';
                // }
                //max_transition
                // if (this.settings('max_transition')) {
                //     options.maxTransition = this.settings('max_transition') || 0;
                // }

                //overflow
                if (this.settings('overflow') === 'yes') {
                    options.overflow = true;
                } else {
                    options.overflow = false;
                }

                // Apply SimpleParallax to images
                if (images.length) {
                    new SimpleParallax(images, options);
                }
            },
        });

        // Add the handler to Elementor widgets
        elementorFrontend.hooks.addAction('frontend/element_ready/widget', function ($scope) {
            elementorFrontend.elementsHandler.addHandler(BackgroundImageParallaxHandler, {
                $element: $scope,
            });
        });
    });
})(jQuery, window.elementorFrontend);


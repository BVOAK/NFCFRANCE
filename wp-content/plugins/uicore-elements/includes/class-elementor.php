<?php

namespace UiCoreElements;

/**
 * Scripts and Styles Class
 */
class Elementor
{
    public function __construct()
    {
        $this->init_utils();

        require_once UICORE_ELEMENTS_INCLUDES . '/class-widget-base.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/class-nested-widget-base.php';
        if (!class_exists('\UiCoreAnimate\Base')) {
            require_once UICORE_ELEMENTS_INCLUDES . '/class-animate.php';
        }


        add_action('elementor/elements/categories_registered', [$this, 'create_custom_category'], 999);
        add_action('elementor/widgets/widgets_registered', [$this, 'extend_widgets'], 999);
        add_action('elementor/controls/register', [$this, 'init_controls']);
        add_action('elementor/widgets/register', [$this, 'init_widgets']);
    }

    public function init_widgets()
    {
        // Generic widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/highlighted-text.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/icon-list.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/counter.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/icon-box.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/accordion.php';

        // Gallery Widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/gallery-grid.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/gallery-carousel.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/gallery-slider.php';

        // Post Widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/advanced-post-grid.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/advanced-post-carousel.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/advanced-product-grid.php';

        // Testimonial widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/testimonial-grid.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/testimonial-carousel.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/testimonial-slider.php';

        // Logo widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/logo-grid.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/logo-carousel.php';

        // Form widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/contact-form.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/newsletter.php';

        // Nested feature widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/tabs.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/custom-carousel.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/custom-slider.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/custom-table.php';

        // Theme Builder widgets
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/theme-builder/the-content.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/theme-builder/the-title.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/theme-builder/post-meta.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/theme-builder/page-description.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/widgets/theme-builder/post-featured-image.php';

        // Dependent on Uicore Framework
        if (defined('UICORE_ASSETS')) {
            require_once UICORE_ELEMENTS_INCLUDES . '/widgets/post-grid.php';
        }
    }


    public function init_controls()
    {
        require UICORE_ELEMENTS_INCLUDES . '/controls/class-post-filter-control.php';
        require UICORE_ELEMENTS_INCLUDES . '/controls/class-product-filter-control.php';
    }

    public function init_utils()
    {
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/grid-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/carousel-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/testimonial-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/logo-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/gallery-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/animation-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/meta-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/pagination-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/post-filter-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/item-style-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/post-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/product-component.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/form-service.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/newsletter-services.php';
        require_once UICORE_ELEMENTS_INCLUDES . '/utils/form-component.php';
    }

    public function extend_widgets()
    {
        require UICORE_ELEMENTS_INCLUDES . '/controls/class-lightbox-button.php';
    }

    function create_custom_category($elements_manager)
    {
        $elements_manager->add_category('uicore', [
            'title' => __('UiCore', 'uicore-elements'),
            'icon' => 'fa fa-plug',
        ]);
    }
}

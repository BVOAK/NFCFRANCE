<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;

defined('ABSPATH') || exit();

/**
 * Gallery Slider
 *
 * Use Gallery Carousel as base
 *
 * @author Lucas Marini Falbo <lucas95@uicore.co>
 * @since 1.0.14
 */

class GallerySlider extends GalleryCarousel
{
    public function get_name()
    {
        return 'uicore-gallery-slider';
    }
    public function get_title()
    {
        return esc_html__('Gallery Slider', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-slides ui-e-widget';
    }
    public function get_styles()
    {
        $styles = parent::get_styles();

        // replace 'gallery-carousel' for 'gallery-slider'
        unset($styles['gallery-carousel']);
        $styles[] = 'gallery-slider';

        return $styles;
    }
    // TODO: remove after Optmized Markup experiment is merged to the core
    public function has_widget_inner_wrapper(): bool
    {
        return true;
    }
    protected function register_controls()
    {
        parent::register_controls();
        $this->TRAIT_update_slider_controls();

        // Remove conditions from image height and image style section
        $this->update_responsive_control(
            'image_height',
            [
                'conditions' => false,
            ]
        );
        $this->update_responsive_control(
            'style_image_section',
            [
                'conditions' => false,
            ]
        );

        // Remove item active state styles
        $this->remove_control('tab_item_active');
        $this->remove_control('item_active_background');
        $this->remove_control('item_active_border_color');
        $this->remove_control('item_active_box_shadow');

        // Remove controls that are meant for carousel, not slide type widgets
        $this->remove_control('main_image');

        // Add vertical content alignment control
        $this->start_injection([
            'of' => 'layout',
            'at' => 'after',
        ]);

        $this->add_responsive_control(
            'content_v_alignment',
            [
                'label'     => __('Vertical Alignment', 'uicore-elements'),
                'type'      => Controls_Manager::CHOOSE,
                'default' => 'start',
                'options'   => [
                    'start'    => [
                        'title' => __('Start', 'uicore-elements'),
                        'icon'  => 'eicon-align-start-v',
                    ],
                    'center'  => [
                        'title' => __('Center', 'uicore-elements'),
                        'icon'  => 'eicon-align-center-v',
                    ],
                    'end'   => [
                        'title' => __('Bottom', 'uicore-elements'),
                        'icon'  => 'eicon-align-end-v',
                    ],
                ],
                'condition' => [
                    'layout' => 'ui-e-overlay'
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-content' => 'justify-content: {{VALUE}};',
                ]
            ]
        );

        $this->end_injection();
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new GallerySlider());

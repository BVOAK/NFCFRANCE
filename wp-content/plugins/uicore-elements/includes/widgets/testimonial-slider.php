<?php

namespace UiCoreElements;

defined('ABSPATH') || exit();

/**
 * Testimonial Slider
 *
 * Use Testimonial Carousel as base
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.1
 */

class TestimonialSlider extends TestimonialCarousel
{
    public function get_name()
    {
        return 'uicore-testimonial-slider';
    }
    public function get_title()
    {
        return esc_html__('Testimonial Slider', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-testimonial ui-e-widget';
    }
    public function get_keywords()
    {
        return ['testimonial', 'review', 'services', 'cards', 'box', 'client', 'slider'];
    }
    public function get_styles()
    {
        $styles = parent::get_styles();

        // replace 'testimonial-carousel' for 'testimonial-slider'
        unset($styles['testimonial-carousel']);
        $styles[] = 'testimonial-slider';

        return $styles;
    }
    protected function register_controls()
    {

        parent::register_controls();
        $this->TRAIT_update_slider_controls();

        // Alignment adjustment
        $this->update_control(
            'h_alignment',
            [
                'default' => 'center'
            ]
        );
        // Default avatar size with one slide visible per time is too big, needs a decrease
        $this->update_control(
            'avatar_size',
            [
                'devices' => ['desktop', 'tablet', 'mobile'],
                'default' => [
                    'size' => 10,
                    'unit' => '%'
                ],
                'tablet_default' => [
                    'size' => 15,
                    'unit' => '%'
                ],
                'mobile_default' => [
                    'size' => 40,
                    'unit' => '%'
                ],
            ]
        );
        // Change default border radius to zero
        $this->update_control(
            '
            item_border_radius',
            [
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
            ]
        );
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new TestimonialSlider());

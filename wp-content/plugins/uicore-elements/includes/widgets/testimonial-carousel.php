<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Carousel_Trait;
use UiCoreElements\Utils\Testimonial_Trait;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Item_Style_Component;

defined('ABSPATH') || exit();

/**
 * Testimonial Carousel
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.1
 */

class TestimonialCarousel extends UiCoreWidget
{

    use Carousel_Trait;
    use Testimonial_Trait;
    use Animation_Trait;
    use Item_Style_Component;

    public function get_name()
    {
        return 'uicore-testimonial-carousel';
    }
    public function get_title()
    {
        return esc_html__('Testimonial Carousel', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-testimonial ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['testimonial', 'review', 'services', 'cards', 'box', 'client', 'carousel'];
    }
    public function get_styles()
    {
        $styles = [
            'testimonial-carousel',
            'carousel',
            'animation', // hover animations
            'entrance', // entrance basic style
        ];
        if (!class_exists('\UiCore\Core') && !class_exists('\UiCoreAnimate\Base')) {
            $styles['e-animations'] = [ // entrance animations
                'external' => true,
            ];
        }
        return $styles;
    }
    public function get_scripts()
    {
        $scripts = [
            'testimonial' => [
                'condition' => [
                    'layout' => 'layout_5'
                ]
            ]
        ];
        return array_merge($scripts, $this->TRAIT_get_carousel_scripts());
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }
    protected function register_controls()
    {

        $this->TRAIT_register_testimonial_repeater_controls('Testimonial Carousel Items'); // Repeater Controls

        // Additional Carousel Controls
        $this->start_controls_section(
            'section_review_additional_settings',
            [
                'label' => __('Additional Settings', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->TRAIT_register_specific_testimonial_controls('layout');
        $this->TRAIT_register_carousel_additional_controls(); // Carousel Additionals
        $this->TRAIT_register_testimonial_additional_controls(); // Testimonial Additionals

        $this->end_controls_section();

        $this->start_controls_section(
            'section_carousel_settings',
            [
                'label' => __('Carousel Settings', 'uicore-elements'),
            ]
        );

        $this->TRAIT_register_carousel_settings_controls(); // Carousel settings

        $this->end_controls_section();

        $this->TRAIT_register_navigation_controls(); // Navigation settings

        $this->start_controls_section(
            'section_style_review_items',
            [
                'label'     => esc_html__('Items', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        // move it to specific controls
        $this->add_control(
            'item_gap',
            [
                'label' => esc_html__('Inner Content Spacing', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'size_units' => ['px', '%', 'em'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                        'step' => 1,
                    ],
                    '%' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 15,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-testimonial-flex' => 'margin-bottom: {{SIZE}}{{UNIT}};',
                ],
                'conditions' => [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'image_inline',
                            'operator' => '===',
                            'value' => 'yes',
                        ],
                        [
                            'name' => 'layout',
                            'operator' => 'in',
                            'value' => ['layout_2'],
                        ],
                    ],
                ],
            ]
        );
        $this->TRAIT_register_all_item_style_controls();

        $this->end_controls_section();

        $this->TRAIT_register_style_controls(); // All Testimonial components styles
        $this->TRAIT_register_navigation_style_controls(); // Carousel Navigation Styles

        $this->start_controls_section(
            'section_style_animations',
            [
                'label' => __('Animations', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_testimonial_animation_controls();

        $this->end_controls_section();

        // We need to set border radius as variable to use item radius on other components on swiper elements
        $this->update_control('item_border_radius', [
            'selectors' => [
                '{{WRAPPER}} .ui-e-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                '{{WRAPPER}}' => '--ui-e-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};'
            ],
        ]);
    }
    public function render()
    {
        $settings = $this->get_settings_for_display();
        $total_slides = count($settings['review_items']);

?>
        <div class="ui-e-carousel swiper">
            <div class='swiper-wrapper'>
                <?php
                foreach ($settings['review_items'] as $index => $item) :
                    $this->TRAIT_render_review_item($item, $settings['layout']);
                endforeach;

                // Most recent swiper versions requires, if loop, at least one extra slide compared to visible slides
                if ($this->TRAIT_should_duplicate_slides($total_slides)) {
                    $diff = $this->TRAIT_get_duplication_diff($total_slides);
                    for ($i = 0; $i <= $diff; $i++) {
                        $index = $i % $total_slides; // Ensure the index does not surpass the maximum index
                        $this->TRAIT_render_review_item($settings['review_items'][$index], $settings['layout']);
                    }
                }
                ?>
            </div>
        </div>
        <?php $this->TRAIT_render_carousel_navigations(); ?>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new TestimonialCarousel());

<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Testimonial_Trait;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Grid_Trait;
use UicoreElements\Utils\Item_Style_Component;

use function PHPSTORM_META\map;

defined('ABSPATH') || exit();

/**
 * Testimonial Grid
 *
 * @author Lucas Marini Falbo <lucas95@uicore.co>
 * @since 1.0.1
 */

class TestimonialGrid extends UiCoreWidget
{
    use Testimonial_Trait;
    use Animation_Trait;
    use Grid_Trait;
    use Item_Style_Component;

    public function get_name()
    {
        return 'uicore-testimonial-grid';
    }
    public function get_title()
    {
        return esc_html__('Testimonial Grid', 'uicore-elements');
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
        return ['testimonial', 'review', 'services', 'cards', 'box', 'features', 'client', 'grid'];
    }
    public function get_styles()
    {
        $styles = [
            'testimonial-grid',
            'grid',
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
        return [
            'testimonial' => [
                'condition' => [
                    'layout' => 'layout_5'
                ],
            ],
            'entrance' => [
                'condition' => [
                    'animate_items' => 'ui-e-grid-animate'
                ],
            ]
        ];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }
    protected function register_controls()
    {

        $this->TRAIT_register_testimonial_repeater_controls('Testimonial Grid Items'); // Repeater Controls

        $this->start_controls_section( // Specific Additional Grid Controls
            'section_review_additional_settings',
            [
                'label' => __('Additional Settings', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->TRAIT_register_specific_testimonial_controls('layout');
        $this->TRAIT_register_grid_layout_controls();
        $this->TRAIT_register_testimonial_additional_controls(); // Content Additional Controls

        $this->end_controls_section();

        $this->start_controls_section(
            'section_style_review_items',
            [
                'label'     => esc_html__('Items', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_all_item_style_controls(false);

        $this->end_controls_section();

        $this->TRAIT_register_style_controls(); // Other Components Style Controls

        $this->start_controls_section(
            'section_style_animations',
            [
                'label' => __('Animations', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_testimonial_animation_controls();

        $this->end_controls_section();
    }
    public function render()
    {
        $settings = $this->get_settings_for_display();
        $layout   = $settings['layout'];
        $masonry  = $this->is_option('masonry', 'yes')
            ? 'ui-e-maso'
            : '';

        $this->add_render_attribute('review-card', 'class', ['ui-e-grid', $masonry]);
?>
        <div <?php $this->print_render_attribute_string('review-card'); ?>>
            <?php foreach ($settings['review_items'] as $index => $item) : ?>
                <?php $this->TRAIT_render_review_item($item, $layout); ?>
            <?php endforeach; ?>
        </div>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new TestimonialGrid());

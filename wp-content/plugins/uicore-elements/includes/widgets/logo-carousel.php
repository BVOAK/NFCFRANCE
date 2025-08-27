<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Carousel_Trait;
use UiCoreElements\Utils\Logo_Trait;
use UiCoreElements\Utils\Animation_Trait;
use UicoreElements\Utils\Item_Style_Component;

defined('ABSPATH') || exit();

/**
 * Logo Carousel
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.1
 */

class LogoCarousel extends UiCoreWidget
{

    use Carousel_Trait;
    use Logo_Trait;
    use Animation_Trait;
    use Item_Style_Component;

    public function get_name()
    {
        return 'uicore-logo-carousel';
    }
    public function get_title()
    {
        return esc_html__('Logo Carousel', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-logo ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['logo', 'client', 'brand', 'showcase', 'carousel', 'slide'];
    }
    public function get_styles()
    {
        $styles = [
            'logo-carousel',
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
        return $this->TRAIT_get_carousel_scripts();
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }
    protected function register_controls()
    {

        $this->TRAIT_register_logo_repeater_controls('Logo Carousel Items'); // Repeater Controls

        $this->start_controls_section(
            'section_carousel_settings',
            [
                'label' => __('Carousel Settings', 'uicore-elements'),
            ]
        );

        $this->TRAIT_register_carousel_additional_controls(); // Carousel Additionals
        $this->TRAIT_register_logo_adittional_controls();
        $this->add_control('add_carousel_divider', ['type' => Controls_Manager::DIVIDER,]);
        $this->TRAIT_register_carousel_settings_controls(); // Carousel settings

        $this->end_controls_section();

        $this->TRAIT_register_navigation_controls(); // Navigation settings

        $this->start_controls_section(
            'section_style_review_items',
            [
                'label'     => esc_html__('Logo Carousel', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs('tabs_item_style');

        $this->start_controls_tab(
            'tab_item_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );
        $this->TRAIT_register_normal_item_style_controls(false);
        $this->TRAIT_register_logos_image_style_controls('normal');
        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_item_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );
        $this->TRAIT_register_hover_item_style_controls(false);
        $this->TRAIT_register_logos_image_style_controls('hover');
        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_item_active',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );
        $this->TRAIT_register_active_item_style_controls(false);
        $this->TRAIT_register_logos_image_style_controls('active');
        $this->end_controls_tabs();

        $this->end_controls_tabs(); // Necessary because register_testimonial_card_controls() OPENS but DON'T close start_controls_tabs()

        $this->end_controls_section();

        $this->TRAIT_register_navigation_style_controls(); // Carousel Navigation Styles

        $this->start_controls_section(
            'section_style_animations',
            [
                'label' => __('Animations', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_entrance_animations_controls();
        $this->TRAIT_register_hover_animation_control(
            'Item Hover Animation',
            [],
            ['underline']
        );
        $this->TRAIT_register_hover_animation_control(
            'Logo Hover Animation',
            [],
            ['underline']
        );

        $this->end_controls_section();

        // Remove unused logo control
        $this->remove_control('height');
    }
    public function render()
    {
?>
        <div class="ui-e-carousel swiper">
            <div class='swiper-wrapper'>
                <?php $this->TRAIT_render_logo_item(); ?>
            </div>
        </div>
        <?php $this->TRAIT_render_carousel_navigations(); ?>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new LogoCarousel());

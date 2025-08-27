<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;

use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Gallery_Trait;
use UiCoreElements\Utils\Carousel_Trait;
use UicoreElements\Utils\Item_Style_Component;

defined('ABSPATH') || exit();

/**
 * Gallery Carousel
 *
 * @author Lucas Marini Falbo <lucas95@uicore.co>
 * @since 1.0.14
 */

class GalleryCarousel extends UiCoreWidget
{
    use Animation_Trait;
    use Gallery_Trait;
    use Carousel_Trait;
    use Item_Style_Component;

    public function get_name()
    {
        return 'uicore-gallery-carousel';
    }
    public function get_title()
    {
        return esc_html__('Gallery Carousel', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-carousel-loop ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['gallery', 'carousel', 'slides', 'content', 'box'];
    }
    public function get_styles()
    {
        $styles = [
            'gallery-carousel',
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
        $this->TRAIT_register_gallery_repeater_controls('Content');
        $this->TRAIT_register_additional_controls(true);

        $this->start_controls_section(
            'carousel_section',
            [
                'label' => esc_html__('Carousel Settings', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_CONTENT,
            ]
        );
        $this->TRAIT_register_carousel_additional_controls();
        $this->add_control(
            'divider',
            [
                'type' => Controls_Manager::DIVIDER,
            ]
        );
        $this->TRAIT_register_carousel_settings_controls();

        $this->end_controls_section();

        $this->TRAIT_register_navigation_controls();

        $this->start_controls_section(
            'items_style_section',
            [
                'label' => esc_html__('Item', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_all_item_style_controls();

        $this->end_controls_section();

        $this->TRAIT_register_filters_style_controls();
        $this->TRAIT_register_image_style_controls(true);
        $this->TRAIT_register_title_style_controls();
        $this->TRAIT_register_description_style_controls();
        $this->TRAIT_register_tags_style_controls();
        $this->TRAIT_register_badge_style_controls();
        $this->TRAIT_register_navigation_style_controls();
        $this->TRAIT_register_gallery_animations();

        // Update some component controls
        $this->update_control(
            'item_border_border',
            [
                'default' => 'none'
            ]
        );
        $this->update_control(
            'item_padding',
            [
                'default' => [
                    'top' => 10,
                    'right' => 10,
                    'bottom' => 10,
                    'left' => 10,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-content' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->update_control(
            'item_border_radius',
            [
                'default' => [
                    'top' => 0,
                    'right' => 0,
                    'bottom' => 0,
                    'left' => 0,
                    'unit' => 'px',
                ]
            ]
        );
        $this->update_control(
            'match_height',
            [
                'render_type' => 'template',
            ]
        );

        $image_conditions = [
            'relation' => 'or',
            'terms' => [
                [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'layout',
                            'operator' => '==',
                            'value' => '',
                        ],
                        [
                            'name' => 'vertical',
                            'operator' => '!=',
                            'value' => 'true',
                        ],
                    ]
                ],
                [
                    'relation' => 'and',
                    'terms' => [
                        [
                            'name' => 'layout',
                            'operator' => '==',
                            'value' => 'ui-e-overlay',
                        ],
                        [
                            'name' => 'match_height',
                            'operator' => '==',
                            'value' => 'yes',
                        ],
                        [
                            'name' => 'vertical',
                            'operator' => '!=',
                            'value' => 'true',
                        ],
                    ],
                ]
            ],
        ];

        $this->update_control(
            'style_image_section',
            [
                'conditions' => $image_conditions
            ]
        );
        $this->update_responsive_control(
            'image_height',
            [
                'conditions' => $image_conditions
            ]
        );


        // Remove some useless component controls or group controls that can't be properly updated
        $this->remove_control('item_background_background');
        $this->remove_control('item_hover_background_background');
        $this->remove_control('item_background_color');
        $this->remove_control('item_background_image');
        $this->remove_control('item_background_gradient');

        // Inject new controls
        $this->start_injection([
            'of' => 'animation_style',
            'at' => 'after',
        ]);

        $this->add_control(
            'main_image',
            [
                'label'        => __('Main image', 'uicore-elements'),
                'type'         => Controls_Manager::SELECT,
                'default'      => '',
                'options' => [
                    '' => esc_html__('Default', 'uicore-elements'),
                    'ui-e-main-image-scale' => esc_html__('Scale', 'uicore-elements'),
                    'ui-e-main-image-show' => esc_html__('Show Info', 'uicore-elements'),
                    'ui-e-main-image-show_scale' => esc_html__('Show Info & Scale', 'uicore-elements'),
                ],
                'description' => __('Works best with centered slides.', 'uicore-elements'),
                'prefix_class' => '',
                'render_type'  => 'template',
                'frontend_available' => true,
                'condition' => [
                    'animation_style!' => 'marquee',
                ]
            ]
        );

        $this->end_injection();

        $this->start_injection([
            'of' => 'item_border_border',
            'at' => 'before',
        ]);

        // normal background controls
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_gallery_background',
                'selector' => '{{WRAPPER}} .ui-e-item',
                'condition' => [
                    'layout' => '',
                ]
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_content_background',
                'selector' => '{{WRAPPER}} .ui-e-content',
                'condition' => [
                    'layout' => 'ui-e-overlay',
                ]
            ]
        );

        $this->end_injection();

        $this->start_injection([
            'of' => 'item_hover_border_color',
            'at' => 'before',
        ]);

        // hover background controls
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_gallery_hover_background',
                'selector' => '{{WRAPPER}} .ui-e-item:hover',
                'condition' => [
                    'layout' => '',
                ]
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'item_content_hover_background',
                'selector' => '{{WRAPPER}} .ui-e-item:hover .ui-e-content',
                'condition' => [
                    'layout' => 'ui-e-overlay',
                ]
            ]
        );

        $this->end_injection();
    }

    public function render()
    {
        $settings = $this->get_settings_for_display();
?>
        <div class="ui-e-carousel swiper">
            <div class='swiper-wrapper'>
                <?php $this->TRAIT_render_gallery($settings, true); ?>
            </div>
        </div>
        <?php $this->TRAIT_render_carousel_navigations(); ?>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new GalleryCarousel());

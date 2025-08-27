<?php

namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use Elementor\Utils;
use Elementor\Repeater;
use Elementor\Group_Control_Image_Size;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Css_Filter;

use UiCoreElements\Utils\Carousel_Trait;
use UiCoreElements\Helper;

defined('ABSPATH') || exit();

trait Logo_Trait
{

    use Carousel_Trait;

    // Settings
    protected function TRAIT_register_logo_repeater_controls($logo)
    {
        $this->start_controls_section(
            'section_logo',
            [
                /* translators: %s: logo name */
                'label' => esc_html(sprintf('%s', $logo), 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $repeater = new Repeater();
        $repeater->add_control(
            'title',
            [
                'label'   => esc_html__('Title', 'uicore-elements'),
                'type'    => Controls_Manager::TEXT,
            ]
        );
        $repeater->add_control(
            'image',
            [
                'label'   => esc_html__('Logo Image', 'uicore-elements'),
                'type'    => Controls_Manager::MEDIA,
                'default' => [
                    'url' => Utils::get_placeholder_image_src(),
                ],
            ]
        );
        $repeater->add_control(
            'link',
            [
                'label'         => esc_html__('Website URL', 'uicore-elements'),
                'type'          => Controls_Manager::URL,
                'show_external' => false,
                'label_block'   => false,
            ]
        );
        $this->add_control(
            'logo_list',
            [
                'show_label'  => false,
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'title_field' => "{{{ title }}}",
                'default'     => [
                    [
                        'title' => esc_html__('Item 1', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                    [
                        'title' => esc_html__('Item 2', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                    [
                        'title' => esc_html__('Item 3', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                    [
                        'title' => esc_html__('Item 4', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                    [
                        'title' => esc_html__('Item 5', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                    [
                        'title' => esc_html__('Item 6', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                    [
                        'title' => esc_html__('Item 7', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                    [
                        'title' => esc_html__('Item 8', 'uicore-elements'),
                        'image' => ['url' => Utils::get_placeholder_image_src()]
                    ],
                ]
            ]
        );
        $this->end_controls_section();
    }
    protected function TRAIT_register_logo_adittional_controls()
    {
        $this->add_group_control(
            Group_Control_Image_Size::get_type(),
            [
                'name'      => 'thumbnail',
                'default'   => 'large',
                'default'   => 'medium',
                'exclude'   => [
                    'custom'
                ]
            ]
        );
        $this->add_responsive_control(
            'height',
            [
                'label'      => esc_html__('Item Height', 'uicore-elements'),
                'type'       => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range'      => [
                    'px' => [
                        'max' => 500,
                        'min' => 100,
                    ]
                ],
                'selectors'  => [
                    '{{WRAPPER}} .ui-e-item' => 'height: {{SIZE}}{{UNIT}};'
                ],
            ]
        );
    }

    // Grid Styles
    protected function TRAIT_register_logos_grid_style_controls()
    {
        $this->start_controls_tabs('logos_style');

        $this->start_controls_tab(
            'tab_logos_style_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );

        $this->add_control(
            'item_bg_color',
            [
                'label'     => esc_html__('Background Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} figure' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        // Using Group Control Border overlays script border styling
        $this->add_control(
            'item_border_type',
            [
                'label'     => esc_html__('Border Type', 'uicore-elements'),
                'type'      => Controls_Manager::SELECT,
                'options'   => [
                    'none'   => esc_html__('None', 'uicore-elements'),
                    'solid'  => esc_html__('Solid', 'uicore-elements'),
                    'double' => esc_html__('Double', 'uicore-elements'),
                    'dotted' => esc_html__('Dotted', 'uicore-elements'),
                    'dashed' => esc_html__('Dashed', 'uicore-elements'),
                    'groove' => esc_html__('Groove', 'uicore-elements'),
                ],
                'default'   => 'solid',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item' => 'border-style: {{VALUE}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'item_border_width',
            [
                'label'          => esc_html__('Border Width', 'uicore-elements'),
                'type'           => Controls_Manager::SLIDER,
                'default'        => [
                    'size' => 2,
                ],
                'tablet_default' => [
                    'size' => 2,
                ],
                'mobile_default' => [
                    'size' => 2,
                ],
                'range'          => [
                    'px' => [
                        'min' => 0,
                        'max' => 50,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-border-width: {{SIZE}}{{UNIT}};'
                ],
                'condition' => [
                    'item_border_type!' => 'none',
                ]
            ]
        );
        $this->add_control(
            'item_border_color',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item' => 'border-color: {{VALUE}};',
                ],
                'default'   => '#EEE',
                'condition' => [
                    'item_border_type!' => 'none',
                ]
            ]
        );
        $this->add_responsive_control(
            'item_border_radius',
            [
                'label'      => esc_html__('Border Radius', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%'],
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    // each side is used separately by logo-grid script
                    '{{WRAPPER}}' => '--ui-e-top-radius: {{TOP}}{{UNIT}};
                                            --ui-e-right-radius: {{RIGHT}}{{UNIT}};
                                            --ui-e-left-radius: {{LEFT}}{{UNIT}};
                                            --ui-e-bottom-radius: {{BOTTOM}}{{UNIT}};
                                            --ui-e-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'item_padding',
            [
                'label'      => esc_html__('Padding', 'uicore-elements'),
                'type'       => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%'],
                'default' => [
                    'top' => 20,
                    'right' => 20,
                    'bottom' => 20,
                    'left' => 20,
                    'unit' => 'px',
                    'isLinked' => true,
                ],
                'selectors'  => [
                    '{{WRAPPER}} figure' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'logos_box_shadow_item',
                'exclude'  => [
                    'box_shadow_position',
                ],
                'selector' => '{{WRAPPER}} .ui-e-item',
                'condition' => [
                    'layout' => 'inner-border'
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'logos_box_shadow_wrapper',
                'exclude'  => [
                    'box_shadow_position',
                ],
                'selector' => '{{WRAPPER}} .ui-e-grid',
                'condition' => [
                    'layout!' => 'inner-border'
                ],
            ]
        );
        $this->TRAIT_register_logos_image_style_controls('normal');
        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_logos_style_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'item_bg_hover_color',
            [
                'label'     => esc_html__('Background Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item:hover figure' => 'background-color: {{VALUE}};',
                ],
            ]
        );
        $this->add_control(
            'grid_border_hover_color',
            [
                'label'     => esc_html__('Border Color', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-item:hover' => 'border-color: {{VALUE}};',
                ],
                'condition' => [
                    'item_border_type!' => 'none',
                    'layout'            => 'inner-border',
                ]
            ]
        );
        $this->TRAIT_register_logos_image_style_controls('hover');
        $this->end_controls_tab();

        $this->end_controls_tabs();
    }
    // Logo Image Styles
    protected function TRAIT_register_logos_image_style_controls($state)
    {
        if ($state == 'normal') {
            $this->add_group_control(
                Group_Control_Css_Filter::get_type(),
                [
                    'name'     => 'image_css_filters',
                    'selector' => '{{WRAPPER}} figure img',
                ]
            );
            $this->add_responsive_control(
                'image_size',
                [
                    'label'      => esc_html__('Image Size', 'uicore-elements'),
                    'type'       => Controls_Manager::SLIDER,
                    'size_units' => ['px', '%'],
                    'range'      => [
                        'px' => [
                            'min' => 10,
                            'max' => 500,
                        ],
                        '%' => [
                            'min' => 0,
                            'max' => 100,
                        ],
                    ],
                    'selectors'  => [
                        '{{WRAPPER}} img' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; object-fit: contain;'
                    ],
                ]
            );
        } else if ($state == 'hover') {
            $this->add_group_control(
                Group_Control_Css_Filter::get_type(),
                [
                    'name'     => 'image_css_filters_hover',
                    'selector' => '{{WRAPPER}} figure:hover img',
                ]
            );
            $this->add_control(
                'image_bg_hover_transition',
                [
                    'label'     => esc_html__('Transition Duration', 'uicore-elements'),
                    'type'      => Controls_Manager::SLIDER,
                    'range'     => [
                        'px' => [
                            'max'  => 3,
                            'step' => 0.1,
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} figure img' => 'transition-duration: {{SIZE}}s;',
                    ],
                ]
            );
        } else if ($state == 'active') {
            $this->add_group_control(
                Group_Control_Css_Filter::get_type(),
                [
                    'name'     => 'image_css_filters_active',
                    'selector' => '{{WRAPPER}} .is-selected figure img',
                ]
            );
        }
    }

    // Rendering
    protected function render_logo_figure($data, $size)
    {
        $image  = wp_get_attachment_image_url($data['id'], $size);
        $name   = isset($data['name']) ? $data['name'] : '';
        $desc   = isset($data['description']) ? $data['description'] : '';
        $alt    = $name . ' : ' . $desc;
?>
        <figure class="<?php echo esc_attr($this->get_settings_for_display('logo_hover_animation')); ?>">
            <?php
            if ($image) {
                echo wp_get_attachment_image(
                    $data['id'],
                    $size,
                    false,
                    ['alt'   => esc_attr($alt)]
                );
            } else {
                printf(
                    '<img src="%s" alt="%s">',
                    esc_url(Utils::get_placeholder_image_src()),
                    esc_attr($alt)
                );
            }
            ?>
        </figure>
    <?php
    }
    protected function TRAIT_render_logo_item()
    {
        $settings   = $this->get_settings_for_display();

        // Get entrance and item hover animation classes
        $entrance   = $this->is_option('animate_items', 'ui-e-grid-animate') ? 'elementor-invisible' : '';
        $hover      = isset($settings['item_hover_animation']) ? $settings['item_hover_animation'] : null;
        $animations = sprintf('%s %s', $entrance, $hover);

        // Check if any animation that relies on the animation wrapper is set
        $has_animation = !empty($entrance) || !empty($hover) || !empty($settings['item_hover_animation']) || !empty($settings['logo_hover_animation']);

        // Build wrapper class
        $is_carousel = strpos($this->get_name(), 'carousel') !== false;
        $this->add_render_attribute([
            'wrapper' => [
                'class' => $is_carousel ? 'ui-e-wrp swiper-slide' : 'ui-e-wrp',
            ],
        ]);

        foreach ($settings['logo_list'] as $index => $item) {
            $this->render_logo($index, $item, $settings, $has_animation, $animations);
        }

        if ($is_carousel) {

            $total_slides = count($settings['logo_list']);

            // Most recent swiper versions requires, if loop, at least one extra slide compared to visible slides
            if ($this->TRAIT_should_duplicate_slides($total_slides)) {
                $diff = $this->TRAIT_get_duplication_diff($total_slides);
                for ($i = 0; $i <= $diff; $i++) {
                    $this->render_logo($index, $item, $settings, $has_animation, $animations);
                }
            }
        }
    }

    protected function render_logo($index, $item, $settings, $has_animation, $animations)
    {
        // Params
        $key = 'logo_' . $index;
        $tag = 'div';
        $this->add_render_attribute($key, 'class', 'ui-e-item');

        // Build URL
        if (!empty($item['link']['url'])) {
            $tag = 'a';
            $this->add_link_attributes($key, $item['link']);
        }
    ?>
        <div <?php $this->print_render_attribute_string('wrapper'); ?>>
            <?php if ($has_animation) : ?>
                <div class='ui-e-animations-wrp <?php echo esc_attr($animations); ?>'>
                <?php endif; ?>
                <<?php echo Helper::esc_tag($tag); ?> <?php $this->print_render_attribute_string($key); ?>>
                    <?php $this->render_logo_figure($item['image'], $settings['thumbnail_size']); ?>
                </<?php echo Helper::esc_tag($tag); ?>>
                <?php if ($has_animation) : ?>
                </div>
            <?php endif; ?>
        </div>
<?php
    }
}

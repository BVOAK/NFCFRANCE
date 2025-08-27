<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Background;
use UiCoreElements\UiCoreWidget;

use UiCore\Assets;
use UiCore\Portfolio;
use UiCore\Blog;
use UiCore\Helper;
use UiCoreElements\Controls\Post_Filter;
use UiCoreElements\Controls\Query;

defined('ABSPATH') || exit();

/**
 * Post Grid
 *
 */

class PostGrid extends UiCoreWidget
{
    private $_query;

    public function __construct($data = [], $args = null)
    {
        parent::__construct($data, $args);
    }
    public function get_name()
    {
        return 'uicore-post-grid';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_title()
    {
        return __('Post Grid', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-gallery-grid ui-e-widget';
    }
    public function get_keywords()
    {
        return ['post', 'grid', 'blog', 'recent', 'news'];
    }
    public function get_styles()
    {
        return [
            'post-grid',
            // get framework assets
            'uicore-blog-st' => [
                'external' => true,
            ],
            'uicore-portfolio-st' => [
                'external' => true,
                'condition' => [
                    'posts-filter_post_type' => 'portfolio',
                ]
            ]
        ];
    }
    public function get_scripts()
    {
        return [];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }

    public function on_import($element)
    {
        if (!get_post_type_object($element['settings']['posts-filter_post_type'])) {
            $element['settings']['posts-filter_post_type'] = 'post';
        }

        return $element;
    }
    public function on_export($element)
    {
        $element = Post_Filter::on_export_remove_setting_from_element($element, 'uicore-posts-filter');
        return $element;
    }

    public function get_query()
    {
        return $this->_query;
    }

    protected function register_controls()
    {

        $default_columns = Helper::get_option('blog_col', 3);

        $this->start_controls_section('section_post_grid_def', [
            'label' => esc_html__('Query', 'uicore-elements'),
        ]);

        $this->add_group_control(Post_Filter::get_type(), [
            'name' => 'posts-filter',
            'label' => esc_html__('Posts', 'uicore-elements'),
        ]);

        $this->add_control('item_limit', [
            'label' => esc_html__('Item Limit', 'uicore-elements'),
            'type' => Controls_Manager::SLIDER,
            'reder_type' => 'template',
            'range' => [
                'px' => [
                    'min' => 1,
                    'max' => 30,
                ],
            ],
            'default' => [
                'size' => 3,
            ],
        ]);
        $this->add_control('col_number', [
            'label' => esc_html__('Columns Number', 'uicore-elements'),
            'type' => Controls_Manager::SLIDER,
            'reder_type' => 'template',
            'range' => [
                'px' => [
                    'min' => 1,
                    'max' => 4,
                ],
            ],
            'default' => [
                'size' => $default_columns,
            ],
        ]);

        $this->end_controls_section();

        $this->start_controls_section('section_post_grid_layout', [
            'label' => esc_html__('Layout', 'uicore-elements'),
        ]);
        $this->add_control(
            'layout',
            [
                'label' => __('Item Style', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default'  => __('Default', 'uicore-elements'),
                    'classic' => __('classic', 'uicore-elements'),
                    'grid' => __('Grid', 'uicore-elements'),
                    'horizontal' => __('Horizontal', 'uicore-elements'),
                    'masonry' => __('Masonry', 'uicore-elements'),
                ],
                'condition' => array(
                    'posts-filter_post_type!' => 'portfolio',
                ),
            ]
        );

        $this->add_control(
            'box_style',
            [
                'label' => __('layout', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default'  => __('Default', 'uicore-elements'),
                    'boxed' => __('Boxed', 'uicore-elements'),
                    'boxed-creative' => __('Boxed Creative', 'uicore-elements'),
                    'cover' => __('Cover', 'uicore-elements'),
                ],
                'condition' => array(
                    'posts-filter_post_type!' => 'portfolio',
                ),
            ]
        );

        $this->add_control(
            'box_ratio',
            [
                'label' => __('Image Ratio', 'uicore-elements'),
                'type' => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default'  => __('Default', 'uicore-elements'),
                    'square' => __('Square', 'uicore-elements'),
                    'landscape' => __('Landscape', 'uicore-elements'),
                    'portrait' => __('Portrait', 'uicore-elements'),
                ],
                'condition' => array(
                    'posts-filter_post_type!' => 'portfolio',
                ),
            ]
        );
        $this->add_control(
            'extra_author',
            [
                'label' => __('Author', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
                'reder_type' => 'template',
                'condition' => array(
                    'posts-filter_post_type!' => 'portfolio',
                ),
            ]
        );
        $this->add_control(
            'extra_date',
            [
                'label' => __('Date', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
                'reder_type' => 'template',
                'condition' => array(
                    'posts-filter_post_type!' => 'portfolio',
                ),
            ]
        );
        $this->add_control(
            'extra_excerpt',
            [
                'label' => __('Excerpt', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
                'reder_type' => 'template',
                'condition' => array(
                    'posts-filter_post_type!' => 'portfolio',
                ),
            ]
        );
        $this->add_control(
            'extra_category',
            [
                'label' => __('Category', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'return_value' => 'yes',
                'default' => '',
                'reder_type' => 'template',
                'condition' => array(
                    'posts-filter_post_type!' => 'portfolio',
                ),
            ]
        );

        $this->end_controls_section();


        $this->start_controls_section(
            'section_style_typo',
            [
                'label' => __('Content Style', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_control(
            'post_heading_title',
            [
                'label' => esc_html__('Post Title', 'uicore-elements'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'post_title_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-post-title' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'post_title_typography',
                'selector' => '{{WRAPPER}} .uicore-post-title, {{WRAPPER}} .uicore-post-title',
            ]
        );


        $this->add_control(
            'extra_excerpt_heading',
            [
                'label' => esc_html__('Excerpt', 'uicore-elements'),
                'type' => Controls_Manager::HEADING,
                'separator' => 'before',
            ]
        );
        $this->add_responsive_control(
            'extra_excerpt_bottom_space',
            [
                'label' => esc_html__('Spacing', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}  .uicore-post-info-wrapper > p' => 'margin-top: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'extra_excerpt_color',
            [
                'label' => esc_html__('Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}} .uicore-post-info-wrapper > p' => 'color: {{VALUE}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'extra_excerpt_typography',
                'selector' => '{{WRAPPER}} .uicore-post-info-wrapper > p',
            ]
        );


        $this->add_responsive_control(
            'box_padding',
            [
                'label' => esc_html__('Content Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', '%', 'rem'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .uicore-blog-grid .uicore-post .uicore-post-info' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}}!important;',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'box_background',
                'selector' => '{{WRAPPER}} .uicore-blog-grid .uicore-post',
            ]
        );

        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'box_border',
                'selector' => '{{WRAPPER}} .uicore-blog-grid .uicore-post',
                'separator' => 'before',
            ]
        );

        $this->add_control(
            'box_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::SLIDER,
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 100,
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}} .uicore-blog-grid .uicore-post' => '--uicore-blog--radius: {{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'box_shadow_style',
                'selector' => '{{WRAPPER}} .uicore-blog-grid .uicore-post',
            ]
        );


        $this->end_controls_section();
    }

    public function query_posts($posts_per_page, $type = null)
    {
        $query_args = Query::get_query_args('posts-filter', $this->get_settings());

        if ($type === 'portfolio') {
            $query_args['orderby'] = 'menu_order date';
        }

        $query_args['posts_per_page'] = $posts_per_page;

        $this->_query = new \WP_Query($query_args);
    }

    protected function render()
    {
        $settings = $this->get_settings();

        $col = $settings['col_number']['size'];
        $type = $settings['posts-filter_post_type'];

        if ($type != 'portfolio') {
            $type = str_replace(' ', '-', $settings['box_style']);
        }

        $this->query_posts($settings['item_limit']['size'], $type);
        $wp_query = $this->get_query();

        if (!$wp_query->found_posts) {
            echo 'No Posts Found!';
            return;
        }


        if ($type === 'portfolio') {

            if (!class_exists('\UiCore\Portfolio\Frontend')) {
                require_once UICORE_INCLUDES . '/portfolio/class-template.php';
                require_once UICORE_INCLUDES . '/portfolio/class-frontend.php';
            }
            Portfolio\Frontend::frontend_css(true);
            $portfolio = new Portfolio\Template('display');
            $portfolio->portfolio_layout($wp_query, null, $col);
        } else {
            $layout = $settings['layout'] === 'default' ? null : $settings['layout'];
            $style = $settings['box_style'] === 'default' ? null : $settings['box_style'];

            if (isset($settings['box_ratio'])) {
                $ratio = $settings['box_ratio'] === 'default' ? null : $settings['box_ratio'];
            } else { //Fallback for older versions
                $ratio = null;
            }
            if (isset($settings['extra_author'])) {
                $extra = [
                    'author'    => $this->is_option('extra_author', 'yes'),
                    'date'      => $this->is_option('extra_date', 'yes'),
                    'excerpt'   => $this->is_option('extra_excerpt', 'yes'),
                    'category'  => $this->is_option('extra_category', 'yes')
                ];
            } else { //Fallback for older versions
                $extra = [
                    'author'    => null,
                    'date'      => null,
                    'excerpt'   => null,
                    'category'  => null
                ];
            }


            if (!class_exists('\UiCore\Blog\Frontend')) {
                require_once UICORE_INCLUDES . '/blog/class-template.php';
                require_once UICORE_INCLUDES . '/blog/class-frontend.php';
            }
            Blog\Frontend::frontend_css(true);
            $blog = new Blog\Template('display');
            $blog->blog_layout($wp_query, $layout, $col, null, $ratio, $extra, $style);
        }
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new PostGrid());

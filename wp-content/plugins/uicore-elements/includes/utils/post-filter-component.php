<?php

namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use UiCoreElements\Helper;

use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Post Filter Component
 */

trait Post_Filters_Trait
{

    function TRAIT_register_filter_controls($section = true)
    {
        if ($section) {
            $this->start_controls_section(
                'section_filters',
                [
                    'label' => esc_html__('Filters', 'uicore-elements'),
                ]
            );
        }

        $this->add_control(
            'post_filtering',
            [
                'label' => __('Filters', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
                'default' => 'no',
                'render_type' => 'template',
            ]
        );
        $this->add_control(
            'filters_taxonomies',
            [
                'label' => __('Taxonomies', 'uicore-elements'),
                'type' => Controls_Manager::SELECT2,
                'multiple' => false,
                'label_block' => true,
                'options' => Helper::get_taxonomies(),
                'default' => 'category',
                'condition' => [
                    'post_filtering' => 'yes',
                ],
            ]
        );
        $this->add_control(
            'custom_meta',
            [
                'label' => esc_html__('Meta Slug', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'filters_taxonomies' => 'custom',
                ],
            ]
        );
        $this->add_control(
            'filters_ajax_alert',
            [
                'type' => Controls_Manager::ALERT,
                'alert_type' => 'warning',
                'heading' => esc_html('Ajax enabled', 'uicore-elements'),
                'content' => esc_html__('When using `load more` pagination, filters are disabled on taxonomy archive pages, such as categories, tags, authors, etc. Blog, shop, or custom post-type archive pages are not affected.', 'uicore-elements'),
                'condition' => [
                    'post_filtering' => 'yes',
                    'pagination_type' => 'load_more',
                ],
            ]
        );

        if ($section) {
            $this->end_controls_section();
        }
    }

    function TRAIT_register_filter_style_controls()
    {
        $this->start_controls_section(
            'section_filter_style',
            [
                'label' => esc_html__('Filter Style', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
                'condition' => array(
                    'post_filtering' => 'yes',
                ),
            ]
        );
        $this->add_responsive_control(
            'filters_align',
            [
                'label' => esc_html__('Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'start'    => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-h-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-h-align-center',
                    ],
                    'end' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon' => 'eicon-h-align-right',
                    ],
                ],
                'default' => 'center',
                'selectors' => [
                    '(desktop){{WRAPPER}} .ui-e-filters' => 'justify-content: {{VALUE}}',
                    '(mobile){{WRAPPER}} .ui-e-filters' => 'align-items: {{VALUE}}' // mobile version sets flex-direction as column
                ]
            ]
        );
        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'filters_typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_ACCENT,
                ],
                'selector' => '{{WRAPPER}} .ui-e-filter-item',
            ]
        );
        $this->start_controls_tabs('tabs_filters_style');

        $this->start_controls_tab(
            'tab_filters_normal',
            [
                'label' => esc_html__('Normal', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'filters_text_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filter-item' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'filters_background',
                'types' => ['classic'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .ui-e-filter-item',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                    'color' => [
                        'global' => [
                            'default' => Global_Colors::COLOR_ACCENT,
                        ],
                    ],
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'filters_border',
                'selector' => '{{WRAPPER}} .ui-e-filter-item',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'filters_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-filter-item',
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_filters_hover',
            [
                'label' => esc_html__('Hover', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'filters_hover_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filter-item:hover' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'filters_background_hover',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .ui-e-filter-item:hover',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'hover_filters_border',
                'selector' => '{{WRAPPER}} .ui-e-filter-item:hover',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'filters_hover_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-filter-item:hover',
            ]
        );
        $this->end_controls_tab();

        $this->start_controls_tab(
            'tab_filters_active',
            [
                'label' => esc_html__('Active', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'filters_active_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filter-item.ui-e-active' => 'color: {{VALUE}};',
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Background::get_type(),
            [
                'name' => 'filters_background_active',
                'types' => ['classic', 'gradient'],
                'exclude' => ['image'],
                'selector' => '{{WRAPPER}} .ui-e-active',
                'fields_options' => [
                    'background' => [
                        'default' => 'classic',
                    ],
                ],
            ]
        );
        $this->add_group_control(
            Group_Control_Border::get_type(),
            [
                'name' => 'active_filters_border',
                'selector' => '{{WRAPPER}} .ui-e-active',
            ]
        );
        $this->add_group_control(
            Group_Control_Box_Shadow::get_type(),
            [
                'name' => 'filters_active_box_shadow',
                'selector' => '{{WRAPPER}} .ui-e-active',
            ]
        );
        $this->end_controls_tab();

        $this->end_controls_tabs();

        $this->add_responsive_control(
            'filters_border_radius',
            [
                'label' => esc_html__('Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'custom'],
                'separator' => 'before',
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filter-item' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->add_responsive_control(
            'filters_padding',
            [
                'label' => esc_html__('Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', '%', 'em', 'rem', 'vw', 'custom'],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-filter-item' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );
        $this->end_controls_section();
    }

    function TRAIT_render_filters($settings, $is_product = false)
    {

        // Tax filters should not be displayed on taxonomy pages if ajax is enabled
        // since may cause several conflicts with the query management
        $is_tax_archive =  is_tax() || is_category() || is_tag() || is_author();
        if ($this->is_option('pagination_type', 'load_more') && $is_tax_archive) {
            return;
        }

        $slug      = $is_product ? 'product-filter_' : 'posts-filter_';
        $post_type = $is_product ? 'product' : $settings[$slug . 'post_type']; // $settings[] option may not necesarilly be a valid post type
        $taxonomy  = $settings['filters_taxonomies'] === 'custom' ? $settings['custom_meta'] : $settings['filters_taxonomies']; // taxonomy label
        $ajax      = $this->is_option('pagination_type', 'load_more'); // if filters should work with rest api
        $is_main_query = $post_type === 'current';

        // Return if filters are disabled or if there's no taxonomies
        if ($this->is_option('post_filtering', 'yes', '!==') || !$taxonomy) {
            return;
        }

        // Get taxonomy list
        $post_type = $post_type === 'current' ? get_post_type() : $settings[$slug . 'post_type'];
        $taxonomy_list = get_object_taxonomies($post_type);

        // Invalid/nonexistent taxonomy fallback
        if (!$is_product && !taxonomy_exists($taxonomy)) {
            if (!in_array($taxonomy, $taxonomy_list)) {
                if (\Elementor\Plugin::instance()->editor->is_edit_mode()) {
                    /* translators: %s: taxonomy name */
                    echo esc_html(sprintf('<i>%s</i> is not a valid taxonomy.', $taxonomy), 'uicore-elements');
                }
                return;
            }
        }

        // Get the taxonomy label
        $tax_obj = get_taxonomy($taxonomy);
        $label = $tax_obj ? $tax_obj->label : '';

        // Check if a tax query is set in the current WP_Query
        $active_terms = [];
        foreach ($taxonomy_list as $tax) {
            $setting_key = $slug . $tax . '_ids';
            if (!empty($settings[$setting_key])) {
                $active_terms = $settings[$setting_key];
            }
        }

        // Fetch terms
        $terms = get_terms([
            'taxonomy' => $taxonomy,
            'hide_empty' => false,
            'include' => $active_terms
        ]);

        // Build archive url if we're not using rest api
        if (!$ajax) {

            // main query is the current query, so we get the post type archive pages
            if ($is_main_query) {
                $archive = get_post_type_archive_link($post_type);
                // If post type don't have an archive page, we get the current page.
                if (!$archive) {
                    global $wp;
                    $archive = home_url($wp->request);
                }

                // On other queries we keep at the same page
            } else {
                global $wp;
                $archive = home_url($wp->request);
            }
        }

?>
        <nav class="ui-e-filters" aria-label="<?php echo esc_html($label); ?>">

            <?php if (!$ajax) : ?>
                <a href="<?php echo esc_url($archive); ?>">
                    <button class="ui-e-filter-item" data-ui-e-action="clear"> <?php echo esc_html__('All', 'uicore-elements'); ?> </button>
                </a>
            <?php else: ?>
                <button class="ui-e-filter-item" data-ui-e-action="clear"> <?php echo esc_html__('All', 'uicore-elements'); ?> </button>
            <?php endif; ?>

            <?php foreach ($terms as $term) :
                $active_class = '';
                if ((is_archive() || is_tax() || is_post_type_archive()) && $is_main_query) {
                    $current = get_queried_object();
                    if (isset($current->term_id) && $term->term_id == $current->term_id) {
                        $active_class = 'ui-e-active';
                    }
                } else {
                    if (!$ajax && isset($_GET['term']) && $term->term_id == $_GET['term']) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
                        $active_class = 'ui-e-active';
                    }
                }
            ?>

                <?php if (!$ajax) :
                    // current query works with standart term links, other queries uses url params
                    $term_url = $is_main_query ? get_term_link($term->term_id, $term->taxonomy) : $archive . '?tax=' . $term->taxonomy . '&term=' . $term->term_id;
                ?>
                    <a href="<?php echo esc_url($term_url); ?>">
                    <?php endif; ?>

                    <button
                        class="ui-e-filter-item <?php echo esc_attr($active_class); ?>"
                        data-ui-e-action="filter"
                        data-ui-e-term="<?php echo esc_attr($term->term_id); ?>"
                        data-ui-e-taxonomy="<?php echo esc_attr($term->taxonomy); ?>">
                        <?php echo esc_html($term->name); ?>
                    </button>

                    <?php if (!$ajax) : ?>
                    </a>
                <?php endif; ?>

            <?php endforeach; ?>

        </nav>
<?php
    }
}

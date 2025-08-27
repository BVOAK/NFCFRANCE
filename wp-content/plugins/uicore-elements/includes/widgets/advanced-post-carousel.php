<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Carousel_Trait;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Grid_Trait;
use uicoreElements\Utils\Post_Trait;
use uiCoreElements\Utils\Post_Filters_Trait;

defined('ABSPATH') || exit();

class AdvancedPostCarousel extends UiCoreWidget
{
    use Carousel_Trait,
        Animation_Trait,
        Grid_Trait,
        Post_Filters_Trait,
        Post_Trait;

    private $_query;

    public function get_name()
    {
        return 'uicore-advanced-post-carousel';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_title()
    {
        return __('Advanced Post Carousel', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-posts-carousel ui-e-widget';
    }
    public function get_keywords()
    {
        return ['post', 'carousel', 'slide', 'blog', 'recent', 'news'];
    }
    public function get_styles()
    {
        $styles = [
            'advanced-post-carousel',
            'carousel',
            'post-meta',
            'filters' => [
                'condition' => [
                    'post_filtering' => 'yes',
                ]
            ],
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

    private function filter_missing_taxonomies($settings)
    {
        $taxonomy_filter_args = [
            'show_in_nav_menus' => true,
        ];
        if (!empty($settings['posts-filter_post_type'])) {
            $taxonomy_filter_args['object_type'] = [$settings['posts-filter_post_type']];
        }

        $taxonomies = get_taxonomies($taxonomy_filter_args, 'objects');

        foreach ($taxonomies as $taxonomy => $object) {
            $controll_id = 'posts-filter_' . $taxonomy . '_ids';
            //error_log($controll_id);
            //error_log(print_r($settings[$controll_id], true));

            if (!isset($settings[$controll_id]) || empty($settings[$controll_id])) {
                continue;
            }

            //if is set check if this id still exists
            $settings[$controll_id] = array_filter($settings[$controll_id], function ($term_id) use ($object) {
                return term_exists($term_id, $object->name);
            });

            if (empty($settings[$controll_id])) {
                $settings[$controll_id] = null;
            }
        }

        return $settings;
    }
    public function on_import($element)
    {
        $element['settings'] = $this->filter_missing_taxonomies($element['settings']);
        return $element;
    }
    function get_query()
    {
        return $this->_query;
    }

    protected function register_controls()
    {

        // Contents and Settings
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Carousel', 'uicore-elements'),
            ]
        );
        $this->TRAIT_register_carousel_additional_controls(); // Grid Layouts with old masonry control
        $this->end_controls_section();

        $this->start_controls_section(
            'section_carousel_settings',
            [
                'label' => __('Carousel Settings', 'uicore-elements'),
            ]
        );
        $this->TRAIT_register_carousel_settings_controls(); // Carousel settings
        $this->end_controls_section();

        $this->TRAIT_register_navigation_controls();
        $this->TRAIT_register_post_item_controls();
        $this->TRAIT_register_post_button_controls();
        $this->TRAIT_register_post_meta_controls();
        $this->TRAIT_register_post_query_controls();
        $this->TRAIT_register_filter_controls();

        // Styles
        $this->TRAIT_register_post_item_style_controls(); // instead of using carousel item style controls, we use post item and only updates some default values after. Is a better approach because advanced post widgets inherits some APG legacy codes and methods, such as setting borders and paddings on <article> instead of 'ui-e-item'
        $this->TRAIT_register_navigation_style_controls();
        $this->TRAIT_register_post_content_style_controls();
        $this->TRAIT_register_post_button_style_controls();
        $this->TRAIT_register_post_meta_style_controls();
        $this->TRAIT_register_filter_style_controls();

        $this->start_controls_section(
            'section_style_animations',
            [
                'label' => __('Animations', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $this->TRAIT_register_entrance_animations_controls();
        $this->TRAIT_register_hover_animation_control(
            'Item Hover Animation',
            [],
            ['underline'],
        );
        $this->TRAIT_register_post_animation_controls();
        $this->end_controls_section();

        // Update post component controls
        $this->update_control('image_size', [
            'condition' => [],
        ]);
        $this->update_control('content_padding', [
            'default' => [
                'top' => 20,
                'right' => 20,
                'bottom' => 20,
                'left' => 20,
                'unit' => 'px',
            ],
        ]);
        $this->update_control('item_limit', [
            'default' => [
                'size' => 5,
            ],
        ]);

        // TODO: create new method to return animations list and use it throughout the plugin
        // Update carousel animations
        $this->update_control('animation_style', [
            'options'      => [
                'circular'       => esc_html__('Circular', 'uicore-elements'),
                'fade_blur'   => esc_html__('Fade Blur', 'uicore-elements'),
                'default'     => esc_html__('Default', 'uicore-elements'),
            ],
        ]);
    }

    function content_template() {}

    protected function render()
    {

        // Get query args, settings and post type slug
        global  $wp_query;
        $default_query = $wp_query;
        $settings = $this->get_settings();

        // Get the quantity of items and creates a loop control
        $items = $settings['item_limit']['size'];
        $loops = 0;

        // Check if should duplicate slides and update settings
        if ($this->TRAIT_should_duplicate_slides($items)) {
            $diff = abs($this->TRAIT_get_duplication_diff($items) + 1); // +1 to fix zero based index
            $settings['item_limit']['size'] = $items + $diff;
        }

        // Build query
        $this->TRAIT_query_posts($settings, $wp_query->query);
        $wp_query = $this->get_query();

        $this->TRAIT_render_filters($settings);

        // No posts found
        if (!$wp_query->have_posts()) {
            echo '<p style="text-align:center">' . esc_html__('No posts found.', 'uicore-elements') . '</p>';
        } else {

?>
            <div class="ui-e-carousel swiper">
                <div class='swiper-wrapper'>
                    <?php
                    while ($wp_query->have_posts()) {

                        // sticky posts disregards posts per page, so ending the loop if $items == $loop forces the query respects the users item limit
                        // if ($settings['sticky'] && $items == $loops) {
                        //     break;
                        // }

                        $wp_query->the_post();
                        $this->TRAIT_render_item($loops, true);

                        $loops++;
                    }
                    ?>
                </div>
            </div>
            <?php $this->TRAIT_render_carousel_navigations(); ?>
<?php
        }

        //reset query
        wp_reset_query();
        $wp_query = $default_query;
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new AdvancedPostCarousel());

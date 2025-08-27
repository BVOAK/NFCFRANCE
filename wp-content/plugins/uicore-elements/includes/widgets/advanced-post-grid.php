<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Pagination_Trait;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Grid_Trait;
use uicoreElements\Utils\Post_Trait;
use uiCoreElements\Utils\Post_Filters_Trait;

defined('ABSPATH') || exit();

/**
 * Scripts and Styles Class
 *
 */
class AdvancedPostGrid extends UiCoreWidget
{
    use Pagination_Trait,
        Animation_Trait,
        Grid_Trait,
        Post_Filters_Trait,
        Post_Trait;

    private $_query;

    public function get_name()
    {
        return 'uicore-advanced-post-grid';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_title()
    {
        return __('Advanced Post Grid', 'uicore-elements');
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
        $styles = [
            'advanced-post-grid',
            'legacy-grid',
            'post-meta',
            'filters' => [
                'condition' => [
                    'post_filtering' => 'yes',
                ],
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
        return [
            'masonry' => [
                'condition' => [
                    'masonry' => 'ui-e-maso'
                ],
            ],
            'entrance' => [
                'condition' => [
                    'animate_items' => 'ui-e-grid-animate'
                ],
            ],
            'ajax-request' => [
                'condition' => [
                    'pagination_type' => 'load_more'
                ]
            ]
        ];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }

    private function filter_missing_taxonomies($settings)
    {
        // Check if is an "Advanced Product.." widget, since this widget is extended by other(s)
        $slug = strpos($this->get_name(), 'product') !== false ?
            'product-filter_post_type' :
            'posts-filter_post_type';

        $taxonomy_filter_args = [
            'show_in_nav_menus' => true,
        ];
        if (!empty($settings[$slug])) {
            $taxonomy_filter_args['object_type'] = [$settings[$slug]];
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
        // Query(curent/custom/related/manual)

        // /Pagination (filtering?)
        // /Aditional (no posts)

        // Contents and Settings
        $this->start_controls_section(
            'section_layout',
            [
                'label' => esc_html__('Grid', 'uicore-elements'),
            ]
        );
        $this->TRAIT_register_grid_layout_controls();
        $this->end_controls_section();

        $this->TRAIT_register_post_item_controls();
        $this->TRAIT_register_post_button_controls();
        $this->TRAIT_register_post_meta_controls();
        $this->TRAIT_register_post_query_controls(true, $this->get_name());
        $this->TRAIT_register_filter_controls();
        $this->TRAIT_register_pagination_controls();

        // Styles
        $this->TRAIT_register_post_item_style_controls();
        $this->TRAIT_register_post_content_style_controls();
        $this->TRAIT_register_post_button_style_controls();
        $this->TRAIT_register_post_meta_style_controls();
        $this->TRAIT_register_filter_style_controls();
        $this->TRAIT_register_pagination_style_controls();

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
            'anim_item'
        );
        $this->TRAIT_register_post_animation_controls();
        $this->end_controls_section();

        // Update masonry from component to a legacy version
        $this->update_control('masonry', [
            'label' => __('Masonry', 'uicore-elements'),
            'type' => Controls_Manager::SWITCHER,
            'frontend_available' => true,
            'default'  => 'no',
            'return_value' => 'ui-e-maso',
            'render_type' => 'template',
            // reset values from component
            'prefix_class' => '',
            'condition' => [],
            'selectors' => [],
        ]);
    }

    function content_template() {}

    /**
     * Renders the widget content using AJAX.
     *
     * This method retrieves the widget settings; set up the query, and renders each post item.
     * If no posts are found, it returns false. After rendering, it resets the query and returns the output.
     *
     * @param array|false $current_query The current query args, or false if the widget isn't set to use the current query.
     * @param int $pageID The ID of the page where the widget is located.
     *
     * @return string|false The rendered widget content or false if no posts are found.
     */
    public function render_ajax($current_query)
    {
        // Get settings and post type
        $settings = $this->get_settings();
        $loop = 0;

        $this->TRAIT_query_posts($settings, $current_query);
        $wp_query = $this->get_query();

        if (!$wp_query || !$wp_query->have_posts()) {
            $markup = '';
        }

        \ob_start();
        while ($wp_query->have_posts()) {
            $wp_query->the_post();
            $this->TRAIT_render_item($loop, false, true);
            $loop++;
        }
        $markup = \ob_get_clean();

        return [
            'markup' => $markup,
        ];
    }

    protected function render()
    {
        // Get current post ID
        $post_id = get_the_ID();

        // Get query args, settings and post type slug
        global $wp_query;
        $default_query = $wp_query;
        $settings = $this->get_settings();

        // Build query
        $this->TRAIT_query_posts($settings, $wp_query->query);
        $wp_query = $this->get_query();

        // Store widget settings in a transient
        $ID = strval($this->get_ID());
        set_transient('ui_elements_widgetdata_' . $ID, $settings, \MONTH_IN_SECONDS);

        // Get the quantity of items and creates a loop control
        $items = $settings['item_limit']['size'];
        $loops = 0;

        // Set masonry class
        $masonry = $this->is_option('masonry', 'yes')
            ? 'ui-e-maso'
            : '';

        $this->TRAIT_render_filters($settings);

        // No posts found
        if (!$wp_query->have_posts()) {
            echo '<p style="text-align:center">' . esc_html__('No posts found.', 'uicore-elements') . '</p>';
        } else {

?>
            <div class="ui-e-adv-grid <?php echo esc_attr($masonry); ?>">
                <?php
                while ($wp_query->have_posts()) {

                    // sticky posts disregards posts per page, so ending the loop if $items == $loop forces the query respects the users item limit
                    if ($settings['sticky'] && $items == $loops) {
                        break;
                    }

                    $wp_query->the_post();
                    $this->TRAIT_render_item($loops, false, true);

                    $loops++;
                }
                ?>
            </div>
<?php

            $this->TRAIT_render_pagination($settings, $post_id);

            if (
                $this->is_option('pagination', 'yes') &&
                $this->is_option('pagination_type', 'load_more') &&
                $this->is_option('posts-filter_post_type', 'current')
            ) {

                // Build the public query args the ajax request script passes to rest api
                $public_query = array();
                $public_query['query']['post_type'] = get_post_type();
                $public_query['query_vars'] = $wp_query->query_vars;

                echo '<script>';
                echo 'window.ui_total_pages_' . esc_html($ID) . ' = "none";'; // add none to avoid ajax errors for lack of value, but posts don't need it
                echo 'window.ui_query_' . esc_html($ID) . ' = ' . \json_encode($public_query) . ';';
                echo '</script>';
            }
        }

        //reset query
        wp_reset_query();
        $wp_query = $default_query;
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new AdvancedPostGrid());

<?php

namespace UiCoreElements;

use UiCoreElements\Controls\Product_Filter;
use uicoreElements\Utils\Product_Trait;

defined('ABSPATH') || exit();

/**
 * Advanced Product Grid Widget. Extends APG.
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.11
 */

class AdvancedProductGrid extends AdvancedPostGrid
{
    private $_query;

    use Product_Trait;

    public function get_name()
    {
        return 'uicore-advanced-product-grid';
    }
    public function get_categories()
    {
        return ['uicore', 'uicore-woo'];
    }
    public function get_title()
    {
        $this->custom_controls_verification();
        return __('Advanced Product Grid', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-gallery-grid ui-e-widget';
    }
    public function get_keywords()
    {
        return ['woocommerce', 'product', 'shop', 'store', 'post', 'grid'];
    }
    public function get_styles()
    {
        // get parent get_styles
        $styles = parent::get_styles();

        // remove parent AdvancedPostGrid widget main style. OBS: the IDE complains because get_styles doc
        // says it returns obj, but we're extending a widget that returns the array, so the parent
        // is not widget base, but the AdvancedPostGrid widget
        if (($key = array_search('advanced-post-grid', $styles)) !== false) {
            unset($styles[$key]);
        }

        // Add the product grid style
        $styles[] = 'advanced-product-grid';

        return $styles;
    }

    /**
     *  Makes sure some of our custom controls are present. This should be a
     *  temporary fix to a problem that do not happens consistently.
     */
    function custom_controls_verification()
    {
        // In some contexts, such as theme-builder, our custom controls are not available
        // and requiring in register_controls method breaks the widget render in editor
        if (! defined('DOING_AJAX') || ! DOING_AJAX) {
            if (! class_exists('UiCoreElements\Controls\Product_Filter')) {
                require_once UICORE_ELEMENTS_INCLUDES . '/controls/class-product-filter-control.php';
            }
            if (! class_exists('UiCoreElements\Controls\Post_Filter')) {
                require_once UICORE_ELEMENTS_INCLUDES . '/controls/class-post-filter-control.php';
            }
        }
    }
    function get_query()
    {
        return $this->_query;
    }

    protected function register_controls()
    {

        // Fallback
        if (!class_exists('WooCommerce')) {
            $this->start_controls_section(
                'section_fallback',
                [
                    'label' => esc_html__('Content', 'uicore-elements'),
                ]
            );
            $this->add_control(
                'woocommerce_warning',
                [
                    'type' => \Elementor\Controls_Manager::ALERT,
                    'alert_type' => 'warning',
                    'content' => esc_html__('Please enable WooCommerce to use this widget.', 'uicore-elements'),
                ]
            );
            $this->end_controls_section();
            return;
        }

        parent::register_controls();

        // Remove query source and meta controls from parent
        $this->remove_control('posts-filter_post_type');
        $this->remove_control('top_meta');
        $this->remove_control('before_title_meta');
        $this->remove_control('after_title_meta');
        $this->remove_control('bottom_meta');

        // Register query control with updated options
        $this->start_injection([
            'of' => 'section_post_grid_def',
            'at' => 'after',
        ]);
        $this->add_group_control(
            Product_Filter::get_type(),
            [
                'name' => 'product-filter',
                'label' => esc_html__('Products', 'uicore-elements'),
                'description' => esc_html__('Current Query Settings > Reading', 'uicore-elements'),
            ]
        );
        $this->end_injection();

        // Register meta controls with updated options
        $this->start_injection([
            'of' => 'section_extra_item_content',
            'at' => 'after',
        ]);
        $this->TRAIT_register_post_meta_controls(false, true);
        $this->end_injection();

        // Register new button placement control
        $this->start_injection([
            'of' => 'section_button_style',
            'at' => 'after',
        ]);
        $this->add_control(
            'button_position',
            [
                'label' => esc_html__('Placement', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SELECT,
                'options' => [
                    '' => 'Default',
                    'ui-e-button-placement-image' => 'On Image',
                ],
                'default' => 'ui-e-button-placement-image',
                'render_type' => 'template',
                'prefix_class' => ''
            ]
        );
        $this->end_injection();

        // Register new Swatches and Sale Badge controls
        $this->start_injection([
            'of' => 'show_button',
            'at' => 'after',
        ]);
        $this->add_control(
            'show_swatches',
            [
                'label' => esc_html__('Swatches', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'uicore-elements'),
                'label_off' => esc_html__('Hide', 'uicore-elements'),
                'description' => esc_html__('Will only work if you have Uicore Framework plugin active at least on version 6.0.1', 'uicore-elements'),
                'default' => 'yes',
            ]
        );
        $this->add_control(
            'show_sale_badge',
            [
                'label' => esc_html__('Sale Badge', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'label_on' => esc_html__('Show', 'uicore-elements'),
                'label_off' => esc_html__('Hide', 'uicore-elements'),
                'default' => 'yes',
                'prefix_class' => 'ui-e-show-sale-badge-',
            ]
        );
        $this->add_control(
            'badge_warning',
            [
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => __("If you can't see the Sale Badge after setting 'Show', you problably didn't set any Meta option as 'Product Sale', or you don't have products on sale.", 'uicore-elements'),
                'content_classes' => 'elementor-control-field-description',
                'condition' => [
                    'show_sale_badge' => 'yes',
                ],
            ]
        );
        $this->end_injection();

        // Register new Hide out of stock products control
        $this->start_injection([
            'of' => 'sticky',
            'at' => 'before',
        ]);
        $this->add_control(
            'hide_out_of_stock',
            [
                'label' => esc_html__('Hide out of stock', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::SWITCHER,
                'condition' => [
                    'product-filter_post_type!' => 'related',
                ],
            ]
        );
        $this->end_injection();

        //
        $this->start_injection([
            'of' => 'text',
            'at' => 'after',
        ]);
        $this->add_control(
            'variations_text',
            [
                'label'       => esc_html__('Select Options Text', 'uicore-elements'),
                'label_block' => true,
                'default'     => '',
                'description' => esc_html__('Variable product purchase text.', 'uicore-elements'),
                'placeholder' => esc_html__('Select Options', 'uicore-elements'),
            ]
        );
        $this->add_control(
            'unavailable_text',
            [
                'label'       => esc_html__('Unavailable Text', 'uicore-elements'),
                'label_block' => true,
                'default'     => '',
                'description' => esc_html__('Out of stock or not available purchase text.', 'uicore-elements'),
                'placeholder' => esc_html__('Read more', 'uicore-elements'),
            ]
        );
        $this->end_injection();

        // Update controls that uses 'posts_filter' to 'product_filter'
        $this->update_control(
            'item_limit',
            [
                'condition' => [
                    'product-filter_post_type!' => 'current',
                ],
            ]
        );

        // Update filters options
        $this->update_control(
            'filters_taxonomies',
            [
                'options' => Helper::get_taxonomies(true, true),
                'default' => ['product_cat'],
            ]
        );

        // Update Image animation options and default value
        $this->update_control(
            'anim_image',
            [
                'options' => [
                    '' => __('None', 'uicore-elements'),
                    'ui-e-img-anim-zoom' => __('Zoom', 'uicore-elements'),
                    'ui-e-img-anim-change' => __('Change image', 'uicore-elements'),
                ],
                'default' => 'ui-e-img-anim-change',
                'render_type' => 'template', // `change image` adds a new html element
            ]
        );

        // Update top meta style options so sale badge (default top meta) looks better by default
        $this->update_control(
            'top_meta_color',
            [
                'default' => '#FFF',
            ]
        );
        $this->update_control(
            'top_meta_background',
            [
                'default' => '#000',
            ]
        );
        $this->update_control(
            'top_meta_padding',
            [
                'default' => [
                    'top' => 5,
                    'right' => 5,
                    'bottom' => 5,
                    'left' => 5,
                    'unit' => 'px'
                ],
            ]
        );

        // Update some UI controls labels and default values
        $this->update_control(
            'excerpt',
            [
                'label'   => esc_html__('Product Summary', 'uicore-elements'),
                'default' => ''
            ]
        );
        $this->update_control(
            'excerpt_trim',
            ['label' => esc_html__('Summary Length (words)', 'uicore-elements')]
        );
        $this->update_control(
            'show_button',
            [
                'label'   => esc_html__('Add to Cart Button', 'uicore-elements'),
                'default' => 'yes'
            ]
        );
        $this->update_control(
            'text',
            [
                'label'       => esc_html__('Add to Cart Text', 'uicore-elements'),
                'label_block' => true,
                'default'     => '',
                'placeholder' => esc_html__('Add to cart', 'uicore-elements'),
            ]
        );
        $this->update_control(
            'text_color',
            ['label' => esc_html__('Summary Color', 'uicore-elements')]
        );
        $this->update_control(
            'text_typography',
            ['label' => esc_html__('Summary Typography', 'uicore-elements')]
        );
        $this->update_control(
            'text_gap',
            ['label' => esc_html__('Summary Top Space', 'uicore-elements')]
        );

        // Update button controls
        $this->update_control(
            'border_radius', // button border-radius
            [
                'default' => ['top' => 0, 'right' => 0, 'bottom' => 0, 'left' => 0]
            ]
        );
        $this->update_control(
            'text_padding', // button padding
            [
                'default' => ['top' => 10, 'right' => 10, 'bottom' => 10, 'left' => 10, 'unit' => 'px']
            ]
        );
        $this->update_control(
            'button_align',
            [
                'condition' => [
                    'button_position!' => 'ui-e-button-placement-image',
                ],
                // since product might have ajax add to cart, wich has flex display so the cart added icon do not break button layout,
                // we justify the flex content to center so justified text align button keeps text centralized. See: https://uicore.atlassian.net/browse/ELM-429
                'selectors' => [
                    '{{WRAPPER}} .ui-e-readmore' => 'align-self: {{VALUE}}; justify-content: center;',
                ]
            ]
        );
        $this->update_control(
            'button_gap',
            [
                'condition' => [
                    'button_position!' => 'ui-e-button-placement-image',
                ]
            ]
        );
    }

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

        $this->TRAIT_query_products($settings, $current_query);
        $wc_query = $this->get_query();

        $products = wc_get_products($wc_query);

        // No products found
        if (empty($products)) {
            return false;
        }

        \ob_start();
        foreach ($products as $index => $product) {
            $post_object = get_post($product->get_id());
            setup_postdata($post_object);

            $this->TRAIT_render_product($product, $index, false, true, true);

            wp_reset_postdata();
        }
        $markup = \ob_get_clean();

        return [
            'markup' => $markup,
            'total_pages' => $wc_query['total_pages']
        ];
    }

    protected function render()
    {
        if (!class_exists('WooCommerce')) {
            if ($this->is_edit_mode()) {
                echo '<p>' . esc_html__('Please enable WooCommerce to use this widget.', 'uicore-elements') . '</p>';
            }
            return;
        }

        global $wp_query;
        $settings = $this->get_settings();

        // Get product ID
        $product_id = get_the_ID();

        // Related products, on save page action at editor, triggers an fata error upon post object handle.
        // Prevent it for now by disabling the widget on edit mode. TODO: remove it after fixing the issue
        if ('related' === $settings['product-filter_post_type'] && $this->is_edit_mode()) {
            echo '<p>' . esc_html__('This widget is set to display related products. To see the results, please preview the page, or customize your widget before setting "current" query.', 'uicore-elements') . '</p>';
            return;
        }

        // Build query
        $products = $this->TRAIT_query_products($settings, $wp_query->query);
        $wc_query = $this->get_query();

        // Store widget settings in a transient
        $ID = strval($this->get_ID());
        set_transient('ui_elements_widgetdata_' . $ID, $settings, \MONTH_IN_SECONDS);

        // Get the quantity of items and creates a loop control
        $items = $settings['item_limit']['size'];
        $loops = 0;

        $this->TRAIT_render_filters($settings, true);

        // No posts found
        if (empty($products)) {
            echo '<p style="text-align:center">' . esc_html__('No products found.', 'uicore-elements') . '</p>';
        } else {
?>
            <div class="ui-e-adv-grid">
                <?php
                foreach ($products as $index => $product) {

                    // sticky posts disregards posts per page, so ending the loop if $items == $loop forces the query respects the users item limit
                    if ($settings['sticky'] && $items == $loops) {
                        break;
                    }

                    // check is is related post type
                    if ('related' === $settings['product-filter_post_type']) {
                        $product = wc_get_product($product);
                    }

                    $post_object = get_post($product->get_id());
                    setup_postdata($post_object);

                    $this->TRAIT_render_product($product, $index, false, true);

                    wp_reset_postdata(); // Reset post data after rendering each product
                    $loops++;
                }
                ?>
            </div>
<?php

            $this->TRAIT_render_pagination($settings, $product_id, true);

            // Add some data, to help rest api, to the js
            if ($this->is_option('pagination', 'yes') && $this->is_option('pagination_type', 'load_more')) {

                echo '<script>';

                // Add total pages to js variable
                echo 'window.ui_total_pages_' . esc_html($ID) . ' = ' . esc_html($wc_query['total_pages']) . ';';

                // Pass current query to js variable
                if ($settings['product-filter_post_type'] == 'current') {
                    echo 'window.ui_query_' . esc_html($ID) . ' = ' . \json_encode($wc_query) . ';';
                }

                echo '</script>';
            }
        }
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new AdvancedProductGrid());

<?php

namespace UiCoreElements\Utils;

use Elementor\Icons_Manager;

use UiCoreElements\Helper;
use UiCoreElements\Controls\Query;

use UiCoreElements\Utils\Meta_Trait;
use UiCoreElements\Utils\Post_Trait;

defined('ABSPATH') || exit();

/**
 * Product Component
 *
 * @since 1.0.11
 */

trait Product_Trait
{

    use Post_Trait,
        Meta_Trait;

    /**
     * Returns the proper query args for the product loop.
     *
     * @param array $settings Elementor controls settings.
     * @param array|WP_Query $default_query The default query variables.
     *
     * @return array|stdClass Return the products data, prepared for loop.
     */
    function TRAIT_query_products($settings, $default_query)
    {
        $post_type = $settings['product-filter_post_type'];

        switch ($post_type) {
            case 'current':
                // Makes sure widget will render some products at editor screen
                if ($this->is_edit_mode()) {
                    $query_args['post_type'] = 'product';
                } else {
                    $query_args = $default_query;

                    // Set pagination
                    $query_args['paged'] = Query::get_queried_page($settings);

                    // Set tax filters for filter component, if enabled
                    $queried_filters = Query::get_queried_filters($settings, 'product', 'product-filter');
                    $query_args['tax_query'] = empty($queried_filters['tax_query']) ? [] : $queried_filters['tax_query'];
                }

                // Set products per page
                $query_args['limit'] = Helper::get_framework_visible_posts('product');
                break;

            case 'related':
                $current_product_id = $settings['__current_post_id'] ?? [];
                $query_args['posts__in'] = Helper::get_product_related($settings['item_limit']['size'], $current_product_id);
                break;

            default:
                $query_args = Query::get_query_args('product-filter', $settings, true);
                break;
        }

        // Set total pages
        $query_args['total_pages'] = Query::get_total_pages($query_args);

        // Hide out of stock products
        if ($this->is_option('hide_out_of_stock', 'yes')) {
            $query_args['meta_query'] = [
                [
                    'key' => '_stock_status',
                    'value' => 'instock',
                    'compare' => '=',
                ]
            ];
        }

        $this->_query = $query_args;

        return wc_get_products($query_args);
    }


    // Render functions
    function get_product_image(object $product)
    {
        if ($this->is_option('image', 'yes', '!==')) {
            return;
        }

        // Get product image ID
        $pic_id = $product->get_image_id();
        if (!$pic_id) {
            return;
        }

        // Get image size
        $size = $this->get_settings_for_display('image_size_select') ?? 'uicore-medium';
?>
        <a class="ui-e-post-img-wrapp"
            href="<?php echo esc_url($product->get_permalink()); ?>"
            title="<?php echo esc_attr__('View Product:', 'uicore-elements') . ' ' . esc_attr($product->get_name()); ?>">

            <?php if ($this->get_settings_for_display('masonry') === 'ui-e-maso') { ?>
                <?php
                // Get secondary image if available
                $sec_img = $this->get_product_secondary_image($product);
                $classes = $sec_img ? 'ui-e-post-img ui-e-hover-hide' : 'ui-e-post-img';

                // Print secondary image markup if exists
                echo $sec_img ? wp_kses_post($sec_img) : '';

                // Print main product image
                echo wp_get_attachment_image($pic_id, $size, false, ['class' => $classes]);
                ?>
            <?php } else { ?>
                <?php
                // Get secondary image with size
                $sec_img = $this->get_product_secondary_image($product, $size, true);
                echo $sec_img ? wp_kses_post($sec_img) : '';
                ?>
                <div class="ui-e-post-img <?php echo $sec_img ? 'ui-e-hover-hide' : ''; ?>"
                    style="background-image:url(<?php echo esc_url(wp_get_attachment_image_url($pic_id, $size)); ?>)">
                </div>
            <?php } ?>
        </a>
    <?php
    }
    function get_product_secondary_image($product, $size = 'woocommerce_thumbnail', $bg_output = false)
    {
        // Requested only for products with change-image animation
        if ('ui-e-img-anim-change' !== $this->get_settings_for_display('anim_image')) {
            return false;
        }

        // Get product gallery image IDs
        $gallery_image_ids = $product->get_gallery_image_ids();

        // Check if there is at least one gallery image
        if (empty($gallery_image_ids)) {
            return false;
        }

        // Get the first image from the gallery
        $secondary_image_id = $gallery_image_ids[0];
        $secondary_image_url = wp_get_attachment_image_url($secondary_image_id, $size);

        // Output secondary image as <img> or background <div>
        if ($bg_output) {
            return sprintf(
                '<div class="ui-e-post-img ui-e-post-img-secondary" style="background-image: url(%s);"></div>',
                esc_url($secondary_image_url)
            );
        } else {
            return wp_get_attachment_image($secondary_image_id, $size, false, [
                'class' => 'ui-e-post-img ui-e-post-img-secondary'
            ]);
        }
    }
    function get_product_title($product, $tag)
    {
        if ($this->is_option('title', 'yes', '!==')) {
            return;
        }

    ?>
        <a href="<?php echo esc_url($product->get_permalink()); ?>"
            title="<?php echo esc_attr__('View Product:', 'uicore-elements') . ' ' . esc_html($product->get_name()); ?>">
            <<?php echo esc_html($tag); ?> class="ui-e-post-title">
                <span> <?php echo esc_html($product->get_name()); ?> </span>
            </<?php echo esc_html($tag); ?>>
        </a>
    <?php
    }

    function get_product_summary($product, $length = 55)
    {
        if (! $this->get_settings_for_display('excerpt')) {
            return;
        }

        $summary = $product->get_short_description();

        // Get product description if no summary
        if (empty($summary)) {
            $summary = $product->get_description();
        }

        // returns if no description also
        if (empty($summary)) {
            return;
        }

    ?>
        <div class="ui-e-post-text">
            <?php echo wp_kses_post(wp_trim_words($summary, $length)); ?>
        </div>
        <?php
    }

    /**
     * Render the product purchase button. Based on `TRAIT_get_button()` method.
     *
     * @param int $index The loop index.
     * @param bool $is_product. If true, will render an add to cart button.
     *
     * @return void
     * @since 1.2.1
     */
    function get_purchase_button($index)
    {
        global $product;
        $settings = $this->get_settings_for_display();

        $can_purchase = $product->is_purchasable();
        $button_classes = 'elementor-button ui-e-readmore ';

        // Get purchase button text
        if ($can_purchase) {

            // Direct add to cart text
            if ($product->is_type('simple')) {
                $button_text = empty($settings['text'])
                    ? $product->add_to_cart_text()
                    : $settings['text'];

                // Select options text
            } else {
                $button_text = empty($settings['variations_text'])
                    ? $product->add_to_cart_text()
                    : $settings['variations_text'];
            }

            // Unpurchasable product text
        } else {
            $button_text = empty($settings['unavailable_text'])
                ? $product->add_to_cart_text()
                : $settings['unavailable_text'];
        }

        // Render att slugs
        $button_slug = 'button_' . $index;
        $content_slug = 'content-wrapper_' . $index;
        $icon_slug = 'icon_' . $index;
        $text_slug = 'text_' . $index;

        // Only simple products should be ajax added to cart for UX purposes.
        // Update button classes and adds all atts used by woo on product ajax add to cart.
        if ($can_purchase && $product->is_type('simple')) {

            $button_classes .= 'button product_type_simple add_to_cart_button ajax_add_to_cart';

            $this->add_render_attribute($button_slug, [
                'data-quantity' => '1',
                'aria-describedby' => 'woocommerce_loop_add_to_cart_link_describedby_' . $product->get_id(),
                'data-product_id' => $product->get_id(),
                'data-product_sku' => $product->get_sku(),
                'aria-label' => sprintf(__('Add to cart: “%s”', 'uicore-elements'), $product->get_name()),
                'data-success-message' => sprintf(__('“%s” has been added to your cart', 'uicore-elements'), $product->get_name()),
            ]);
        }

        // Button and wrapper classes
        $this->add_render_attribute($button_slug, 'class', $button_classes);
        $this->add_render_attribute($content_slug, 'class', 'elementor-button-content-wrapper');

        if (!empty($settings['hover_animation'])) {
            $this->add_render_attribute($button_slug, 'class', 'elementor-animation-' . $settings['hover_animation']);
        }

        $btn_classes = isset($settings['icon_align'])
            ? ['elementor-button-icon', 'elementor-align-icon-' . $settings['icon_align']]
            : 'elementor-button-icon';

        // Button text and icon classes
        $this->add_render_attribute([
            $icon_slug => [
                'class' => $btn_classes
            ],
            $text_slug => [
                'class' => 'elementor-button-text',
            ],
        ]);

        // Icon alignment
        if (isset($settings['icon_align'])) {
            $this->add_render_attribute($content_slug, 'class', 'elementor-button-content');
        }

        $tbn_content = '<span ' . $this->get_render_attribute_string($content_slug) . '>';

        if (!empty($settings['icon']) || !empty($settings['selected_icon']['value'])) {
            $tbn_content .= '<span ' . $this->get_render_attribute_string($icon_slug) . '>';
            \ob_start();
            Icons_Manager::render_icon($settings['selected_icon'], ['aria-hidden' => 'true']);
            $tbn_content .= \ob_get_clean();
            $tbn_content .= '</span>';
        }

        $tbn_content .= '<span ' . $this->get_render_attribute_string($text_slug) . '>';
        $tbn_content .= $button_text;
        $tbn_content .= '</span> </span>';

        // WooCommerce add to cart button
        //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo Helper::esc_svg(apply_filters(
            'woocommerce_loop_add_to_cart_link', // WPCS: XSS ok.
            sprintf(
                '<a href="%1$s" %2$s> %3$s </a>',
                esc_url($product->add_to_cart_url()),
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                $this->get_render_attribute_string($button_slug),
                //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                Helper::esc_svg($tbn_content)
            ),
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            $product
        ));
    }

    /**
     * Renders a product item with various settings and options.
     * Important: any changes here should also be considered to `TRAIT_render_item()` from Post Component.
     *
     * @param WC_Product $product The product object.
     * @param int $index The loop index.
     * @param bool $carousel Indicates if the item needs carousel classnames.
     * @param bool $legacy Indicates if the item is using legacy classnames.
     * @param bool $is_ajax Indicates if the item is being rendered through REST API.
     *
     * @return void
     */
    function TRAIT_render_product($product, $index, $carousel = false, $legacy = false, $is_ajax = false)
    {
        $settings = $this->get_settings_for_display();

        // Classnames but checking if we the widget is APG (legacy version)
        $item_classes     = $legacy ? ['ui-e-post-item', 'ui-e-item'] : ['ui-e-item']; // Single item lower wrap class receptor
        $hover_item_class = $legacy ? 'anim_item' : 'item_hover_animation'; // item hover animation class

        // If widget is not carousel type, we set animations classes directly on item selector
        if (!$carousel) {
            $item_classes[] = 'ui-e-animations-wrp';
            $item_classes[] = $settings['animate_items'] === 'ui-e-grid-animate' ? 'elementor-invisible' : '';
            $item_classes[] = $settings[$hover_item_class] !== '' ? $settings[$hover_item_class] : '';
        } else {
            // Get entrance and item hover animation classes
            $entrance   = $this->is_option('animate_items', 'ui-e-grid-animate') ? 'elementor-invisible' : '';
            $hover      = isset($settings[$hover_item_class]) ? $settings[$hover_item_class] : '';
            $animations = sprintf('%s %s', $entrance, $hover);

            // Check if entrance or hover animation are set
            $has_animation = !empty($entrance) || !empty($hover)

            // Prints extra wrappers required by the carousel script
        ?>
            <div class="ui-e-wrp swiper-slide">
                <?php if ($has_animation) : ?>
                    <div class="ui-e-animations-wrp <?php echo esc_attr($animations); ?>">
                    <?php endif; ?>

                <?php } ?>

                <div class="<?php echo esc_attr(implode(' ', $item_classes)); ?>">
                    <article <?php post_class('product'); ?>>
                        <div class="ui-e-post-top">
                            <?php $this->get_product_image($product); ?>
                            <?php $this->get_post_meta('top'); ?>
                            <?php
                            if ($settings['button_position'] === 'ui-e-button-placement-image') {
                                $this->get_purchase_button($index);
                            }
                            ?>
                        </div>
                        <div class="ui-e-post-content">
                            <?php $this->get_post_meta('before_title'); ?>
                            <?php $this->get_product_title($product, $settings['title_tag']); ?>
                            <?php $this->get_post_meta('after_title'); ?>
                            <?php $this->get_product_summary($product, $settings['excerpt_trim']); ?>
                            <?php $this->get_post_meta('bottom');  // button
                            ?>
                            <?php
                            if (defined('UICORE_VERSION') && version_compare(UICORE_VERSION, '6.0.1', '>=') && $this->is_option('show_swatches', 'yes')) {
                                // ajax requests works under minimal conditions, wich means Swatches class is not present
                                if ($is_ajax) {
                                    require_once UICORE_INCLUDES . '/woocommerce/components/class-swatches.php';
                                }

                                // In guterberg we might experience an `Class not found` error, so we insist in checking it even
                                // though we just required the file. For more see https://uicore.atlassian.net/browse/ELM-432
                                if (class_exists('\UiCore\WooCommerce\Swatches')) {
                                    \UiCore\WooCommerce\Swatches::print_swatches($product);
                                }
                            }
                            if ($this->is_option('show_button', 'yes')) {
                                // Add match height spacing element if carousel. May look intrusive, but is the simplest method compared
                                // to absolute positioning or catching last content element to apply margin.
                                if ($carousel && $this->is_option('match_height', 'yes')) {
                            ?>
                                    <span class="ui-e-match-height"></span>
                            <?php
                                }

                                if (!isset($settings['button_position']) || empty($settings['button_position'])) {
                                    $this->get_purchase_button($index);
                                }
                            }
                            ?>
                        </div>
                    </article>
                </div>

                <?php if ($carousel) { ?>
                    </div>
                    <?php if ($has_animation) : ?>
            </div>
        <?php endif; ?>

<?php }
            }

            // keep it to avoid fatal error
            public function content_template() {}
        }

<?php
namespace UiCore\WooCommerce;

use Uicore\Helper;

defined('ABSPATH') || exit();


/**
 * Woocommerce single product page Gallery Component.
 *
 * @author Lucas Marini <lucas@uicore.co
 * @since 6.0.0
 */
class ProductGallery
{

    public function __construct() {}

    /**
     * Returns the Gallery component markup or replace the Woo template for it.
     *
     * @param string $gallery_type The gallery type to be used.
     * @param bool $hook If true, replaces the Woocommerce variation template by the swatch component instead of returning the html markup. Default is false.
     * @return void/string The gallery HTML markup or void if hooking.
     */
    public static function init(string $gallery_type, bool $hook = true)
    {

        // Get theme style if is a widget request but we want to use the framework option
        if( !$hook & $gallery_type === 'theme' ){
            $gallery_type = Helper::get_option('woos_product_gallery');
        }

        self::add_classes($gallery_type, $hook);

        // Theme Version (hooks markup to WC template)
        if($hook){

            self::enqueue_assets($gallery_type);

            if( empty($gallery_type) ){
                return;

            // Columns styles
            } else if( in_array($gallery_type, ['grid_column', 'grid_column_2']) ){

                add_action('woocommerce_single_product_zoom_enabled', '__return_false' ); // Disables zoom
                remove_action('woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 ); // Remove the default gallery
                add_action('woocommerce_before_single_product_summary', function() { self::grid_column_gallery(); }, 20); // Add the custom gallery

                return;
            }

        // Widget Version (returns markup)
        } else {

            // Columns styles
            if( in_array($gallery_type, ['grid_column', 'grid_column_2']) ) {
                return self::grid_column_gallery($gallery_type);

            }

            return \wc_get_template( 'single-product/product-image.php' );
        }
    }

    /**
     * Enqueue the product gallery assets.
     *
     * @param string $gallery_type The gallery style to be used.
     *
     * @return void
     */
    public static function enqueue_assets(string $gallery_type)
    {
        if( empty($gallery_type) ){
            return;
        }

        \add_action('wp_enqueue_scripts', function() use ($gallery_type) {

             // Left thumbnails
            if( $gallery_type === 'left_thumbs' ){
                wp_enqueue_style('uicore-product-gallery-thumbs');

            // Columns
            } else {
                wp_enqueue_script('uicore-product-gallery-columns');
                wp_enqueue_style('uicore-product-gallery-columns');
                // Disables Elementor lightbox
                \wp_add_inline_script('elementor-frontend', 'document.addEventListener("DOMContentLoaded", function() {
                        elementorFrontend.getKitSettings().global_image_lightbox = false;
                });
                ','after');
            }
        });
    }

    /**
     * Add and remove custom classes to Woo product gallery default template. Usefull for styles that don't need custom markup, but classes.
     *
     * @param string $gallery_type The gallery type to be used.
     *
     * @return void
     */
    public static function add_classes(string $gallery_type, bool $hook)
    {

        $custom_classes = [
            'left_thumbs' => 'uicore-gallery-left-thumbs',
        ];

        // Before adding any classes, if is widget type request, remove all previolsy added classes since we would inherit them
        if( !$hook ){
            add_filter('woocommerce_single_product_image_gallery_classes', function($classes) use ($custom_classes) {
                $classes = array_diff($classes, $custom_classes);
                return $classes;
            });
        }

        // Left thumb classes
        if( $gallery_type === 'left_thumbs'){
            add_filter('woocommerce_single_product_image_gallery_classes', function($classes) use ($custom_classes) {
                $classes[] = $custom_classes['left_thumbs'];
                return $classes;
            });
        }
    }

    /**
     * Dequeue the product gallery assets enqueued by the theme. Usefull if the page is built with a theme builder and you want to clean any assets toprevents conflicts betweeen widget assets.
     */
    public static function dequeue_theme_assets(){

        $style = Helper::get_option('woos_product_gallery');
        // Default woo style
        if( empty($style) ){
            return;

        // Left thumbnails
        } else if( $style === 'left_thumbs'){
            wp_dequeue_style('uicore-product-gallery-thumbs');

        // Column styles
        } else {
            wp_enqueue_script('uicore-product-gallery-columns');
            wp_dequeue_style('uicore-product-gallery-columns');
        }
    }

    /**
     * Render the grid column gallery. This code is based on the `product-image.php` template from Woocommerce and should keep up with future updates.
     *
     * @param string $gallery_style The gallery style to be used. Default is false.
     * @return string The gallery HTML markup.
     */
    public static function grid_column_gallery($gallery_style = false) {
        global $product;

        $columns = '';
        $post_thumbnail_id = $product->get_image_id();
        $wrapper_classes   = [
            'woocommerce-product-gallery',
            'woocommerce-product-gallery--' . ( $post_thumbnail_id ? 'with-images' : 'without-images' ),
            'images',
        ];
        $attachment_ids = $product->get_gallery_image_ids();

        // Since the two columns gallery is built with an css variable, from theme options, at woo-css,
        // in widget case we overwrite this variable by adding it at a lower level (theme is added on div.product)
        if($gallery_style !== false){
            $columns = $gallery_style === 'grid_column_2' ? '--uicore-gallery-columns: 2;' : '--uicore-gallery-columns: 1;';
        }

        ?>
        <div class="<?php echo esc_attr( implode( ' ', array_map( 'sanitize_html_class', $wrapper_classes ) ) ); ?>"
             style="opacity: 0; transition: opacity .25s ease-in-out; <?php echo esc_attr($columns);?>">
            <div class="woocommerce-product-gallery__grid-wrapper uicore-grid-gallery">

                <?php if($post_thumbnail_id) : ?>
                    <div class="woocommerce-product-gallery__image-wrap main-image">
                        <?php echo wc_get_gallery_image_html( $post_thumbnail_id, true ); ?>
                    </div>
                <?php else :
                    echo sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) );
                endif; ?>

                <?php
                    if($attachment_ids) :
                        foreach ( $attachment_ids as $attachment_id ) :
                ?>
                            <div class="woocommerce-product-gallery__image-wrap">
                                <?php echo wc_get_gallery_image_html( $attachment_id ); ?>
                            </div>
                <?php
                        endforeach;
                    endif;
                ?>

                <?php if ( !$post_thumbnail_id && empty($attachment_ids) ) : ?>
                    <div class="woocommerce-product-gallery__grid-item woocommerce-product-gallery__image--placeholder">
                        <?php sprintf( '<img src="%s" alt="%s" class="wp-post-image" />', esc_url( wc_placeholder_img_src( 'woocommerce_single' ) ), esc_html__( 'Awaiting product image', 'woocommerce' ) ); ?>
                    </div>
                <?php endif; ?>

            </div>
        </div>
        <?php
    }
}
new ProductGallery();

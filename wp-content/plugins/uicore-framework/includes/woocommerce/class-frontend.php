<?php
namespace UiCore\WooCommerce;
use UiCore\Helper as Helper;
use UiCore\Pagination;
use UiCore\Sidebar;
use UiCore\WooCommerce\Swatches;
use UiCore\WooCommerce\ProductGallery;
use Uicore\Woocommerce\Product_Tab;

defined('ABSPATH') || exit();


/**
 * Frontend WooCommerce
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.2
 */
class Frontend
{

    /**
     * Construct Frontend
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2'
     */
    public function __construct()
    {

        $this->markup_filters();
        // //Add Woo Support
        add_action('after_setup_theme', [$this, 'theme_support']);

        // hook on this to have all conditions
        add_action('wp', function () {

            if(self::is_woo()){
                //Add custom classes to body
                add_filter('body_class', [$this, 'add_body_class']);

                // Handle single product updates
                if(\is_product()){


                    //Add custom swatches
                    Swatches::init();

                    //Add custom gallery
                    ProductGallery::init( Helper::get_option('woos_product_gallery') );

                    //Add share buttons
                    ShareLinks::init();

                    //Move tabs in right or left part (after gallery or after meta)
                    if(Helper::get_option('woos_tabs_position') != ''){
                        $this->move_description_tabs();
                    }

                    //Customize Product Tabs
                    Product_Tab::init( Helper::get_option('woos_tabs_style'), true );

                    //Products page title option
                    if(Helper::get_option("woocommerces_title") === "simple page title"){
                        add_filter('uicore_is_pagetitle', '__return_false');
                    }elseif(Helper::get_option("woocommerces_title") != "simple page title" && Helper::get_option("pagetitle") === "true"){
                        //remove default woo title and  woo prod breadcrumb
                        remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_title', 5);
                        remove_action( 'woocommerce_before_main_content','woocommerce_breadcrumb', 20, 0);
                    }

                    //Hide related products
                    if(Helper::get_option('woos_related') === 'false'){
                        remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20);
                    }

                    $is_theme_builder = apply_filters('uicore_is_template', false);
                    if ( $is_theme_builder ) {
                        // Dequeue some woo component theme assets to avoid conflicts with widget assets
                        add_action('wp_enqueue_scripts', function(){
                            Product_Tab::manage_assets();
                            ProductGallery::dequeue_theme_assets();
                        }, 20);
                    }


                    // if woos_ajax_add_to_cart is true
                    if(Helper::get_option('woos_ajax_add_to_cart') === 'true'){
                        // Add the custom button after the default Add to Cart button
                        add_action( 'woocommerce_after_add_to_cart_button',function() {
                            global $product;

                            // Ensure we're on the single product page
                            if ( is_product() ) {
                                // Output the custom button
                                echo '<button type="submit"
                                    name="add-to-cart"
                                    value="' . esc_attr( $product->get_id() ) . '"
                                    class="single_add_to_cart_button button alt ajax_add_to_cart add_to_cart_button uicore-main-add-to-cart"
                                    data-product_id="' . esc_attr( $product->get_id() ) . '">
                                    ' . esc_html( $product->single_add_to_cart_text() ) . '
                                </button>
                                ';
                            }
                        });
                    }
                   


                }

                // Handle shop and archive updates
                if(\is_shop() || \is_post_type_archive('product')  || \is_product_taxonomy()){

                    //Page title option
                    if(Helper::get_option("pagetitle") === "true"){
                        //remove default woo title and  woo archive breadcrumb
                        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
                        add_filter('woocommerce_show_page_title', '__return_false');
                    }

                    // Sidebar toggle
                    if(Helper::get_option("woo_filters_toggle") === "true" &&
                        Helper::get_option("woocommerce_sidebar_id") !== 'none' &&
                        Helper::get_option("woocommerce_sidebar") != 'side drawer'){
                        add_action('woocommerce_before_shop_loop', [$this,'add_sidebar_toggle'], 9);
                    }

                    //mobile sidebar toggle
                    if(Helper::get_option('woocommerce_sidebar_id') != 'none'){
                        add_action('uicore_after_body_content', [$this,'get_filters_drawer_content']);
                    }

                }

            }
        });



        /*
        *those need to work on any page synce we need them in the grid
        * EG: GLOBAL CODE
        */

         //grid zoom effect (we handle it here since the grid can be used in multiple places)
         if(Helper::get_option('woo_hover_effect') === 'zoom'){
            $this->add_hover_zoo_markup();
        }else if(Helper::get_option('woo_hover_effect') === 'change_image'){
            //we also need to add the zoom wrapper here since is similar to the zoom effect combined with image change
            $this->add_hover_zoo_markup();
            //add image change on hover
            add_action('woocommerce_before_shop_loop_item', [$this, 'add_hover_image'], 20);
        }


        //add short description after title in grid if woo_quick_desc is true
        if(Helper::get_option('woo_quick_desc') === 'true'){
            add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_single_excerpt', 6);
        }


        //add rating after title in grid if woo_rating is true
        if(Helper::get_option('woo_rating') === 'true'){
            add_action('woocommerce_shop_loop_item_title', 'woocommerce_template_loop_rating', 8);
        }
        //remove it from the original position
        remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);

        $this->handle_grid_add_to_cart_style();

        //add swatches to grid
        if(Helper::get_option('woo_swatches') === 'true'){
            add_action( 'woocommerce_after_shop_loop_item', [Swatches::class,'print_swatches'], 10);
        }

        // //filter posts number
        add_filter('loop_shop_per_page', [$this, 'filter_posts_number']);
        // //Filter Archieve columns number
        add_filter('loop_shop_columns', [$this, 'filter_col_number']);
        // //Ajax Item in cart no for header icon.
        add_filter('woocommerce_add_to_cart_fragments', [$this, 'mini_cart_count']);

        add_filter('woocommerce_post_class', [$this, 'animate_class_on_prosts']);

        add_filter( 'template_include',[$this, 'tb_check'], 20, 1);

        //deque woocomerce-layout.css
        add_action('wp_enqueue_scripts', function(){
            wp_dequeue_style('woocommerce-layout');
        }, 1000);

        //add to cart ajax
        add_action('wp_ajax_uicore_ajax_add_to_cart',[$this, 'ajax_add_to_cart'],90);
        add_action('wp_ajax_nopriv_uicore_ajax_add_to_cart',[$this, 'ajax_add_to_cart'],90);


        // new Pagination;
        require_once UICORE_INCLUDES . '/templates/pagination.php';
    }

    function tb_check( $template)
    {
        if(self::is_woo()){  
            //If there is a theme builder template for it replace it with the default one
            $is_theme_builder_template = apply_filters('uicore_is_template', false);
            if(strpos($template,'product') !== false  && $is_theme_builder_template){
                $template = locate_template( array( 'index.php' ) );
            }
        }
        return $template;
    }


    /**
     * Add Conditional body classes
     *
     * @param array $classes
     * @return array $classes
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function add_body_class(array $classes)
    {
        $woocommerce_sidebar = Helper::get_option('woocommerce_sidebar_id', 'none');
        $woocommerce_sidebar_pos = Helper::get_option('woocommerce_sidebar', 'left');
        $woocommerce_single_sidebar = Helper::get_option('woocommerces_sidebar_id', 'none');
        $woocommerce_single_sidebar_pos = Helper::get_option('woocommerces_sidebar', 'left');

        $newclasses = [
            (is_shop() || is_product_taxonomy()) && $woocommerce_sidebar !== 'none'
                ? 'uicore-sidebar-' . $woocommerce_sidebar_pos
                : null,
            is_product() && $woocommerce_single_sidebar !== 'none'
                ? 'uicore-sidebar-' . $woocommerce_single_sidebar_pos
                : null,
            'uicore-woo-page'
        ];

        return array_merge($classes, $newclasses);
    }

    function animate_class_on_prosts($classes){
        $newclasses = ['uicore-animate'];
        return array_merge($classes, $newclasses);
    }

    /**
     * Filter posts to return a number that fit's the grid.
     *
     * @param object $query
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    function filter_posts_number()
    {
        return Helper::get_option('woocommerce_posts_number');
    }

    /**
     * Filter posts to return a number that fit's the grid.
     *
     * @param object $query
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    function filter_col_number()
    {
        return Helper::get_option('woocommerce_col');
    }

    /**
     * Filter posts to return a number that fit's the grid.
     *
     * @param object $query
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 5.0.6
     */
    function filter_title($title)
    {
        $is_simple =  (Helper::get_option("woocommerces_title") === "simple page title");
        if($is_simple){
            return $title;
        }else{
            return false;
        }
    }

    /**
     * Filter posts to return a number that fit's the grid.
     *
     * @param object $query
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    function theme_support()
    {
        add_theme_support('woocommerce');
        add_theme_support('wc-product-gallery-zoom');
        add_theme_support('wc-product-gallery-lightbox');
        add_theme_support('wc-product-gallery-slider');
    }

    /**
     * Filter posts to return a number that fit's the grid.
     *
     * @param object $query
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    function mini_cart_count($fragments)
    {
        ob_start(); ?>
        <span  id="uicore-count-update">
            <?php echo WC()->cart->get_cart_contents_count(); ?>
        </span>
        <?php
        $fragments['#uicore-count-update'] = ob_get_clean();
        return $fragments;
    }


    /**
     * Filter posts to return a number that fit's the grid.
     *
     * @param object $query
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.2
     */
    function markup_filters()
    {

        //remove woo default sidebar
        remove_action('woocommerce_sidebar', 'woocommerce_get_sidebar', 10);

        // Remove breadcrumbs from their original location and move them to the top of the product title
        remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
        add_action('woocommerce_single_product_summary', 'woocommerce_breadcrumb', 4);

        //wrapp summary flas and gallery to one div
        add_action('woocommerce_before_single_product_summary', function(){
            echo '<div class="uicore-summary-gallery">';
        }, 4);
        add_action('woocommerce_before_single_product_summary', function(){
            echo '</div>';
        }, 999);


        remove_action('woocommerce_after_shop_loop', 'woocommerce_pagination', 10);
        add_action(
            'woocommerce_after_shop_loop',
            function () {
                new Pagination();
            },
            10
        );


        //Wrap the woo pages
        remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
        remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);

        add_action('woocommerce_before_main_content', function ()
            {
                $extra_class = '';
                if(\is_archive()){
                    $extra_class = ' uicore-archive uicore-shop-grid uicore-shop-animation';
                }
                ?>
                <main id="main" class="site-main uicore-section uicore-box uicore">
                <div class="uicore uicore-container uicore-content-wrapper uicore-woo">
                    <div class="uicore-post-content<?php echo $extra_class; ?>">
                    <?php
            }
        );

        add_action('woocommerce_after_main_content', function()
            {
                ?>
                    </div>
                <?php do_action('uicore_sidebar'); ?>
                </div>
            </main>
            <?php
            }
        );

        add_filter('woocommerce_before_shop_loop',
            function(){
                echo '<div class="uicore-animate">';
            },
         -999);

        add_filter('woocommerce_before_shop_loop',
            function (){
                echo '</div>';
            },
         999);


        add_filter('woocommerce_output_related_products',
            function (){
                echo '</div><div>';
            },
         11); //woocommerce_output_product_data_tabs - 10



        //wrap product page content in one section
        add_action('woocommerce_before_single_product_summary', ['\UiCore\WooCommerce\Frontend', 'start_summary_wrapper'], 1);
        add_action('woocommerce_after_single_product_summary', ['\UiCore\WooCommerce\Frontend', 'close_summary_wrapper'], 1);


        add_action('woocommerce_product_review_comment_form_args', [$this,'display_average_rating'], 10);

        // Wrap the shop page header elements
        add_action( 'woocommerce_before_shop_loop',
            function(){
                echo '<div class="uicore-header-elements-wrp uicore-right">';
            },
        10);
        add_action( 'woocommerce_before_shop_loop',
            function(){
                echo '</div>';
            },
        30);

    }

    public static function start_summary_wrapper(){
        echo '<div class="uicore-summary-wrapp">';
    }
    public static function close_summary_wrapper(){
        echo '</div>';

    }

    /**
     * Display average rating and review count for a product.
     *
     * @author Andrei Voica <andrei@uicore.co>
     * @param array $comment_form The comment form arguments.
     * @return array The modified comment form arguments.
     * @since 6.0.0
     */
    public static function display_average_rating($comment_form)
    {
        global $product;

        // Get the average rating and the total number of reviews
        $average      = $product->get_average_rating();
        $review_count = $product->get_review_count();

        \ob_start();
        if ($review_count > 0) {
            // Calculate the width for the star rating
            $rating_percentage = ($average / 5) * 100;

            // Output the rating in the desired HTML structure
            echo '<div class="uicore-average-count-wrap">';
                echo '<h1 class="uicore-average-count">' . esc_html(number_format($average, 2)) . '</h1>';
                echo '<div class="star-rating" role="img" aria-label="Rated ' . esc_attr(number_format($average, 2)) . ' out of 5">';
                    echo '<span style="width:' . esc_attr($rating_percentage) . '%">Rated <strong class="rating">' . esc_html(number_format($average, 2)) . '</strong> out of 5</span>';
                echo '</div>';
                echo '<span class="total-num">Based on ' . esc_html($review_count) . ' reviews</span>';
            echo '</div>';
        }
        $comment_form['title_reply'] = ob_get_clean();
        return $comment_form;
    }


    /**
     *  Move tabs in right or left part (after gallery or after meta)
     *
     * @author Andrei Voica <andrei@uicore.co>
     * @return string
     * @since 6.0.0
     */
    function move_description_tabs(){
        // Remove tabs from the default location
        remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );

        $position = Helper::get_option('woos_tabs_position');

        if($position === 'below_meta'){
            // Add tabs after the product meta
            add_action( 'woocommerce_share', 'woocommerce_output_product_data_tabs', 999 );
        }else if($position === 'below_gallery'){
            // Add tabs after the product gallery
            add_action( 'woocommerce_before_single_product_summary', 'woocommerce_output_product_data_tabs', 20 );
        }

    }

   /**
     * Adds the Toggle Sidebar button and wrap the adjacent header elements for alignment.
     *
     * @author Lucas Marini <lucas@uicore.co>
     * @return string
     * @since 6.0.0
     */
    function add_sidebar_toggle() {
        $is_hidden = Helper::get_option('woocommerce_sidebar') === 'top';
        echo '
            <button class="uicore-sidebar-toggle">
                <span class="filters-toggle-icon">
                    <span>
                        <span class="line top" style="transform: translateX('.($is_hidden ? '8' : '0').'px);"></span>
                        <span class="line bottom" style="transform: translateX('.($is_hidden ? '0' : '8').'px);"></span>
                    </span>
                </span>
                <span class="text-wrap">
                    <span class="show" style="display: '.($is_hidden ? 'block' : 'none').';">' . __('Show', 'uicore-framework') . '</span>
                    <span class="hide" style="display: '.($is_hidden ? 'none' : 'block').';">' . __('Hide', 'uicore-framework') . '</span>
                    Filters
                </span>
            </button>';

    }

    function add_hover_zoo_markup() {
        add_action(
            'woocommerce_before_shop_loop_item',
            function () {
                echo '<div class="uicore-zoom-wrapper">';
            },
            20
        );
        add_action(
            'woocommerce_before_shop_loop_item_title',
            function () {
                echo '</div>';
            },
            10
        );
    }

    function handle_grid_add_to_cart_style() {
        $style = Helper::get_option('woo_add_to_cart_style');

        switch($style){

            case 'reveal':
                // Add to cart&price hover effect
                remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_price', 10);
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                add_action(
                    'woocommerce_after_shop_loop_item',
                    function () {
                        echo '<div class="uicore-reveal-wrapper"><div class="uicore-reveal">';
                    },
                    8
                );
                add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_price', 9);
                add_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                add_action(
                    'woocommerce_after_shop_loop_item',
                    function () {
                        echo '</div></div>';
                    },
                    11
                );
                break;
            case 'link':
                break;

            case 'btn_hover':
                ///move button just before the title(remove it from where it is and add it before title)
                remove_action('woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10);
                add_action('woocommerce_before_shop_loop_item_title', 'woocommerce_template_loop_add_to_cart', 12);
                //no break so it will add the 'alt' class to the button

            default:
                add_filter('woocommerce_loop_add_to_cart_link', [$this, 'add_to_cart_btn_update'], 10);
                break;

        }
    }

    public function add_to_cart_btn_update($button){
        // Adds 'alt' class to the button
        return str_replace('class="', 'class="alt ', $button);
    }

    static function get_filters_drawer_content(){
        $show_on_desktop = Helper::get_option('woocommerce_sidebar') === 'side drawer';
        ?>
        <div class="ui-drawer-toggle ui-filters-buton">
            <span class="filters-toggle-icon">
                <span>
                    <span class="line top" style="transform: translateX(8px);"></span>
                    <span class="line bottom" style="transform: translateX(0px);"></span>
                </span>
            </span>
        </div>
        <div class="ui-drawer ui-filters-drawer">
            <div class="ui-sd-backdrop"></div>
            <div class="ui-drawer-wrapp">
                <div class="ui-drawer-content">
                    <?php
                        Sidebar::get_sidebar(Helper::get_option('woocommerce_sidebar_id'),false);
                    ?>
                </div>
                <button class="ui-drawer-toggle ui-close" aria-label="close">×</button>
            </div>
        </div>
        <?php
    }

    static function add_hover_image(){
        global $product;
        $attachment_ids = $product->get_gallery_image_ids();
        $image_id = isset($attachment_ids[0]) ? $attachment_ids[0] : null;
        if($image_id){
            // echo img with srcset
            echo wp_get_attachment_image($image_id, 'woocommerce_thumbnail', false, ['class' => 'uicore-hover-image']);
        }
    }

    	/**
	 * AJAX add to cart.
	 */
	public static function ajax_add_to_cart() {
		ob_start();

		// phpcs:disable WordPress.Security.NonceVerification.Missing
		if ( ! isset( $_POST['product_id'] ) ) {
			return;
		}

		$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_POST['product_id'] ) );
		$product           = wc_get_product( $product_id );
		$quantity          = empty( $_POST['quantity'] ) ? 1 : wc_stock_amount( wp_unslash( $_POST['quantity'] ) );
		$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
		$product_status    = get_post_status( $product_id );
		$variation_id      = 0;
		$variation         = array();

        if ( $product && 'variation' === $product->get_type() ) {
            $variation = isset($_POST['variation']) ? $_POST['variation'] : [];
            $variation_id = isset($_POST['variation_id']) ? $_POST['variation_id'] : 0;
        }

		if ( $passed_validation && false !== WC()->cart->add_to_cart( $product_id, $quantity, $variation_id, $variation ) && 'publish' === $product_status ) {

			do_action( 'woocommerce_ajax_added_to_cart', $product_id );

			if ( 'yes' === get_option( 'woocommerce_cart_redirect_after_add' ) ) {
				wc_add_to_cart_message( array( $product_id => $quantity ), true );
			}

			\WC_AJAX::get_refreshed_fragments();

		} else {
            $error_message = wc_get_notices( 'error' );
            wc_clear_notices();
			// If there was an error adding to the cart, redirect to the product page to show any errors.
			$data = array(
				'error'       => true,
                'error_messages' => $error_message,
				'product_url' => apply_filters( 'woocommerce_cart_redirect_after_error', get_permalink( $product_id ), $product_id ),
			);

			wp_send_json( $data );
		}
		// phpcs:enable
	}

    /**
     * Function to check if the current template is a theme builder template and modify the template include filter accordingly.
     *
     * @return void
     */
    function theme_builder_template() {
        $is_theme_builder_template = apply_filters('uicore_is_template', false);
        if($is_theme_builder_template){
            add_filter('template_include', function($template){
                if(\strpos($template,'single-product.php') !== false){
                    //force out theme single.php
                    return locate_template( array( 'index.php' ) );
                }
                return $template;
            });
        }
    }
    public static function is_woo()
    {
        return (is_post_type_archive('product')  || is_product_taxonomy() || is_product() || is_woocommerce() || is_shop() || is_cart() ||
        is_account_page() ||
        is_checkout() ||
        is_wc_endpoint_url());
    }
}
new Frontend();

<?php
namespace UiCore;

defined('ABSPATH') || exit();
/**
 * UiCore Utils Functions
 */
class Helper
{
    /**
     * Page Option Setting filter
     *
     * @param  string $setting - page option setting name
     * @param  string $global_setting - Theme options setting name
     * @param  string $default - default value
     * @param  mixed $post - Post ID
     *
     * @return string // setting value
     */
    public static function po($setting, $global_setting, $default, $post)
    {
        //Check if is blog and get the meta from blog page
        $is_blog =
			is_search() ||
            is_post_type_archive('post') ||
            is_home() ||
            is_category() ||
            is_day() ||
            is_month() ||
            is_author() ||
            is_year() ||
            is_tag();
        if ($is_blog) {
            $post = get_option('page_for_posts');
        }
        //Check if is Portfolio and get the meta from blog page
        $is_portfolio = is_post_type_archive('portfolio') || is_tax('portfolio_category');
        if ($is_portfolio) {
            $page = self::get_option('portfolio_page');

            if (isset($page['id'])) {
                $post = $page['id'];
            }
        }

        //Extra Check for using woocomerce functions
        if (class_exists('WooCommerce')) {
            $is_shop = is_product_taxonomy() || is_shop();
            if ($is_shop) {
                $post = get_option('woocommerce_shop_page_id');
            }
        }

        $meta = get_post_meta($post, 'page_options', true);

        //if is false don't look for it
        if($global_setting){
            $global_setting = self::get_option($global_setting);
        }

        if (!Helper::isJson($meta)) {
            if(!$global_setting){
                return $default;
            }else{
                return $global_setting;
            }
        } else {
            $meta = Settings::po_get_page_settings($post);

            if (isset($meta[$setting])) {
                if ($meta[$setting] == 'theme default') {
                    if(!$global_setting){
                        return $default;
                    }else{
                        return $global_setting;
                    }
                }
                if ($meta[$setting] == 'enable') {
                    return 'true';
                }
                if ($meta[$setting] == 'disable') {
                    return 'false';
                }
                if ($meta[$setting] == '') {
                    if(!$global_setting){
                        return $default;
                    }else{
                        return $global_setting;
                    }
                } else {
                    return $meta[$setting];
                }
            } else {
                if(!$global_setting){
                    return $default;
                }else{
                    return $global_setting;
                }
            }
        }
    }

    /**
     * isJson - Check if sting is Json
     *
     * @param  mixed $string
     *
     * @return bolean
     */
    public static function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }

    public static function get_separator()
    {
        return '<span class="uicore-meta-separator"></span>';
    }

    public static function delete_frontend_transients()
    {
        delete_transient('uicore_pages');
        delete_transient('uicore_library_v3_blocks');
        delete_transient('uicore_library_v3_pages');
        delete_transient('uicore_library_v3_portfolio');
        delete_transient('uicore_library_v3__type_archive');
        delete_transient('uicore_library_v3__type_footer');
        delete_transient('uicore_library_v3__type_mm');
        delete_transient('uicore-main-menu');
        delete_transient('uicore-footer-markup');
        delete_transient('uicore-social-markup');
        delete_transient('uicore-style');
        delete_transient('uicore-style-json');

        //clear cache of all know cache plugins
        if (function_exists('sg_cachepress_purge_cache')) {
            sg_cachepress_purge_cache();
        }
        if (function_exists('w3tc_flush_all')) {
            w3tc_flush_all();
        }
        if (function_exists('wp_cache_clear_cache')) {
            wp_cache_clear_cache();
        }
        if (function_exists('wp_fastest_cache_clear_cache')) {
            wp_fastest_cache_clear_cache();
        }
        if (function_exists('wpfc_clear_all_cache')) {
            wpfc_clear_all_cache();
        }
        if (function_exists('rocket_clean_domain')) {
            rocket_clean_domain();
        }
    }

    public static function parse_css($css)
    {
        preg_match_all( '/(?ims)([a-z0-9\s\.\:#_\-@,]+)\{([^\}]*)\}/', $css, $arr);
        $result = array();
        foreach ($arr[0] as $i => $x){
            $selector = trim($arr[1][$i]);
            $rules = explode(';', trim($arr[2][$i]));
            $rules_arr = array();
            foreach ($rules as $strRule){
                if (!empty($strRule)){
                    $rule = explode(":", $strRule);
                    $rules_arr[trim($rule[0])] = trim($rule[1]);
                }
            }

            $selectors = explode(',', trim($selector));
            foreach ($selectors as $strSel){
                $result[$strSel] = $rules_arr;
            }
        }
        return $result;

    }

    	/**
	 * Return Theme options.
	 *
	 * @param  string $option       Option key.
	 * @param  string $default      Option default value.
	 * @param  string $deprecated   Option default value.
	 * @return Mixed               Return option value.
	 */
	static function get_option( $option, $default = '') {

		$theme_options = ThemeOptions::get_front_options_all();

		$value = ( isset( $theme_options[ $option ] ) && '' !== $theme_options[ $option ] )
        ? $theme_options[ $option ]
        : $default;

		return apply_filters( "uicore_get_option_{$option}", $value, $option, $default );
	}

    static function is_full()
    {
        if(function_exists('tutor_utils')){
            global $wp_query;

            if ( ! empty($wp_query->query['tutor_student_username'])){
                return true;
            }
            if(is_singular(['lesson', 'tutor_quiz']) ){
                return true;
            }
            $dashboard_page = (int) tutor_utils()->get_option('tutor_dashboard_page_id');
            if($dashboard_page  === get_the_ID()){
                return true;
            }
        }

        return false;
    }

    /**
     * Retrive the actual css color value (filter globals)
     *
     * @param string $color
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.2.2
     */
    static function get_css_color($color, $fallback = null)
    {
        if(!$color){
            return self::get_css_color($fallback);
        }
        $globals = ['Primary', 'Secondary', 'Accent', 'Headline', 'Body', 'Dark Neutral', 'Light Neutral', 'White'];
        if(in_array($color, $globals)){
            $set = strtolower( $color[0] ) . 'Color';
            $color = Helper::get_option($set);
        }
        return $color;
    }

    static function get_taxonomy($name)
    {
        global $post;

        $categories = get_the_terms( $post->ID, $name );
        if ( ! $categories || is_wp_error( $categories ) ) {
            return false;
        }

        $categories = array_values( $categories );
        foreach ($categories as $t) {
            $term_name[] =
                '<a href="' . get_term_link($t) . '" title="View ' . $t->name . ' posts">' . $t->name . '</a>';
        }
        $category = implode(', ', $term_name);

        return $category;
    }

    static function get_reading_time()
    {
        global $post;
        // get the content
        $the_content = $post->post_content;
        // count the number of words
        $words = str_word_count( strip_tags( $the_content ) );
        // rounding off and deviding per 200 words per minute
        $minute = floor( $words / 200 );

        // calculate the amount of time needed to read
        $minute = $minute ? $minute : 1;
        $duration_format = _n('%d min read', '%d min read', $minute, 'uicore-framework');
        return sprintf($duration_format, $minute);
    }

    static function register_widget_style($name,$deps=[])
    {
        wp_register_style('uicore-'.$name, UICORE_ASSETS . '/css/elements/'.$name.'.css',$deps,UICORE_VERSION);
    }
    static function register_widget_script($name,$deps=[])
    {
        wp_register_script('uicore-'.$name, UICORE_ASSETS . '/js/elements/'.$name.'.js',$deps,UICORE_VERSION,true);
    }

    public static function get_related($filter, $number)
    {
        global $post;

        $args = [];

        if ($filter == 'category') {
            $categories = get_the_category($post->ID);

            if ($categories) {
                $category_ids = [];
                foreach ($categories as $individual_category) {
                    $category_ids[] = $individual_category->term_id;
                }

                $args = [
                    'category__in' => $category_ids,
                    'post__not_in' => [$post->ID],
                    'posts_per_page' => $number,
                    'ignore_sticky_posts' => 1,
                ];
            }
        } elseif ($filter == 'tag') {
            $tags = wp_get_post_tags($post->ID);

            if ($tags) {
                $tag_ids = [];
                foreach ($tags as $individual_tag) {
                    $tag_ids[] = $individual_tag->term_id;
                }
                $args = [
                    'tag__in' => $tag_ids,
                    'post__not_in' => [$post->ID],
                    'posts_per_page' => $number,
                    'ignore_sticky_posts' => 1,
                ];
            }
        } else {
            $args = [
                'post__not_in' => [$post->ID],
                'posts_per_page' => $number,
                'orderby' => 'rand',
            ];
        }

        $related_query = new \wp_query($args);

        if ($related_query->have_posts()) {
            return $related_query;
        } else {
            return false;
        }
    }

    static function get_post_navigation($prev_text, $next_text)
    {
        $post_type = (get_post_type() == 'post') ? 'blog' : get_post_type();
        $is_loop_enabled = (Helper::get_option($post_type.'s_loop_navigation', 'false') === 'true');

        ?>
        <div class="ui-post-nav">
            <div class="ui-post-nav-item ui-prev">
            <?php
            $prev_post = get_previous_post();
            //check if there is a previous post if not get the last post
            if ( $is_loop_enabled && empty( $prev_post ) ){
                $prev_post = get_posts( array(
                    'numberposts' => 1,
                    'order' => 'DESC',
                    'post_type' => get_post_type(),
                    'suppress_filters' => false
                ) );
                $prev_post = $prev_post[0];
            }
            if ( ! empty( $prev_post ) ): ?>
                <a href="<?php echo get_permalink( $prev_post->ID ); ?>" rel="prev">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="none" stroke="#444" stroke-width="2" x="0px" y="0px" viewBox="0 0 24 24" xml:space="preserve" width="24" height="24">
                    <g>
                        <line stroke-miterlimit="10" x1="22" y1="12" x2="2" y2="12" stroke-linejoin="miter" stroke-linecap="butt"></line>
                        <polyline stroke-linecap="square" stroke-miterlimit="10" points="9,19 2,12 9,5 " stroke-linejoin="miter"></polyline>
                    </g>
                </svg>
                <span class="ui-post-nav-info"><?php echo $prev_text; ?></span>
                    <h4 title="<?php echo apply_filters( 'the_title', $prev_post->post_title, $prev_post->ID ); ?>"><?php echo apply_filters( 'the_title', $prev_post->post_title, $prev_post->ID ); ?></h4>
                </a>
            <?php endif; ?>
            </div>
            <div class="ui-post-nav-item ui-next">
            <?php
            $next_post = get_next_post();
            //if next post does not exist then get the first post
            if ( $is_loop_enabled && empty( $next_post ) ){
                $next_post = get_posts( array(
                    'numberposts' => 1,
                    'order' => 'ASC',
                    'post_type' => get_post_type(),
                ) );
                $next_post = $next_post[0];
            }
            if ( ! empty( $next_post ) ): ?>
                <a href="<?php echo get_permalink( $next_post->ID ); ?>" rel="next">
                <svg version="1.1" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" fill="none" stroke="#444" stroke-width="2" x="0px" y="0px" viewBox="0 0 24 24" xml:space="preserve" width="24" height="24">
                    <g transform="rotate(180 12,12) ">
                        <line stroke-miterlimit="10" x1="22" y1="12" x2="2" y2="12" stroke-linejoin="miter" stroke-linecap="butt"></line>
                        <polyline stroke-linecap="square" stroke-miterlimit="10" points="9,19 2,12 9,5 " stroke-linejoin="miter"></polyline>
                    </g>
                </svg>
                <span class="ui-post-nav-info"><?php echo $next_text; ?></span>
                   <h4 title="<?php echo apply_filters( 'the_title', $next_post->post_title, $next_post->ID ); ?>"><?php echo apply_filters( 'the_title', $next_post->post_title, $next_post->ID ); ?></h4>
                </a>
            <?php endif; ?>
            </div>
        </div>
        <?php
    }

    static function activate_ep()
    {
        update_option('element_pack_license_key', 'e3ebfd6c-188d-45a6-b90d-2e26308d6047');
        update_option('element_pack_license_email', 'email@email.com');
        update_option('dci_allow_status_dci_' . str_replace('-', '_', sanitize_title('Element Pack Pro') . '_' . md5('Element Pack Pro')), 'disallow');

    }

    static function handle_connect($type, $data=null)
    {
        $local_data = get_option('uicore_connect', [
            'url' => '',
            'token'=> '',
        ]);
        switch ($type) {

            case 'get':
                return $local_data;
                break;

            case 'local_check':
                if($local_data['token']){
                    return ["status"=>"success", 'data' => $local_data];
                }else{
                    return ["status"=>"not_connected"];
                }
                break;

            case 'update':
                Helper::activate_ep();
                update_option('uicore_connect',$data);
                break;

			case 'remove':
                delete_option('uicore_connect');
                break;

            case 'staging_check':
                $url = $local_data['url'];
                if($url && get_site_url() != $url){
                    return true;
                } else {
                    return false;
                }

                break;

            case 'get_staging':
                $url = $local_data['url'];
                if($url){
                    return $url;
                } else {
                    return false;
                }
                break;

            default:
                return ["status"=>"error"];
                break;
        }
    }

    static function get_current_meta_id()
    {
        if(\class_exists('\UiCore\Blog\Frontend') && \UiCore\Blog\Frontend::is_blog() && !is_singular('post')){
            $post_id = get_option('page_for_posts', true);
        }elseif(\class_exists('\UiCore\Portfolio\Frontend') && \UiCore\Portfolio\Frontend::is_portfolio() && !is_singular('portfolio')){
            $post_id = \UiCore\Portfolio\Frontend::get_portfolio_page_id();
        }else{
            $post_id = get_queried_object_id();
        }

        return $post_id;
    }


    static function get_buttons_class($state='default',$style_type='full', $no_elementor=false, $wrapper=''){
        $not = array('.bdt-offcanvas-button');
        $all_style_selectors = array(
			'{{WRAPPER}} input[type="button"]',
			'{{WRAPPER}} input[type="submit"]',
            '{{WRAPPER}} [type="submit"]',
            '{{WRAPPER}} .wp-block-button__link',
            '.uicore-mobile-menu-wrapper .uicore-cta-wrapper a',
            '.uicore-left-menu .uicore-cta-wrapper a',
            '.wc-block-components-button:not(.is-link)'
		);
        $no_padding_selectors = array(
			'.uicore-navbar a.uicore-btn',
		);
        if(!$no_elementor){
            $all_style_selectors = array_merge($all_style_selectors,[
            '{{WRAPPER}} .elementor-button.elementor-button',
            '{{WRAPPER}} .elementor-button:not('.implode('):not(',$not).')',  //maybe not
            '{{WRAPPER}} .bdt-button-primary',
            '{{WRAPPER}} .bdt-ep-button',
            'button.metform-btn',
            'button.metform-btn:not(.toggle)',
            '{{WRAPPER}} .bdt-callout a.bdt-callout-button',

            '{{WRAPPER}} .tutor-button',
            '{{WRAPPER}} .tutor-login-form-wrap input[type="submit"]',
            ]);
            $no_padding_selectors[] = '{{WRAPPER}} .bdt-contact-form .elementor-button';
        }

		if(apply_filters( "uicore_woo_buttons_global", \function_exists('is_shop') )){
			 $all_style_selectors = array_merge( $all_style_selectors, [
				 '{{WRAPPER}}.woocommerce #respond input#submit',
				 '{{WRAPPER}}.uicore-woo-page a.button:not(.add_to_cart_button):not(.product_type_grouped):not(.product_type_external):not(.product_type_simple):not(.wc-forward)',
				 '{{WRAPPER}}.uicore-woo-page a.checkout-button.button.alt',
			 ]);
			$no_padding_selectors = array_merge($no_padding_selectors,[
                '{{WRAPPER}}.uicore-woo-page a.button.wc-forward',
				'{{WRAPPER}} .widget.woocommerce a.button',
				'{{WRAPPER}} .woocommerce button.button',
				'{{WRAPPER}} .woocommerce div.product form.cart .button',
				'{{WRAPPER}} .woocommerce-cart-form .button',
				'{{WRAPPER}} .woocommerce #respond input#submit.alt',
				'{{WRAPPER}}.woocommerce a.button.alt',
				'.woocommerce button.button.alt',
				'{{WRAPPER}}.woocommerce button.button.alt.disabled',
				'{{WRAPPER}}.woocommerce input.button.alt'
			]);
		}
        $only_hover = array(
            '.uicore-navbar a.uicore-btn',
            '.uicore-transparent:not(.uicore-scrolled) .uicore-btn.uicore-inverted',
            '{{WRAPPER}} .metform-btn'
        );
        if($style_type === 'full'){
            $selectors = \array_merge($all_style_selectors,$no_padding_selectors);
        }else{
            $selectors = $all_style_selectors;
        }

        if($state != 'default'){
            $selectors = \array_merge($selectors,$only_hover);
            foreach ($selectors as $selector){
                $new_selector[] = $selector.':hover';
                $new_selector[] = $selector.':focus';
            }
            $selectors = $new_selector;
        }

        $selector_string = implode( ',', $selectors );
        if($wrapper){
            $selector_string = str_replace('{{WRAPPER}}',$wrapper,$selector_string);
        }
        return $selector_string;

    }

    static function get_image_size_by_url($image_url) {
        // Try to get the attachment ID first
        $attachment_id = attachment_url_to_postid($image_url);

        if ($attachment_id) {
            // Get metadata for the attachment
            $metadata = wp_get_attachment_metadata($attachment_id);

            if ($metadata) {
                return [
                    'width' => $metadata['width'],
                    'height' => $metadata['height']
                ];
            }
        }

        return false;
    }

    /**
     * Check if we're on Elementor Edit or Preview mode.
     * Although this function is partialy present in Widget Base Class, due to the relation with uicore animate,
     * wich has animations and features we cant' run on editor, having this verification as Helper function is usefull.
     *
     * @param bool $server_method - If true, checks for elementor URI request parameters instead of using elementor API.
     *
     * @return bool
     */
    static function is_edit_mode( $server_method = false ) {

        // Cases where Elementor instance is not available
        if($server_method){
            if (
                strpos($_SERVER['REQUEST_URI'], 'elementor') !== false ||
                (
                    strpos($_SERVER['REQUEST_URI'], 'preview') !== false &&
                    strpos($_SERVER['REQUEST_URI'], 'preview_id') !== false &&
                    strpos($_SERVER['REQUEST_URI'], 'preview_nonce') !== false
                )
            ) {
                return true;
            }

            return false;
        }

        // Default elementor method
        $elementor_instance = \Elementor\Plugin::instance();
        if ( $elementor_instance->preview->is_preview_mode() || $elementor_instance->editor->is_edit_mode() ) {
            return true;
        }

        return false;
    }

    static function ensure_assets_manager_exists(){
        $path = UICORE_INCLUDES . '/extra/assets-manager';
        //Required for CSS Class and Js Class
        if (!\class_exists('MatthiasMullie\Minify\Minify')) {
            require_once $path . '/Minify.php';
            require_once $path . '/CSS.php';
            require_once $path . '/JS.php';
            require_once $path . '/Exception.php';
            require_once $path . '/Exceptions/BasicException.php';
            require_once $path . '/Exceptions/FileImportException.php';
            require_once $path . '/Exceptions/IOException.php';
            require_once $path . '/ConverterInterface.php';
            require_once $path . '/Converter.php';
        }
        require_once $path . '/customCSS.php';

        if (!class_exists('MatthiasMullie\PathConverter\NoConverter')) {
            require_once $path . '/NoConverter.php';
        }
    }
}

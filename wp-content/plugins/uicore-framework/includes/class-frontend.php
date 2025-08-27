<?php
namespace UiCore;


defined('ABSPATH') || exit();


/**
 * Frontend ui and functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 1.0.0
 */
class Frontend
{

    private $assets_version;

    /**
     * Construct Frontend
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function __construct()
    {
        // Helper::activate_ep();
        $this->assets_version = Helper::get_option('settings_version', false);
        $this->css_check();

        //404 Redirect
        add_action('template_redirect', [$this, 'custom_404_redirect'],1);

        //maintenance Redirect
        if (Helper::get_option('gen_maintenance') === 'true') {
            add_action('pre_get_posts', [$this, 'maintenance_redirect']);
            add_filter( 'the_posts', [$this, 'maintenance_overwrite'], 10, 2 );

        }

        $this->disable_cache_if_multilingual();

        //Include frontend classes file
        $this->frontend_includes();

        //Initiate all the frontend Classes
        $this->frontend_render();

        //Enque general scripts and style
        add_action('wp_enqueue_scripts', [$this, 'frontend_css'], 50);

        //Add Theme Color
        if (Helper::get_option('gen_themecolor') == 'true') {
            add_action('wp_head', [$this, 'add_theme_color']);
        }

        //Add Favicon
        add_action('wp_head', [$this, 'add_favicon']);

        //Add Custopm content in Head
        add_action('wp_head', [$this, 'add_head_content'], 2);

        //Add Custopm content in Head
        add_action('wp_footer', [$this, 'add_footer_content'], 99);

        //If Google font url is setted add it to registred style
        // add_action('wp_head', [$this, 'add_preconnect'], 1);

        //Enque scripts in footer
        add_action('wp_footer', [$this, 'add_script_in_footer'], 4);

        //Add custom classes to body
        add_filter('body_class', [$this, 'add_body_class']);

        //add uicore-simple-megamenu class
        add_filter('nav_menu_css_class' , [$this, 'menu_extra_nav_class'] , 10 , 2);

        //Menu Extra Meta
        add_filter( 'walker_nav_menu_start_el', [$this, 'menu_extra'], 10, 4 );

         //maintenance Redirect
         if (Helper::get_option('gen_cursor') === 'true') {
            add_action('wp_footer', [$this, 'custom_cursor']);
        }

        if(Helper::handle_connect('staging_check')){
            add_action('wp_footer', [$this, 'display_staging'], 0);
        }
        //uicore_before_body_content
        add_action('uicore_before_body_content', [$this, 'add_page_options_custom_html'], 0);


        $this->show_hooks();

    }

    /**
     * Run frontend components
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function frontend_render()
    {
        //Enque Specific Inline Style
        new InlineStyle();

        new Header();
        new PageTitle();
        new Sidebar();
        new Footer();

        new Search();
        new Extras();

        new Animations();
        new Performance();

        if (Helper::get_option('disable_blog') === 'false' ){
            new Blog\Frontend();
        }
        if (Helper::get_option('disable_portfolio') === 'false' ){
            new Portfolio\Frontend();
        }
    }

    /**
     * Enqueue frontend css and js
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function frontend_css()
    {
        // new \Elementor\Frontend->enqueue_styles();
        // delete_option('elementor_active_kit');
        wp_enqueue_style( 'elementor-frontend' );
        wp_enqueue_style('uicore_global');
        wp_enqueue_script('uicore_global');

        $critical_inline_css = \get_option('uicore_global_critical_css', false);
        if ($critical_inline_css) {
                $inline_style_handle = 'uicore-critical-styles';
                wp_register_style($inline_style_handle, false); // Register a dummy style handle
                wp_enqueue_style($inline_style_handle); // Enqueue the style
                wp_add_inline_style($inline_style_handle, $critical_inline_css); // Add inline styles
        }

        if (class_exists('\Elementor\Plugin')) {
            if('internal' === get_option( 'elementor_css_print_method' )){
                $kit_id = get_option('elementor_active_kit');
                //Add kit just to be sure it loads on all pages if is inline
                $post_css_file = new \Elementor\Core\Files\CSS\Post($kit_id);
                $fonts = $post_css_file->enqueue();
            }
        }

        if ( is_rtl() ) {
            wp_enqueue_style('uicore_rtl');
        }
    }

    /**
     * Include Frontend Resources
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function frontend_includes()
    {
        $includes = [
            '/templates/header.php', // Header Template
            '/templates/page-title.php', // Page Title Template
            '/templates/sidebar.php', // Sidebar Template
            '/templates/footer.php', // Footer Template
            '/templates/search.php', // Search Comp Template
            '/templates/extras.php', // Frontend Extras
            '/templates/posts.php', // Custom post and Blog post Template
            '/templates/pages.php', // Custom post and Blog post Template
            '/extra/class-inline-style.php', // Inline Style
            '/extra/class-animations.php', // UiCore Animations
            '/extra/class-performance.php', // Performance Manager
        ];

        //loop trough all required files
        foreach ($includes as $file) {
            $filepath = UICORE_INCLUDES . $file;
            if (!$filepath) {
                trigger_error(sprintf('Error locating /inc%s for inclusion', $file), E_USER_ERROR);
            } else {
                require $filepath;
            }
        }
    }

    /**
     * Add Theme Color Meta markup to head
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function add_theme_color()
    {
        $color = Helper::get_option('gen_themecolorcode');

        echo '<meta name="theme-color" content="' . Helper::get_css_color($color) . '" />';
    }

    /**
     * Add preconnect for google fonts
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function add_preconnect()
    {
        echo '<link rel="preconnect" href="//fonts.googleapis.com" crossorigin>'; //CSS
        echo '<link rel="preconnect" href="//fonts.gstatic.com" crossorigin>'; //Font
    }

    /**
     * Add Favicon Meta
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function add_favicon()
    {
        $favicon = Helper::get_option('fav');
        if (!$favicon) {
            $favicon = UICORE_ASSETS . '/img/favicon.png';
        }
        echo '
        <link rel="shortcut icon" href="' .
            $favicon .
            '" >
		<link rel="icon" href="' .
            $favicon .
            '" >
		<link rel="apple-touch-icon" sizes="152x152" href="' .
            $favicon .
            '">
		<link rel="apple-touch-icon" sizes="120x120" href="' .
            $favicon .
            '">
		<link rel="apple-touch-icon" sizes="76x76" href="' .
            $favicon .
            '">
        <link rel="apple-touch-icon" href="' .
            $favicon .
            '">
        ';
    }

    /**
     * Add Custom Js in Footer
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function add_script_in_footer()
    {
        $script = '';

        $settings = Settings::po_get_page_settings(Helper::get_current_meta_id());
        if(isset($settings['customjs'])){
            $script = $settings['customjs'];
        }


        if (Helper::get_option("header_top") === 'true' && Helper::get_option('header_top_dismissable') === 'true'){
            $script .="
            if(document.querySelector('#ui-banner-dismiss')){
                if(!localStorage.getItem('uicore_tb') || localStorage.getItem('uicore_tb') != '".Helper::get_option('header_top_token')."'){
                    document.querySelector('#ui-banner-dismiss').addEventListener('click', function(event){
                        var topBar = document.querySelector('.uicore-top-bar');
                        if (topBar) {
                            if (topBar.style.display === 'none' || getComputedStyle(topBar).display === 'none') {
                                topBar.style.display = '';
                            } else {
                                topBar.style.display = 'none';
                            }
                        }
                        var navbar = document.querySelector('.uicore-navbar.uicore-sticky');
                        if (navbar) {
                            navbar.style.transition = 'top 0.3s';
                            navbar.style.top = '0';
                        }
                        localStorage.setItem('uicore_tb', '".Helper::get_option('header_top_token') ."');
                    });
                }
            }
            ";
        }

        echo "<script> \n";
        echo $script;
        echo "var uicore_frontend = {'back':'". esc_attr__(Helper::get_option('mobile_back'), 'Frontend - Mobile submenu', 'uicore-framework') ."', 'rtl' : '".is_rtl()."','mobile_br' : '".Helper::get_option('mobile_breakpoint')."'};";
        if(apply_filters('uicore_versions_output', true)){
            echo "\n console.log( 'Using " . str_replace("'", "\\'", UICORE_THEME_NAME) . " v." . UICORE_THEME_VERSION . "');";
            echo "\n console.log( 'Powered By UiCore Framework v.". UICORE_VERSION . "');";
        }
        echo "\n </script> ";
    }

    /**
     * 404 Page Redirect
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function custom_404_redirect()
    {
        global $wp_query, $post;

        // check if is a 404 error, and it's on your jobs custom post type
        if (is_404()) {
            $wp_query->is_singular = true;
            $wp_query->is_single = false;
            $wp_query->is_category = false;

            $page = Helper::get_option('gen_404');
            if (isset($page['id']) && $page['id'] == '0') {
                $wp_query->is_404 = true;
                $wp_query->is_singular = false;
            } else {
                $post = get_post(Helper::get_option('gen_404')['id']);
                $wp_query->is_404 = false;
                $wp_query->queried_object = $post;
                $wp_query->queried_object_id = $post->ID;
                $wp_query->query_vars['page_id'] = $post->ID;
                $wp_query->is_page = true;
                $wp_query->set('page_id', $page['id']);
                status_header(404);
            }

            $wp_query->post_count = 1;
            $wp_query->current_post = -1;
            $wp_query->posts = [$post];
        }
    }

    /**
     * Maintenance Page Redirect
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function maintenance_redirect($wp_query)
    {
        // check if is a 404 error, and it's on your jobs custom post type
        if (!is_user_logged_in() && $wp_query->is_main_query()) {
            // TODO: Add a frontend notice for that

            $page = Helper::get_option('gen_maintenance_page');
            if (isset($page['id'])) {

                $wp_query->is_page = true;
                $wp_query->is_single = true;
                $wp_query->is_home = false;
                $wp_query->is_singular = false;
                $wp_query->is_category = false;
                $wp_query->is_404 = false;
                $wp_query->post_count = 0;
                $wp_query->current_post = -1;
                  // Set the query to display the specific post
                $wp_query->set( 'p',$page['id']);

                // Remove pagination
                $wp_query->set( 'posts_per_page', 1 );

                if ($page['id'] != '0') {
                    $wp_query->set('page_id', $page['id']);
                } else {
                    $wp_query->posts = [];
                    include get_template_directory() . '/maintenance.php';
                    exit();
                }
            }
        }

        return null;
    }
    function maintenance_overwrite($posts, $wp_query)
    {
        if (!is_user_logged_in() && $wp_query->is_main_query()) {
            $page = Helper::get_option('gen_maintenance_page');
            if (isset($page['id']) && $page['id'] == '0') {
                return [];
            }
            if($wp_query->is_page && $wp_query->get('p') == $page['id']){
                $posts = [get_post($page['id'])];
            }
        }
        return $posts;
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
        global $post;

        if (isset($post->ID)) {
            $site_layout = Helper::po('layout', 'gen_layout', 'full width', $post->ID);
        } else {
            $site_layout = Helper::get_option('gen_layout');
        }

        $sticky_top_bar = Helper::get_option('header_top_sticky') === 'true' ? 'uicore-sticky-tb' : null ;
        $hamburger_menu = strpos(Helper::get_option('header_layout'), 'ham') !== false ? 'uicore-is-ham' : null ;
        $menu_focus = Helper::get_option('menu_focus') === 'true' ? 'uicore-menu-focus' : null ;

        $newclasses = [
            $site_layout == 'boxed' ? 'uicore-boxed' : null,
            $sticky_top_bar,
            $hamburger_menu,
            $menu_focus
        ];

        return array_merge($classes, $newclasses);
    }
    function menu_extra_nav_class($classes, $item){

        if($item->mega == '1'){
            $mega_type = get_post_meta( $item->ID, '_menu_item_mega-type', true );
            if($mega_type){
                $classes[] = 'uicore-'.$mega_type;
            }
            $classes[] = 'uicore-simple-megamenu';
        }
		if(get_post_meta($item->ID, '_menu_item_icon-placement', true ) === 'right') {
			$classes[] = 'ui-icon-right';
		}
        if(!empty( $item->description )){
            $classes[] = 'ui-has-description';
        }
        if(Helper::get_option('menu_active') === 'false'){
            $classes = array_diff($classes, ['current-menu-item'] );
        }

        return $classes;
    }

    function disable_cache_if_multilingual()
    {
        if(function_exists('icl_object_id')  || function_exists('pll_the_languages') ){
            add_filter('uicore-menu-cache', '__return_false');
            add_filter('uicore-footer-cache', '__return_false');
        }
    }
	function css_check()
	{
		if($this->assets_version === false || $this->assets_version == '0' || defined('UICORE_LOCAL_CSS')){
			Settings::clear_cache();
		}
	}

    function menu_extra( $item_output, $item, $depth, $args ) {
        if ( !empty( $item->description ) ) {
            $item->description = htmlspecialchars($item->description);
            if($depth === 0){
                $item_output = str_replace( $args->link_after , '<div class="custom-menu-desc">' . $item->description . '</div>' . $args->link_after, $item_output );
            }else{
                $item_output = str_replace( $args->link_after , $args->link_after . '<span class="custom-menu-desc">' . $item->description . '</span>', $item_output );
            }
        }
        $img = get_post_meta($item->ID, '_menu_item_img', true);
        if($img){
            $item_output = str_replace('<span class="ui-menu-item-wrapper">', wp_get_attachment_image($img, 'thumbnail', '', ["class" => "ui-menu-img" ]) .'<span class="ui-menu-item-wrapper">', $item_output);
        }
        $icon = get_post_meta($item->ID, '_menu_item_icon', true );
        if($icon){
            $icon_placement = get_post_meta($item->ID, '_menu_item_icon-placement', true );
            $icon_color = get_post_meta($item->ID, '_menu_item_icon-color', true );
            if($icon_placement === 'right'){
                $item_output = str_replace( $args->link_after , $args->link_after . Data::get_menu_icons($icon,$icon_color) , $item_output );
            }else{
                $item_output = str_replace('<span class="ui-menu-item-wrapper">', Data::get_menu_icons($icon,$icon_color) .'<span class="ui-menu-item-wrapper">', $item_output);
            }
        }
        $badge = get_post_meta($item->ID, '_menu_item_badge', true );
        if ($badge) {
            $badge_color = get_post_meta($item->ID, '_menu_item_badge-color', true);
            $search_pattern = '<span class="ui-menu-item-wrapper">';
            $end_pattern = '</span>';
            $start_pos = strpos($item_output, $search_pattern);
            $end_pos = strpos($item_output, $end_pattern, $start_pos + strlen($search_pattern));
            if ($start_pos !== false && $end_pos !== false) {
                $content_between_spans = substr($item_output, $start_pos + strlen($search_pattern), $end_pos - $start_pos - strlen($search_pattern));
                $repalce = "<span class=\"ui-menu-item-wrapper\">$content_between_spans<span class=\"ui-badge\" style=\"--ui-badge-color:" . Helper::get_css_color($badge_color, 'Primary') . "\">$badge</span></span>";
                $item_output = substr_replace($item_output, $repalce, $start_pos, $end_pos - $start_pos + strlen($end_pattern));
            }
        }
        return $item_output;
    }

    /**
     * Display the custom content in header
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 4.0.0
     */
    function add_head_content()
    {
        if (!defined('WPSEO_VERSION')) { // Check if Yoast SEO is active
            $post_id = Helper::get_current_meta_id();
            $meta = get_post_meta($post_id, 'page_description', true);
            if (!empty($meta)) {
            echo '<meta name="description" content="' . esc_attr($meta) . '">';
            }
        }
        echo Helper::get_option('header_content');
    }
    /**
     * Display the custom content in footer
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 4.0.0
     */
    function add_footer_content()
    {
        echo Helper::get_option('footer_content');
    }
    /**
     * Display the cursor wrapper
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 4.0.3
     */
    function custom_cursor()
    {
        echo '<div class="ui-cursor ui-cursor-main"></div>';
    }

    /**
     * Display the staging tag on the frontend
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 4.0.3
     */
    function display_staging()
    {
        $extra= '';
        //add url if user is logged in
        if(is_user_logged_in()){
            $extra = ' for ' . Helper::handle_connect('get_staging') . ' - <a href="'.admin_url('admin.php?page=uicore#/updates?action=connect').'">Click here if this is wrong</a>';
        }
        echo '<div class="ui-staging">This is a staging environment'.$extra.'</div>';
        echo '<style>.ui-staging{text-align: center;background: #fff2c8; color: black; padding: 13px 18px; font-size:15px;}</style>';
    }


    function add_page_options_custom_html($c)
    {

        $settings = Settings::po_get_page_settings(Helper::get_current_meta_id());

        if(isset($settings['customhtml'])){
            echo '<!-- Custom HTML - Page Options -->';
            echo $settings['customhtml'];
        }

    }


    function show_hooks()
    {
        //move this to a different palce wher it can be consumed in both admin and frontend
            $hooksList = [
                [
                    'name' => 'Before Header',
                    'value' => 'uicore__before_header',
                ],
                [
                    'name' => 'Header Extras',
                    'value' => 'uicore__header_extras',
                ],
                [
                    'name' => 'Mobile Header',
                    'value' => 'uicore__mobile_header',
                ],
                [
                    'name' => 'Mobile Menu',
                    'value' => 'uicore__mobile_menu',
                ],
                [
                    'name' => 'After Header',
                    'value' => 'uicore__after_header',
                ],
                [
                    'name' => 'Before Content',
                    'value' => 'uicore__before_content',
                ],
                [
                    'name' => 'After Content',
                    'value' => 'uicore__after_content',
                ],
                [
                    'name' => 'Before Footer',
                    'value' => 'uicore__before_footer',
                ],
                [
                    'name' => 'After Footer',
                    'value' => 'uicore__after_footer',
                ],
                [
                    'name' => 'Before Portfolio Archive',
                    'value' => 'uicore__before_portfolio_archive',
                ],
                [
                    'name' => 'After Portfolio Archive',
                    'value' => 'uicore__after_portfolio_archive',
                ],
                [
                    'name' => 'Before Portfolio Single',
                    'value' => 'uicore__before_portfolio_single',
                ],
                [
                    'name' => 'After Portfolio Single',
                    'value' => 'uicore__after_portfolio_single',
                ],
                [
                    'name' => 'Before Blog Archive',
                    'value' => 'uicore__before_blog_archive',
                ],
                [
                    'name' => 'After Blog Archive',
                    'value' => 'uicore__after_blog_archive',
                ],
                [
                    'name' => 'Before Blog Single',
                    'value' => 'uicore__before_blog_single',
                ],
                [
                    'name' => 'After Blog Single Content',
                    'value' => 'uicore__after_blog_single_content',
                ],
                [
                    'name' => 'Before Blog Comments',
                    'value' => 'uicore__before_blog_comments',
                ],
                [
                    'name' => 'After Blog Single',
                    'value' => 'uicore__after_blog_single',
                ],
            ];

        //add hooks for woocomerce if exists
        if(class_exists('WooCommerce')){
            $hooksList[] = [
                'name' => 'Before Product Summary',
                'value' => 'uicore__before_product_summary',
            ];
            $hooksList[] = [
                'name' => 'After Product Summary',
                'value' => 'uicore__after_product_summary',
            ];
            $hooksList[] = [
                'name' => 'Before Product Short Description',
                'value' => 'uicore__before_product_short_description',
            ];
            $hooksList[] = [
                'name' => 'After Product Short Description',
                'value' => 'uicore__after_product_short_description',
            ];
            $hooksList[] = [
                'name' => 'After Product Add to Cart Form',
                'value' => 'uicore__after_product_add_to_cart_form',
            ];
            $hooksList[] = [
                'name' => 'After Product Meta',
                'value' => 'uicore__after_product_meta',
            ];
            $hooksList[] = [
                'name' => 'After Product Share',
                'value' => 'uicore__after_product_share',
            ];
            $hooksList[] = [
                'name' => 'Before Product Related',
                'value' => 'uicore__before_product_related',
            ];
        }

        if(isset($_GET['uicore_hooks'])){
            foreach ($hooksList as $hook) {
                \add_action($hook['value'], function() use ($hook){
                    echo '<div data-elementor-type="uicore-tb"  style="background: #fce8ba; padding: 15px; border-radius: 4px; margin: 4px; align-self: center; color: black; font-size: 14px; font-weight: 600; line-height: 20px; text-align: center; border: 2px dashed #feba09;">'.$hook['name'].' <i style="font-size:12px;font-weight:normal;line-height:20px;" title="Hook name to use in PHP"> ('.$hook['value'].')</i></div>';
                });
            }
        }
    }
}

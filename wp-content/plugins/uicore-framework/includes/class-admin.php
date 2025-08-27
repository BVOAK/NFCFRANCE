<?php
namespace UiCore;

defined('ABSPATH') || exit();

/**
 * Admin Functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 1.0.0
 */
class Admin
{
    /**
     * Construct Admin
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function __construct()
    {
        add_action('admin_menu', [$this, 'admin_menu']);
        add_action('admin_head', [$this, 'add_editor_styles']);
        add_action('admin_head', [$this, 'add_menu_icon_style']);
        add_action('admin_head', [$this, 'add_support_script']);

        add_action('admin_enqueue_scripts', [$this, 'add_page_options'], 10, 1);
        add_filter('display_post_states', [$this, 'portfolio_page']);

        $this->elementor_style();

        $this->transients_cleaning_hooks();

        //Flush rewrite rules on portfolio page edit
        add_action( 'save_post', [$this,'flush_rules_on_portfolio_edit'], 20, 2);

        add_filter( 'upload_mimes', array( $this, 'add_fonts_to_allowed_mimes' ) );
        add_filter( 'wp_check_filetype_and_ext', array( $this, 'update_mime_types' ), 10, 3 );

        //Simple Megamenu
        add_action( 'wp_nav_menu_item_custom_fields', [$this, 'menu_meta'], 10, 2 );
        add_action( 'wp_update_nav_menu_item', [$this, 'save_menu_meta'],10, 3);

        //Refresh sethings after activate woocomercee or tutorlms
        add_action( 'activated_plugin', [ $this, 'plugin_3rd_party_refresh_style' ] );

        if (Helper::get_option('disable_blog') === 'true' ){
            add_action('admin_menu', [$this, 'remove_blog_menu']);
        }
        add_action('deleted_post', [$this, 'handle_portfolio_page_remove']);
        \add_action('enqueue_block_editor_assets', [$this, 'block_editor_assets']);
        add_action('enqueue_block_assets', [$this, 'block_editor_custom_css']);
    }

    /**
     * Register Admin Menu
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function admin_menu()
    {
        global $submenu;

        $capability = 'manage_options';
        $slug = 'uicore';
        $name = apply_filters('uicore_theme_name', UICORE_NAME);
        $icon_url = get_template_directory_uri()."/assets/img/dashboard-icon.svg";
        $icon_url = apply_filters('uicore_theme_icon_url', $icon_url);
        $icon = file_exists(get_template_directory()."/assets/img/dashboard-icon.svg") ? $icon_url : 'dashicons-warning';

        $hook = add_menu_page(
            $name,
            $name,
            $capability,
            $slug,
            [$this, 'plugin_page'],
            $icon,
            2
        );
        // prettier-ignore
        if (current_user_can($capability) && file_exists(get_template_directory()."/assets/img/dashboard-icon.svg")) {
            $submenu[$slug][] = [__('Get Started', 'uicore-framework'), $capability, 'admin.php?page=' . $slug . '#/'];
            $submenu[$slug][] = [__('Theme Options', 'uicore-framework'),$capability,'admin.php?page=' . $slug . '#/theme-options'];
            $submenu[$slug][] = [__('System', 'uicore-framework'), $capability, 'admin.php?page=' . $slug . '#/system'];
        }

        add_action('load-' . $hook, [$this, 'init_hooks']);

        //Connect handle
        add_submenu_page('', 'UiCore Connect', 'UiCore Connect', 'manage_options', 'uicore_connect', [$this, 'connect_page_callback']);
    }

    /**
     * Initialize our hooks for the admin page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function init_hooks()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts']);
        add_action('admin_enqueue_scripts', 'wp_enqueue_media');
    }

    /**
     * Enqueue Scripts and style
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function enqueue_scripts()
    {
        wp_enqueue_editor();
        wp_enqueue_style('uicore-admin');
        wp_enqueue_script('uicore-admin');
        wp_enqueue_style('uicore-admin-icons');
        wp_enqueue_style('uicore-admin-font');
        wp_add_inline_script('uicore-vendor', 'var uicore_data = ' . Data::get_admin_data('json'), 'before');
    }

    /**
     * Add Editor Styles
     *
     * @return void
     */
    public function block_editor_assets()
    {
        // class-common.php:123
        wp_enqueue_style('uicore-ai');
        wp_enqueue_script('uicore-ai');

    }

    public function connect_page_callback()
    {
        if(isset($_GET['connect'])){
            $connect_data = [
                'url'   => sanitize_url( isset($_GET['staging']) ? $_GET['staging'] : \get_site_url()),
                'token' => sanitize_text_field($_GET['connect']),
            ];
            Helper::handle_connect( 'update', $connect_data);

            //Redirect to the dashboard via js
            ?>
            <style>
                .loader {
                    position:fixed;
                    top:0;
                    left:0;
                    right:0;
                    bottom:0;
                    z-index: 999999;
                    background: #fff;
                }
            </style>
            <div class="loader">
                <div style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);text-align: center;">
                    <h2>Connecting...</h2>
                </div>
            </div>

            <script>
                window.location.href = "https://my.uicore.co/connection-success/";
            </script>
            <?php
        }

        if(isset($_GET['init'])){
            ?>
            <style>
                .loader {
                    position:fixed;
                    top:0;
                    left:0;
                    right:0;
                    bottom:0;
                    z-index: 999999;
                    background: #fff;
                }
            </style>
            <div class="loader">
                <div style="position: absolute;top: 50%;left: 50%;transform: translate(-50%, -50%);text-align: center;">
                    <h2>Initiating the connection to My UiCore Account...</h2>
                </div>
            </div>

            <script>
                window.location.href = "https://my.uicore.co/disconnection-success/";
            </script>
            <?php
        }

    }

    /**
     * Render Admin Page
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function plugin_page()
    {
        //prettier-ignore
        echo '
        <style>
        .uicore_dark_scheme {
            --uicore-color-9: #242837;
            --uicore-color-8: #fff;
            --uicore-color-7: #e0e3eb;
            --uicore-color-6: #6e778a;
            --uicore-color-5: #5a6172;
            --uicore-color-4: #3f4657;
            --uicore-color-3: #262b3b;
            --uicore-color-2: #171c29;
            --uicore-color-1: #121623;
        }
        .uicore_light_scheme {
            --uicore-color-9: #e7eaef;
            --uicore-color-8: #172b4d;
            --uicore-color-7: #1c2c4e;
            --uicore-color-6: #1c2c4e;
            --uicore-color-5: #5f6875;
            --uicore-color-4: #b0b8ca;
            --uicore-color-3: #eef0f5;
            --uicore-color-2: #fff;
            --uicore-color-1: #f4f5f7;
        }

        #uicore-wrap > #uicore{
            max-width: 1200px;
            border-radius: 5px;
            box-shadow: 0 9px 65px 1px hsla(0,0%,55%,.15);
            position: relative;
            background: var(--uicore-color-2,#171c29);
            transition: all .7s ease;
            border: 1px solid #cfd4df;
            min-height:100vh;
            overflow:hidden;
        }
        #uicore-wrap > #uicore::before{
            content: "";
            position:absolute;
            top: 0;
            left:0;
            bottom:0;
            background-color:#121623;
            width: 260px;
            border-right: 1px solid var(--uicore-color-9,#e7eaef);
        }
        </style>';

        if (Settings::current_settings()['scheme'] === 'dark') {
            $class = 'uicore_dark_scheme';
        } else {
            $class = 'uicore_light_scheme';
        }
        echo '<div id="uicore-wrap" class="wrap ' .
            $class .
            '" >
                    <div id="uicore">
                    </div>
                </div>';
    }

    /**
     * Elementor Editor Style, Fonts and Scripts
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function elementor_style()
    {
        add_action('elementor/editor/before_enqueue_scripts', function () {

            $settings = Settings::current_settings();
            $styles = $this->get_general_styles();


            if( $settings['disable_library'] == 'false' ){
                $styles .= $this->get_library_styles();
            }

            echo '<style id="uicore-csss">' . $styles. '</style>';

            $default = [];
            $purchase = Helper::handle_connect('get');
            if($purchase['token'] === '' && !apply_filters('uicore_is_sandbox',false)){
                $default['purchase'] = 'License';
            }

            $default['blocks'] = 'Blocks';

            switch (\get_post_type()) {
                case 'uicore-tb':
                    $type = wp_get_post_terms(\get_the_ID(), 'tb_type', ['fields' => 'names']);
                    $current_type = $type[0];
                    $type_name = ThemeBuilder\Admin::get_tb_types()[$current_type];
                    break;

                case 'portfolio':
                    $current_type = 'portfolio';
                    $portfolio_config = Portfolio\Common::get_portfolio_display_name();
                    $type_name = $portfolio_config['name'];
                    break;

                default:
                    $current_type = 'pages';
                    $type_name = 'Pages';
                    break;
            }

            // Following scripts required only if Uicore Library is enabled
            if ( $settings['disable_library'] == 'true' ){
                return;
            }

            echo '
            <script>
            var ui_theme_name = "'.(defined('UICORE_THEME_NAME') ? UICORE_THEME_NAME : UICORE_NAME).'";
            var purchase_data = '.\json_encode($purchase).';
            var uicore_data = {
                "v": "' . UICORE_VERSION . '",
                "root": "' . get_site_url() . '",
                "wp_json":"' . get_rest_url(null, 'uicore/v1') . '",
                "nonce":"' . wp_create_nonce('wp_rest') . '",
                "api": "'.UICORE_API.'"
            }
            var uicore_blocks = ' .
                json_encode(Data::get_library('blocks')) .
                ';
                ';
            if($current_type != '_type_block'){
                $default[$current_type] = $type_name;
                echo ' var uicore_extra = ' .
                json_encode(Data::get_library($current_type)) .
                ';';
            }
            echo '
            var uicore_frontend_data = ' .
                json_encode($settings) .
                ';
            var uicore_default = '.json_encode($default).';
            var path = "' . UICORE_NAME . '";
            </script>
            ';

            $prefix = (( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) || defined('UICORE_LOCAL')) ? '' : '.min';

            // wp_enqueue_script(
            //     'uicore-library-manifest',
            //     UICORE_ASSETS . '/js/manifest' . $prefix . '.js',
            //     ['jquery'],
            //     filemtime(UICORE_PATH . '/assets/js/manifest' . $prefix . '.js'),
            //     true
            // );
            wp_enqueue_script(
                'uicore-library-vendor',
                UICORE_ASSETS . '/js/vendor' . $prefix . '.js',
                ['jquery'],
                filemtime(UICORE_PATH . '/assets/js/vendor' . $prefix . '.js'),
                true
            );
            wp_enqueue_script(
                'uicore-library',
                UICORE_ASSETS . '/js/library' . $prefix . '.js',
                ['jquery'],
                filemtime(UICORE_PATH . '/assets/js/library' . $prefix . '.js'),
                true
            );

            // wp_add_inline_script('uicore-vendor', 'var uicore_frontend_data = ' . json_encode(Data::get_frontend_data()), 'before');
        });
        add_action('elementor/frontend/after_enqueue_styles', function () {
            $google_fonts = get_option('uicore_fonts');
            //If Google font url is setted add it to registred style
            if ($google_fonts) {
                wp_enqueue_style('uicore_fonts', $google_fonts);
            }
        });
    }

    /**
     * Get the basic styles
     *
     * @return string
     * @author Lucas Marini <lucas@uicore.co>
     * @since [currentVersion]
     */
    function get_general_styles()
    {
        return '
            #elementor-panel-get-pro-elements-sticky{
                display:none;
            }
            #wrapper-navbar{
                pointer-events:none;
            }
            body #elementor .animated.zoomIn{
                animation-fill-mode:forwards!important;
            }

            .metform-template-item--pro {
                display: none;
            }
			.ui-e-badge {
				height: 16px;
	            width: 16px;
				background-size: 9px;
				display: inline-block;
				vertical-align: middle;
    			margin: -2px 5px 0 0;
			}
            .uicore-tag {
              position: absolute;
              top: 7px;
              right: 7px;
              background: #532df5;
              border-radius: 4.5px;
              color: #fff;
              font-size: 10px;
              line-height: 13px;
              font-weight: 600;
              padding: 4px 6px;
              letter-spacing: .4px;
              text-transform: uppercase;
              -webkit-font-smoothing: antialiased;
            }
            .uicore-green {
              background: #1eaa69;
            }
            .uicore-red {
              background: #dc4545;
            }
            .elementor-panel-menu-item-theme-style-typography,
            .elementor-panel-menu-item-theme-style-buttons,
            .uicore-hide {
                display: none!important;
            }
            .elementor-element .icon .ui-e-widget:after {
				content: "";
			    position: absolute;
			    right: 5px;
			    top: 5px;
			    margin-right: 0;
			    width: 16px;
			    height: 16px;
			    background-size: 9px;
			    background-color: #656c7196;
				transition: background-color .3s ease-in-out;
            }
			.elementor-element:hover .icon .ui-e-widget:after {
				background-color: #532df5;
				transition: background-color .3s ease-in-out;
			}
            #elementor-panel-category-uicore-woo{
                order: -1;
            }
        ';
    }

     /**
     * Get the Uicore Library styles
     *
     * @return string
     * @author Lucas Marini <lucas@uicore.co>
     * @since [currentVersion]
     */
    function get_library_styles()
    {
        return '
            .uicore-template-library-templates-container{
                margin-left: -15px;
                margin-right: -15px;
                box-shadow: none!important;
            }
            .uicore-lib-templates .elementor-template-library-template-page img{
                width:100%;
                height: 239px;
            }
            .uicore-lib-templates .elementor-template-library-template-page .elementor-template-library-template-body {
                height: 239px;
            }
            .uicore-lib-dialog{
                transform: translate3d(-50%, -50%, 0);
                left: 50%;
                top: 50%;
            }
            .uicore-library-logo,
            .ui-e-badge{
                height: 28px;
                width: 28px;
                margin-right: 10px;
                border-radius: 3px;
                background-color: #532df5;
                background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 16 16\' xml:space=\'preserve\'%3E%3Cpath d=\'M5.383 15.217c3.1 0 5.4-2.3 5.4-5.3v-7.9h-2.9v7.9c0 1.4-1.1 2.5-2.5 2.5s-2.5-1.1-2.5-2.5v-7.9h-2.9v7.9c0 3 2.3 5.3 5.4 5.3zM14.283 4.117c1 0 1.7-.7 1.7-1.7s-.7-1.7-1.7-1.7-1.7.7-1.7 1.7.7 1.7 1.7 1.7zM15.683 15.017v-9.6h-2.8v9.6z\' fill=\'%23fff\'/%3E%3C/svg%3E");
                background-size: 16px;
                background-position: center;
                background-repeat: no-repeat;
            }
            .elementor-element .icon .ui-e-widget:after,
            .uicore-library-logo,
            .ui-e-badge {
                border-radius: 3px;
                background-image: url("data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'16\' height=\'16\' viewBox=\'0 0 16 16\' xml:space=\'preserve\'%3E%3Cpath d=\'M5.383 15.217c3.1 0 5.4-2.3 5.4-5.3v-7.9h-2.9v7.9c0 1.4-1.1 2.5-2.5 2.5s-2.5-1.1-2.5-2.5v-7.9h-2.9v7.9c0 3 2.3 5.3 5.4 5.3zM14.283 4.117c1 0 1.7-.7 1.7-1.7s-.7-1.7-1.7-1.7-1.7.7-1.7 1.7.7 1.7 1.7 1.7zM15.683 15.017v-9.6h-2.8v9.6z\' fill=\'%23fff\'/%3E%3C/svg%3E");
                background-position: center;
                background-repeat: no-repeat;
            }

            .uicore-lib-templates .uicore-library-logo{
                width: 70px;
                height: 70px;
                display: inline-block;
                background-size: 40px;
                border-radius: 6px;
                margin: 0;
            }
            .elementor-template-library-template-remote{
                transition:opacity .3s ease-in;
            }
			.uicore-lib-templates .elementor-button-success {
				width: 335px;
			}
			.uicore-lib-templates .uico-error {
				flex-direction: column;
    			align-items: center;
			}
			.uicore-lib-templates .uico-error .register-url {
				height: 55px;
				display: block;
				line-height: 55px;
				font-size: 16px;
				margin-top: 20px;
			}
        ';
    }

    /**
     * Gutenberg Custom CSS style
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 6.1.0
     */
    function block_editor_custom_css(){
        $options = get_option(UICORE_SETTINGS.'_admin');
        if(!isset($options['customcss']) || empty($options['customcss'])) {
            return;
        }
        echo '<style id="uicore-framework-custom-css">'.$options['customcss'].'</style>';
    }

    /**
     * Gutenberg style
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function add_editor_styles()
    {
        //check if is editng a page
        //eg: doo not add blog styles to pages
        if (!get_current_screen() || get_current_screen()->base !== 'post') {
            return;
        }

        echo '
        <style id="uicore-editor">
        ' .get_option('uicore_blog_css') . '
        </style>
        ';
        echo get_option('uicore_blog_fonts')
            ? '<link rel="stylesheet" id="uicore-blog-fonts" href="' .
                get_option('uicore_blog_fonts') .
                '" type="text/css" media="all">'
            : null;
    }

    /**
     * Enqueue Scripts and Style for Page Options
     *
     * @param string $hook
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function add_page_options($hook)
    {
        if ($hook == 'post-new.php' || $hook == 'post.php') {
            // if ('page' === $post->post_type) {
            $this->enqueue_scripts();
            // }
        }

        //Menu Scripts and style
        global $pagenow;
        if ($pagenow === 'nav-menus.php') {
            wp_enqueue_media();
            wp_enqueue_script('uicore-admin-menu');
            wp_enqueue_style('uicore-admin-menu');
            wp_add_inline_script('uicore-vendor', 'var uicore_data = ' . Data::get_menu_data()  , 'before');
            wp_enqueue_style('uicore-admin');
            wp_enqueue_style('uicore-admin-icons');
			wp_enqueue_style('uicore-admin-font');
        }
    }

    /**
     * Portfolio Page Archieve
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function portfolio_page($states)
    {
        global $post;

        $page = Helper::get_option('portfolio_page');

        $portfolio_page_id = $page['id'] ?? 0;

        if (
            isset($post->ID) &&
            'page' == get_post_type($post->ID) &&
            $post->ID == $portfolio_page_id &&
            $portfolio_page_id != '0'
        ) {
            $states[] = __('Portfolio Page', 'uicore-framework');
        }else if(isset($post->ID) && apply_filters( 'wpml_object_id', $portfolio_page_id , 'post', true ) == $post->ID){
            $states[] = __('Portfolio Page', 'uicore-framework');
        }

        return $states;
    }

    /**
     * Add hooks and clear transients
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function transients_cleaning_hooks()
    {
        //Clear menu and footer on menu update
        foreach (
            [
                'rest_after_save_widget',
                'wp_ajax_save-widget',
                'wp_ajax_widgets-order',
                'wp_ajax_customize_save',
                'wp_update_nav_menu',
                'save_post',
                'delete_post'
            ]
            as $action
        ) {
            add_action(
                $action,
                function () {
                    Helper::delete_frontend_transients();
                },
                1
            ); //must use priority, example 1
        }
    }

    /**
     * Add inline style for Admin Menu Icon
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.0.1
     */
    function add_menu_icon_style()
    {
        echo'<style id="uicore-icon">
        .toplevel_page_uicore .wp-menu-image img,
        #toplevel_page_edit-post_type-uicore-tb .wp-menu-image img{
            padding:7px 0 0 0!important;
            opacity:1!important;
            max-height:20px;
        }
        .ep-megamenu-switcher,
        .notice-wpmet-jhanda-holidaydeal2021banner,
        .toplevel_page_metform-menu ul li:last-child,
        a[href$="element_pack_options#license"],
        #element-pack-notice-id-license-issue,
        #bdt-element_pack_license_settings,
        .wpmet-notice.notice-metform-_plugin_rating_msg_used_in_day{
            display:none!important
        }
        </style>';
    }


    /**
     * Check if portfolio page was saved and flush the rewrite rules
     *
     * @param [type] $id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.1.0
     */
    function flush_rules_on_portfolio_edit($id)
    {
       $portfolio_id = Helper::get_option('portfolio_page');
       $portfolio_id = isset($portfolio_id['id']) ? $portfolio_id['id'] : 0;

       if( (int) $id === (int) $portfolio_id){
            flush_rewrite_rules();
       }

    }

    /**
     * Allow fonts to be uploaded
     *
     * @param [type] $mimes
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.0
     */
    public function add_fonts_to_allowed_mimes( $mimes ) {
        $mimes['woff']  = 'application/x-font-woff';
        $mimes['woff2'] = 'application/x-font-woff2';
        $mimes['ttf']   = 'application/x-font-ttf';
        $mimes['svg']   = 'image/svg+xml';
        $mimes['eot']   = 'application/vnd.ms-fontobject';
        $mimes['otf']   = 'font/otf';

        //Temp to ensure support for using wp older than 5.8 ( TO BE REMOVED IN THE FUTURE )
        $mimes['webp'] = 'image/webp';

        return $mimes;
    }

    /**
     * add support for fonts
     *
     * @param $defaults
     * @param $file
     * @param $filename
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.0
     */
    public function update_mime_types( $defaults, $file, $filename ) {
        if ( 'ttf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'application/x-font-ttf';
            $defaults['ext']  = 'ttf';
        }

        if ( 'otf' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'application/x-font-otf';
            $defaults['ext']  = 'otf';
        }

        if ( 'woff' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'application/x-font-woff';
            $defaults['ext']  = 'woff';
        }

        if ( 'woff2' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'application/x-font-woff2';
            $defaults['ext']  = 'woff2';
        }

        if ( 'svg' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'image/svg+xml';
            $defaults['ext']  = 'svg';
        }

        if ( 'eot' === pathinfo( $filename, PATHINFO_EXTENSION ) ) {
            $defaults['type'] = 'application/vnd.ms-fontobject';
            $defaults['ext']  = 'eot';
        }


        return $defaults;
    }

    /**
     * Save Simple megamenu custom field
     *
     * @param [type] $menu_id
     * @param [type] $menu_item_db_id
     * @param [type] $args
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.6
     */
    function save_menu_meta($menu_id, $menu_item_db_id, $args )
    {
        // Verify this came from our screen and with proper authorization.
        if ( ! isset( $_POST['_menu_item_mega_nonce_name'] ) || ! wp_verify_nonce( $_POST['_menu_item_mega_nonce_name'], 'mega_menu_meta_nonce' ) ) {
            return $menu_id;
        }

        $fields = [
            'mega',
            'url',
            'mega-type',
            'img',
            'icon',
            'icon-placement',
            'icon-color',
            'badge',
            'badge-color',
        ];

        foreach ($fields as $type) {


            if ( isset( $_POST['menu-item-'.$type][$menu_item_db_id]  ) && $_POST['menu-item-'.$type][$menu_item_db_id] != 'Primary' && $_POST['menu-item-'.$type][$menu_item_db_id] != 'Inherit' ) {
                $sanitized_data = sanitize_text_field( $_POST['menu-item-'.$type][$menu_item_db_id] );
                update_post_meta( $menu_item_db_id, '_menu_item_'.$type, $sanitized_data );
            } else {
                delete_post_meta( $menu_item_db_id, '_menu_item_'.$type );
            }
            //Remove badge color if badge is not set
            if($type === 'badge-color' && isset( $_POST['menu-item-badge'][$menu_item_db_id] ) && !$_POST['menu-item-badge'][$menu_item_db_id]){
                delete_post_meta( $menu_item_db_id, '_menu_item_'.$type );
            }
            //Remove badge color if badge is not set
            if($type === 'icon-color' && isset( $_POST['menu-item-icon'][$menu_item_db_id] ) && !$_POST['menu-item-icon'][$menu_item_db_id]){
                delete_post_meta( $menu_item_db_id, '_menu_item_'.$type );
            }else if($type === 'icon-color' && isset($_POST['menu-item-'.$type][$menu_item_db_id]) && $_POST['menu-item-'.$type][$menu_item_db_id] != 'Inherit'){
                $sanitized_data = sanitize_text_field( $_POST['menu-item-'.$type][$menu_item_db_id] );
                update_post_meta( $menu_item_db_id, '_menu_item_'.$type, $sanitized_data );
            }
        }
    }

    /**
     * Add simple megamenu custom field to menu item
     *
     * @param [type] $item_id
     * @param [type] $item4
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 1.2.6
     */
    function menu_meta( $item_id, $item )
    {

        wp_nonce_field( 'mega_menu_meta_nonce', '_menu_item_mega_nonce_name' );
        $mega = get_post_meta( $item_id, '_menu_item_mega', true );
        $url = get_post_meta( $item_id, '_menu_item_url', true );
        $mega_type = get_post_meta( $item_id, '_menu_item_mega-type', true );
        $img_src = get_post_meta( $item_id, '_menu_item_img', true );
        $icon_src = get_post_meta( $item_id, '_menu_item_icon', true );
        $icon_placement = get_post_meta( $item_id, '_menu_item_icon-placement', true );
        $icon_color = get_post_meta( $item_id, '_menu_item_icon-color', true );
        $badge_text = get_post_meta( $item_id, '_menu_item_badge', true );
        $badge_color = get_post_meta( $item_id, '_menu_item_badge-color', true );
        $css_color = Helper::get_css_color($badge_color, 'Primary');
        $css_icon_color = Helper::get_css_color($icon_color, ThemeOptions::get_admin_options_all()['menu_typo']['c']);

        $css = null;

        if($item->object === 'uicore-tb'){
            $post_type_object = get_post_type_object( 'uicore-tb' );
            if ( ! $post_type_object ) {
                return;
            }

            if ( ! current_user_can( 'edit_post', $item->object_id ) ) {
                return;
            }

            if ( $post_type_object->_edit_link ) {
                $link = admin_url( sprintf( $post_type_object->_edit_link . '&action=elementor', $item->object_id ) );
            } else {
                $link = '';
            }

            $css = 'display:none;';
            echo '<p class="description description-wide">
					<label for="edit-menu-item-url-'.$item_id.'">
						'.__('Url','uicore-framework').'<br>
						<input type="text" id="edit-menu-item-url-'.$item_id.'" class="widefat" name="menu-item-url['.$item_id.']" value="'.$url.'">
					</label>
				</p>';

            echo '<a style="margin: 12px 0;float:left" href='.$link.'>Edit with Elementor</a>';
        }
        ?>
        <div class="field-custom_menu_meta description-wide" style="margin: 5px 0;<?php echo $css; ?>">
            <input type="hidden" class="nav-menu-id" value="<?php echo $item_id ;?>" />

            <div class="logged-input-holder">
                <input type="checkbox" name="menu-item-mega[<?php echo $item_id ;?>]" id="menu-item-mega-for-<?php echo $item_id ;?>" value="1"
                <?php checked( $mega, 1 ); ?> onCLick="uicore_mega_type(this)"  data-menuid="<?php echo $item_id ;?>"/>
                <label for="menu-item-mega-for-<?php echo $item_id ;?>">
                    <?php _e( 'Simple Megamenu', 'uicore-framework'); ?>
                </label>
            </div>
            <select class="ui-mega-type" name="menu-item-mega-type[<?php echo $item_id ;?>]" style="<?php echo $mega ? '' : 'display:none;' ?>" >
                <option value=""><?php _e( 'Auto Width', 'uicore-framework'); ?></option>
                <option value="full" <?php echo ($mega_type === 'full') ? 'selected' : '' ?>><?php _e( 'Full Width', 'uicore-framework'); ?></option>
                <option value="full_contained" <?php echo ($mega_type === 'full_contained') ? 'selected' : '' ?>><?php _e( 'Full Width Contained', 'uicore-framework'); ?></option>
            </select>
        </div>

        <div class="ui-menu-section<?php echo $img_src ? ' uicore-is-set' : ''; ?>">
            <b><?php _e( 'Image', 'uicore-framework'); ?></b>
            <div class='image-preview-wrapper'>
                <img class='image-preview-<?php echo $item_id ;?>' src='<?php echo $img_src ? wp_get_attachment_url($img_src) : ''; ?>' style='max-height: 30px; width: auto;'>
            </div>
            <input type="button" class="button upload_image_button" data-menuid="<?php echo $item_id ;?>" value="<?php _e( 'Select Image', 'uicore-framework'); ?>" onCLick="uicore_set_image(this)" />
            <input type="button" class="button remove_image_button" data-menuid="<?php echo $item_id ;?>" value="<?php _e( 'Remove Image', 'uicore-framework'); ?>" onCLick="uicore_remove_image(this)" />
            <input type='hidden' name='menu-item-img[<?php echo $item_id ;?>]' value='<?php echo $img_src ?>'>
        </div>

        <div class="ui-menu-section<?php echo $icon_src ? ' uicore-is-set' : ''; ?>">
            <b><?php _e( 'Icon', 'uicore-framework'); ?></b>
            <div class='image-preview-wrapper ui-icon' data-menuid="<?php echo $item_id ;?>">
                <?php echo $icon_src ? Data::get_menu_icons($icon_src) : ''; ?>
                <select class="ui-item-icon-placement" name="menu-item-icon-placement[<?php echo $item_id ;?>]" >
                    <option value=""><?php _e( 'Left Aligned', 'uicore-framework'); ?></option>
                    <option value="right" <?php echo ($icon_placement === 'right') ? 'selected' : '' ?>><?php _e( 'Right Aligned', 'uicore-framework'); ?></option>
                </select>
            </div>
            <input type="button" class="button select_icon_button" data-menuid="<?php echo $item_id ;?>" value="<?php _e( 'Select Icon', 'uicore-framework'); ?>" onCLick="uicore_set_icon(this)" />
            <input type="button" class="button remove_icon_button" data-menuid="<?php echo $item_id ;?>" value="<?php _e( 'Remove Icon', 'uicore-framework'); ?>" onCLick="uicore_remove_icon(this)" />
            <div class='image-preview-wrapper' style="margin-top:5px">
            <span class="ui-icon-color-preview item-color-prev-<?php echo $item_id ;?>" style="--ui-icon-color: <?php echo $css_icon_color ?>"></span>
            <input type="button" class="button select_icon_color_button" data-menuid="<?php echo $item_id ;?>" value="<?php _e( 'Select Color', 'uicore-framework'); ?>" onCLick="uicore_set_color(this, 'icon')" />
            </div>
            <input type='hidden' name='menu-item-icon[<?php echo $item_id ;?>]' value='<?php echo $icon_src ?>'>
            <input type='hidden' name='menu-item-icon-color[<?php echo $item_id ;?>]' value='<?php echo $icon_color ?>'>

        </div>

        <div class="ui-menu-section<?php echo $badge_text ? ' uicore-is-set' : ''; ?>">
            <b><?php _e( 'Badge', 'uicore-framework'); ?></b>
            <input type="text" class="ui-badge-textinput" name='menu-item-badge[<?php echo $item_id ;?>]'
            data-menuid="<?php echo $item_id ;?>" value='<?php echo $badge_text ?>' oninput="uicore_badge_input(this)">
            <div class='image-preview-wrapper' style="margin-top:5px">
                <span class="ui-badge-color-preview item-color-prev-<?php echo $item_id ;?>" style="--ui-badge-color: <?php echo $css_color ?>"></span>
                <input type="button" class="button select_color_button" data-menuid="<?php echo $item_id ;?>" value="<?php _e( 'Select Color', 'uicore-framework'); ?>" onCLick="uicore_set_color(this)" />
                <input type="button" class="button remove_badge_button" data-menuid="<?php echo $item_id ;?>" value="<?php _e( 'Remove Badge', 'uicore-framework'); ?>" onCLick="uicore_remove_badge(this)" />
            </div>
            <input type='hidden' name='menu-item-badge-color[<?php echo $item_id ;?>]' value='<?php echo $badge_color ?>'>
        </div>

        <?php
    }

    function plugin_3rd_party_refresh_style($plugin)
    {
        $plugins = [
            'woocommerce/woocommerce.php',
            'tutor/tutor.php',
            'uicore-animate/uicore-animate.php',
        ];
        if(in_array($plugin,$plugins)){
            $settings = Settings::current_settings();
            Settings::update_style($settings);
        }
    }

    function remove_blog_menu ()
    {
       remove_menu_page('edit.php');
    }

    function add_support_script()
    {
        $connect_data = Helper::handle_connect('get');
        $connect_data = $connect_data['token'] ?? false;

        if($connect_data && !apply_filters('uicore_hide_quick_support',false)){
            $uuid = $connect_data;
            //if uuid contains "ui" split and get only the text after it
            if(strpos($uuid, 'ui') !== false){
                $uuid = explode('ui', $uuid)[1];
            }

        ?>
        <iframe
            id="paia-widget-iframe-a9scjAS7ZuzgGl4AmfkB"
            data-src="https://ask.paia.co/embed/a9scjAS7ZuzgGl4AmfkB"
            title="Ask-Paia"
            style="position: fixed; border: none; opacity:0; z-index: 9999; border-radius: 12px; bottom: 90px; box-shadow: 0px 2px 81px rgba(0, 0, 0, 0.17); right: 25px; width: 400px; height: 600px; max-height:80vh;transform: scale(0.01);transition: transform .3s cubic-bezier(0.59, 0.01, 0.24, 0.99), opacity .3s ease;transform-origin: bottom right;"
        >
        </iframe>

        <button type="button" id="paia-trigger-a9scjAS7ZuzgGl4AmfkB" style="position:fixed;z-index:9999;background-color:#532df5;cursor:pointer;border:none;outline:none;bottom:30px;right:20px;padding:10px;-webkit-appearance:none;border-radius:50%;line-height:0;">
        <svg width="29" height="28" viewBox="0 0 29 28" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M21.5163 4.66406C24.0936 4.66406 26.1829 6.75356 26.1829 9.33073V23.3307C26.1829 24.3702 24.9127 24.9045 24.1777 24.1695L19.8392 20.9974H7.51628C4.93899 20.9974 2.84961 18.9079 2.84961 16.3307V9.33073C2.84961 6.75356 4.93899 4.66406 7.51628 4.66406H21.5163ZM19.1829 11.6641C18.5386 11.6641 18.0163 12.1867 18.0163 12.8307C18.0163 13.4747 18.5386 13.9974 19.1829 13.9974C19.8273 13.9974 20.3496 13.4747 20.3496 12.8307C20.3496 12.1867 19.8273 11.6641 19.1829 11.6641ZM14.5163 11.6641C13.8719 11.6641 13.3496 12.1867 13.3496 12.8307C13.3496 13.4747 13.8719 13.9974 14.5163 13.9974C15.1606 13.9974 15.6829 13.4747 15.6829 12.8307C15.6829 12.1867 15.1606 11.6641 14.5163 11.6641ZM9.84961 11.6641C9.20526 11.6641 8.68294 12.1867 8.68294 12.8307C8.68294 13.4747 9.20526 13.9974 9.84961 13.9974C10.494 13.9974 11.0163 13.4747 11.0163 12.8307C11.0163 12.1867 10.494 11.6641 9.84961 11.6641Z" fill="#FEFEFE"/>
        </svg>
        </button>
        <script>
        window.addEventListener("load", () => {
        // If null the widget will auto generate one
        // change this value to set your own user id
        // the below value must be a non empty string
        const uid = "<?php echo $uuid; ?>"

        const iframe_a9scjAS7ZuzgGl4AmfkB = document.getElementById("paia-widget-iframe-a9scjAS7ZuzgGl4AmfkB")
        const trigger_a9scjAS7ZuzgGl4AmfkB = document.getElementById("paia-trigger-a9scjAS7ZuzgGl4AmfkB")
        const setIframeVisibility=e=>{let t=iframe_a9scjAS7ZuzgGl4AmfkB;if("0"===t.style.opacity){t.style.opacity="1",t.style.transform="none",t.src=t.dataset.src;return}t.style.opacity="0",t.style.transform="scale(0.01)",t.contentWindow.postMessage("regenerate","*")};trigger_a9scjAS7ZuzgGl4AmfkB.addEventListener("click",setIframeVisibility),window.onmessage=e=>{let t=iframe_a9scjAS7ZuzgGl4AmfkB;"paia-widget-close-iframe"===e.data&&setIframeVisibility(),"paia-widget-loaded"===e.data&&t.contentWindow.postMessage({uid},"*")};
        })
        </script>

        <?php
        }
    }

    function handle_portfolio_page_remove($post_id){
        $portfolio_page = Helper::get_option('portfolio_page');
        if(isset($portfolio_page['id']) && $portfolio_page['id'] != '0'){
            $portfolio_post = apply_filters( 'wpml_object_id', $portfolio_page['id'] , 'post', true );
            if($portfolio_post == $post_id){
                $new = Settings::current_settings();
                $new['portfolio_page'] = [
                    'name'	=> 'default',
                    'id'	=> 0
                ];
                Settings::update_settings($new);
            }
        }

    }

}
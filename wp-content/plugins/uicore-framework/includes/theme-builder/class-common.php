<?php
namespace UiCore\ThemeBuilder;

use UiCore\Settings as Settings;
use Elementor\Controls_Manager;
use UiCore\Helper;

defined('ABSPATH') || exit();

/**
 * Theme Builder generic functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.0
 */
class Common
{

    /**
     * Construct Theme Builder generic functions
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.0
     */
    public function __construct()
    {
        $this->init_utils();

		add_filter( 'pll_copy_taxonomies', [$this,'pll_copy_tax'], 10, 2 );
        add_action('init', [$this, 'register_ctp'], 10);
        add_filter('single_template', [$this, 'custom_templates']);
        add_filter( 'theme_uicore-tb_templates', [$this, 'custom_templates_list'] );
        add_shortcode('uicore-block', [$this, 'blocks_shortcode']);


        if(\class_exists('\Elementor\Plugin')){
            add_action( 'elementor/documents/register', [ $this, 'register_tb_types' ] );

            add_action( 'elementor/element/column/section_style/before_section_end', [$this, 'feature_img_controls'] );
            add_action( 'elementor/element/section/section_background/before_section_end', [$this, 'feature_img_controls'] );
            add_action( 'elementor/element/container/section_background/before_section_end', [$this, 'feature_img_controls'] );
            add_action( 'elementor/frontend/column/before_render', [$this, 'feature_img_render'],11, 1 );
            add_action( 'elementor/frontend/section/before_render', [$this, 'feature_img_render'],11, 1 );
            add_action( 'elementor/frontend/container/before_render', [$this, 'feature_img_render'],11, 1 );

            //include woo category; theme builder widgets and custom controls
            add_action('elementor/elements/categories_registered', [$this, 'register_widgets_category']);
            add_action('elementor/widgets/register', [$this, 'init_widgets']);
            add_action('elementor/controls/register', [$this, 'init_controls']);
        }
    }

    function feature_img_render($section){
        $active = $section->get_settings('section_feature_img');
		if ('yes' === $active) {
            $post_id = Helper::get_current_meta_id();
            $img = wp_get_attachment_image_src(get_post_thumbnail_id( $post_id ), 'full');
            if (isset($img[0]) && $img[0] != null) {
                $url = esc_url($img[0]);

                if($section->get_name() === 'column'){
                    $section->add_render_attribute('_widget_wrapper', 'style', 'background-image:url('.$url.')');
                }else{
                    $section->add_render_attribute('_wrapper', 'style', 'background-image:url('.$url.')');
                }
            }
		}
    }
    public function feature_img_controls($section){
        $section->start_injection(
			[
				'type' => 'control',
				'at'   => 'after',
				'of'   => 'background_color',
			] );

		$section->add_control(
			'section_feature_img',
			[
				'label'        => UICORE_BADGE . esc_html__( 'Featured Image', 'uicore-framework' ),
				'type'         => Controls_Manager::SWITCHER,
				'default'      => '',
				'return_value' => 'yes',
				'description'  => esc_html__( 'Please also set an image as a fallback.', 'uicore-framework' ),
				'render_type'  => 'template',
				'frontend_available' => false,
                'condition' => [
                    'background_background' => [ 'classic' ],
                ]
			]
		);
        $section->end_injection();
    }

    /**
     * Register uicore Theme Builder Elementor Document Type
     *
     * @param [type] $documents_manager
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
	public function register_tb_types( $documents_manager )
    {
        $docs_types = [
            'header' => Documents\Base::get_class_full_name(),
            'footer' => Documents\Base::get_class_full_name(),
            'popup' => Documents\Base::get_class_full_name(),
            'megamenu' => Documents\Base::get_class_full_name(),
            'popup' => Documents\Base::get_class_full_name(),
            'pagetitle' => Documents\Single::get_class_full_name(),
            'single' => Documents\Single::get_class_full_name(),
            'archieve' => Documents\Base::get_class_full_name(),
        ];

        foreach ( $docs_types as $type => $class_name ) {
			$documents_manager->register_document_type( $type, $class_name );
		}
	}

    /**
     * Register Custom Post for Theme Builder
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function register_ctp()
    {
        $name =  __('Theme Builder','uicore-framework');
        $slug = 'uicore-tb';

        register_taxonomy(
            'tb_type',
            [],
            [
                'hierarchical' => false,
                'public' => false,
                'label' => _x( 'Type', 'Theme Builder', 'uicore-framework' ),
                'show_ui' => false,
                'show_admin_column' => false,
                'query_var' => true,
                'show_in_rest' => false,
                'rewrite' => false,
            ]
        );
        register_taxonomy(
            'tb_rule',
            [],
            [
                'hierarchical' => false,
                'public' => false,
                'show_ui' => false,
                'show_admin_column' => false,
                'query_var' => true,
                'show_in_rest' => false,
                'rewrite' => false,
            ]
        );

        register_post_type($slug, [
            'labels' => [
                'name' => $name,
                'singular_name' => $name,
            ],
            'has_archive' => false,
            'show_ui' => true,
            'show_in_menu' => false,
            'show_in_admin_bar' => false,
            'show_in_nav_menus' => true,
            'taxonomies' => ['tb_type','tb_rule'],
            'menu_icon' => 'dashicons-format-gallery',
            'public' => true,
            'rewrite' => false,
            'show_in_rest' => true,
			'exclude_from_search' => true,
			'capability_type' => 'post',
			'hierarchical' => false,
			'supports' => [ 'title', 'thumbnail', 'author', 'elementor','editor','revisions' ],

        ]);
    }

    /**
     * Force specific template for theme builder
     *
     * @param [type] $single
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function custom_templates($single)
    {
        global $post;

        /* Checks for single template by post type */
        if ( $post->post_type == 'uicore-tb' ) {

            //we need more controll on popup
            if(self::get_the_type($post->ID) === 'popup'){
                return UICORE_INCLUDES . '/theme-builder/templates/popup.php';
            }
            //default
            return UICORE_INCLUDES . '/theme-builder/templates/canvas.php';
        }
        return $single;
    }

    /**
     * Add Theme Builder Canvas to Templates
     *
     * @param [type] $post_templates
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function custom_templates_list( $post_templates )
    {
        $post_templates[UICORE_INCLUDES . '/theme-builder/templates/canvas.php'] = "ThemeBuilder Canvas";
        $post_templates[UICORE_INCLUDES . '/theme-builder/templates/popup.php'] = "ThemeBuilder Popup";
        return $post_templates;
    }

    /**
     * Get Elementor content for display
     *
     * @param [type] $content_id
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    static function get_builder_content( $content_id , $with_style = true )
    {
        $content = '';
        $content_id = apply_filters( 'wpml_object_id', $content_id, 'post', true );
        //first check that post exist
        if(!get_post($content_id)){
            return;
        }
        if(\class_exists('\Elementor\Plugin') && \Elementor\Plugin::$instance->documents->get( $content_id )->is_built_with_elementor()){
            $elementor_instance = \Elementor\Plugin::instance();
            $content = $elementor_instance->frontend->get_builder_content_for_display( $content_id );

            if($with_style){
                $css_file = new \Elementor\Core\Files\CSS\Post( $content_id );
                $css_file->enqueue();
            }


        }else{
            $content = apply_filters('the_content', get_post_field('post_content', $content_id));
            if($with_style){
                if(\class_exists('\UiCoreBlocks\Frontend')){
                    \UiCoreBlocks\Frontend::enqueue_post_assets($content_id);
                }
            }
        }
        return $content;
    }

    /**
     * Get ThemeBuilder element Type
     *
     * @param int $post_id
     * @return string
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    static function get_the_type($post_id)
    {
        $type = wp_get_post_terms($post_id, 'tb_type', ['fields' => 'names']);
        $type = $type[0] ? str_replace('_type_', '', $type[0] ) : '';
        $type = ($type === 'mm') ? 'mega menu' : $type;
        return $type;
    }

    static function get_cpt_list()
    {
        $args = array(
			'public'   => true,
			'_builtin' => true,
		);
		$post_types = get_post_types( $args, 'objects' );

        $args['_builtin'] = false;
        $custom_post_type = get_post_types( $args, 'objects' );

        $post_types = array_merge( $post_types, $custom_post_type );
        unset( $post_types['attachment'] );
        unset( $post_types['post'] );
        unset( $post_types['page'] );
        unset( $post_types['elementor_library'] );
        unset( $post_types['uicore-tb'] );
        unset( $post_types['e-landing-page'] );
        return $post_types;
    }

    /**
     * Get Rule locations list
     *
     * @return array
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
	public static function get_location_selections() {

        $post_types = self::get_cpt_list();

		$special_pages = array(
			array(
				'name'    => __( '404 Page', 'uicore-framework' ),
				'value'    => 'special-404'
			),
			array(
				'name'    => __( 'Search Page', 'uicore-framework' ),
				'value'    => 'special-search'
			),
			array(
				'name'    => __( 'Blog / Posts Page', 'uicore-framework' ),
				'value'    => 'special-blog'
            ),
			array(
				'name'    => __( 'Front Page', 'uicore-framework' ),
				'value'    => 'special-front'
			),
			array(
				'name'    => __( 'Date Archive', 'uicore-framework' ),
				'value'    => 'special-date'
			),
			array(
				'name'    => __( 'Author Archive', 'uicore-framework' ),
				'value'    => 'special-author'
			)
		);

		if ( class_exists( 'WooCommerce' ) ) {
			$special_pages[] = array(
				'name'    => __( 'WooCommerce Shop Page', 'uicore-framework' ),
				'value'    => 'special-woo-shop'
			);
		}
        foreach($post_types as $post_type){
            $special_pages[] = array(
				'name'    => $post_type->label,
				'value'    => 'cp-'.$post_type->name
			);
            $special_pages[] = array(
				'name'    => $post_type->label. ' Archive',
				'value'    => 'cp-archive-'.$post_type->name
			);

            if($post_type->name === 'portfolio'){
                $special_pages[] = array(
                    'name'    => __( 'Portfolio Category Archive', 'uicore-framework' ),
                    'value'    => 'cp-archive-portfolio_category'
                );
            }

        }
        // print_r($special_pages);
		$selection_options = array(
			'basic'         => array(
				'label' => __( 'Basic', 'uicore-framework' ),
				'value' => array(
					array(
						'name'    => __( 'Entire Website', 'uicore-framework' ),
						'value'    => 'basic-global'
					),
					array(
						'name'    => __( 'All Pages', 'uicore-framework' ),
						'value'    => 'basic-page'
					),
					array(
						'name'    => __( 'All Blog Posts', 'uicore-framework' ),
						'value'    => 'basic-single'
					),
					array(
						'name'    => __( 'All Archives', 'uicore-framework' ),
						'value'    => 'basic-archives'
					),

				),
			),

			'special-pages' => array(
				'label' => __( 'Special Pages', 'uicore-framework' ),
				'value' => $special_pages,
			),
		);


		$selection_options['specific-target'] = array(
			'label' => __( 'Specific Target', 'uicore-framework' ),
			'value' => array(
				array(
					'name'    => __( 'Specific', 'uicore-framework' ),
					'value'    => 'specifics'
				),
			),
		);

		return $selection_options;
	}

    /**
     * Shortcode function for blocks
     *
     * @param [type] $atts
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 2.0.0
     */
    function blocks_shortcode($atts)
    {
        $atr = shortcode_atts(
            [
                'id' => false,
            ],
            $atts
        );
        if($atr['id']){
            return Common::get_builder_content($atr['id']);
        }
    }

    static function popup_markup($content, $id, $is_product_info=false)
    {
        //check first if is in edit mode but on a page where is embeded and hide it if so
        if( isset($_GET['elementor-preview']) && $_GET['elementor-preview'] != $id ){
            return;
        }


        $is_prev = isset($_GET['ui-popup-preview']);
        $is_editor = isset($_GET['elementor-preview']);
        $css_class = $is_editor ? $id . ' ui-popup-active' : $id;

        //we don't need it if is prev iframe
        if($is_prev){
            ?>
            <style>
            #wpadminbar { display:none !important;}
            </style>
            <?php
            return null;
        }

        $trigger = false;
		$settings = get_post_meta($id, 'tb_settings', true);
        if(is_array($settings)){
            $trigger = $settings['trigger'];
        }

        //if is product info we need to trigger it on click witch hardcoded settings
        if($is_product_info){
            $trigger = [
                'maxShow' => [
                    'enable' => 'false',
                ],
                'responsive' => [
                    'desktop' => 'true',
                    'tablet' => 'true',
                    'mobile' => 'true',
                ],
                'pageScroll' => [
                    'enable' => 'false',
                ],
                'pageLoad' => [
                    'enable' => 'false',
                ],
                'scrollToElement' => [
                    'enable' => 'false',
                ],
                'click' => [
                    'enable' => 'false',
                ],
                'clickOnElement' => [
                    'enable' => 'true',
                    'selector' => '[data-id="'.$id.'"].uicore-product-info-popup-trigger',
                ],
                'onExit' => [
                    'enable' => 'false',
                ],
                'maxShow' => [
                    'enable' => 'false',
                ],
            ];
        }


        self::get_generic_style();

        self::get_specific_style($id,$settings);
        ?>

        <div class="ui-popup-wrapper ui-popup-<?php echo $css_class; ?>">

            <?php
            if($settings['overlay'] === 'true'){
            ?>
                <div class="ui-popup-overlay"></div>
            <?php }
            ?>

            <div class="ui-popup">
            <?php
                if($settings['close'] === 'true'){
                ?>
                <div class="ui-popup-close uicore-i-close">
                </div>
                <?php }

                echo $content;
            ?>
            </div>
        </div>
        <?php
        if($trigger && !$is_editor){
           self::get_js($id,$trigger, $settings);
        }
    }

    static function css_position_filter($value)
    {
        if($value === 'bottom' || $value === 'right'){
            return 'flex-end';
        }elseif($value === 'top' || $value === 'left'){
            return 'flex-start';
        }else{
            return $value;
        }
    }

    static function get_js($id,$trigger,$settings)
    {
        $js = null;
        $extra = null;

        //run the triggers js only if we need to show the popup again
        if( $trigger['maxShow']['enable'] === 'true' ){
            $js .= "
            var uicoreNow = new Date().getTime();
            var uicore_popup_".$id." = JSON.parse(localStorage.getItem('uicore_popup_".$id."'));

            //* 24 * 60 * 60 * 1000 replace with 60 * 1000 for testing (repalce days with minutes)
            if (uicore_popup_".$id." && uicore_popup_".$id.".createdTime + (Number(".$trigger['maxShowdelay']['amount'].") * 60 * 1000) < uicoreNow) {
                localStorage.removeItem('uicore_popup_".$id."');
                uicore_popup_".$id." = null;
            }

            if(uicore_popup_".$id." && (uicore_popup_".$id.".count !== undefined && uicore_popup_".$id.".count < ".$trigger['maxShow']['amount'].") || !uicore_popup_".$id." ){
            ";

            $extra .= "
            if(!uicore_popup_".$id."){
                uicore_popup_".$id." = {
                    count: 1,
                    createdTime: uicoreNow
                };
                localStorage.setItem('uicore_popup_".$id."', JSON.stringify(uicore_popup_".$id."));
            }else{
                uicore_popup_".$id.".count++;
                localStorage.setItem('uicore_popup_".$id."', JSON.stringify(uicore_popup_".$id."));
            }";
        }



        $condition = 'true';


        //responsive
        if($settings['responsive']['desktop'] === 'true'){
            $condition .= " && !window.matchMedia( '(min-width: 1025px)' ).matches";
        }
        if($settings['responsive']['tablet'] === 'true'){
            $condition .= " && !window.matchMedia( '(min-width: 768px) and ( max-width: 1025px)' ).matches";
        }
        if($settings['responsive']['mobile'] === 'true'){
            $condition .= " && !window.matchMedia( '(max-width: 767px)' ).matches";
        }

        $js .= "if(".$condition."){
            ";


        if( $settings['pageScroll']  === 'true'){
            //prevent scroll
            $extra .= '
            document.documentElement.setAttribute("style","overflow:hidden;");
            if(ui_animate_lenis){
                ui_animate_lenis.stop();
            }
            ';
        }

        //Triggers
        $js .=  'var uipopupTrigger'.$id.' = function() {
                document.querySelectorAll(".ui-popup-'.$id.'").forEach(function(el) {
                    el.classList.add("ui-popup-active");
                    el.querySelector(".ui-popup > div:not(.ui-popup-close)").setAttribute("data-lenis-prevent", "true");
                });'
                .$extra.'
                };';

        if( $trigger['pageLoad']['enable'] === 'true' ){
            $js .= "
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(function(){
                    uipopupTrigger".$id."();
                }, ".($trigger['pageLoad']['delay'] * 1000).");
            });
            ";
        }
        if( $trigger['pageScroll']['enable'] === 'true' ){
            $direction = ($trigger['pageScroll']['direction'] === 'down')
            ? '> previousScroll && (currentScroll/(docheight-winheight)) > scrolltrigger'
            : '< previousScroll';
            $js .= "
            document.addEventListener('DOMContentLoaded', function() {
                var previousScroll = 0;
                var scrolltrigger = 0.".$trigger['pageScroll']['amount'].";
                window.addEventListener('scroll', pageScrollTrigger".$id.");
                function pageScrollTrigger".$id."() {
                    var currentScroll = window.scrollY || window.pageYOffset;
                    var docheight = Math.max(
                        document.body.scrollHeight, document.documentElement.scrollHeight,
                        document.body.offsetHeight, document.documentElement.offsetHeight,
                        document.body.clientHeight, document.documentElement.clientHeight
                    );
                    var winheight = window.innerHeight;
                    if (currentScroll ". $direction ."){
                        uipopupTrigger".$id."();
                        window.removeEventListener('scroll', pageScrollTrigger".$id.");
                    }
                    previousScroll = currentScroll;
                };
            });
            ";
        }
        if( $trigger['scrollToElement']['enable'] === 'true' ){
            $element = $trigger['scrollToElement']['selector'];
            $js .= "
            document.addEventListener('DOMContentLoaded', function() {
                window.addEventListener('scroll', scrollElementTrigger".$id.");
                function scrollElementTrigger".$id."() {
                    var element = document.querySelector('".$element."');
                    if (!element) return;
                    var rect = element.getBoundingClientRect();
                    var top = rect.top + window.scrollY;
                    var bottom = rect.bottom + window.scrollY;
                    var toBottom = window.scrollY + window.innerHeight;
                    var toTop = window.scrollY;

                    if ((toBottom > top) && (toTop < bottom)){
                        uipopupTrigger".$id."();
                        window.removeEventListener('scroll', scrollElementTrigger".$id.");
                    }
                }
            });
            ";
        }
        if( $trigger['click']['enable'] === 'true' ){
            $no = $trigger['click']['clicks'];
            $js .= "
            document.addEventListener('DOMContentLoaded', function() {
                var clicks = 0;
                var maxClicks = ".$no.";
                window.addEventListener('click', clickTrigger".$id.");
                function clickTrigger".$id."() {
                    clicks++;
                    if (clicks > maxClicks){
                        uipopupTrigger".$id."();
                        window.removeEventListener('click', clickTrigger".$id.");
                    }
                }
            });
            ";
        }
        if( $trigger['clickOnElement']['enable'] === 'true' ){
            $element = $trigger['clickOnElement']['selector'];
            $js .= "
            document.addEventListener('DOMContentLoaded', function() {
                document.querySelectorAll('".$element."').forEach(function(el) {
                    el.addEventListener('click', uipopupTrigger".$id.");
                });
            });
            ";
        }
        if( $trigger['onExit']['enable'] === 'true' ){
            $js .= "
            document.addEventListener('DOMContentLoaded', function() {
                document.addEventListener('mouseout', onExitTrigger".$id.");
                function onExitTrigger".$id."(event) {
                    if (!event.toElement && !event.relatedTarget) {
                        uipopupTrigger".$id."();
                        document.removeEventListener('mouseout', onExitTrigger".$id.");
                    }
                }
            });
            ";
        }

        //run the triggers js only if we need to show the popup again (close the js if)
        if( $trigger['maxShow']['enable'] === 'true' ){
            $js .= "
                }
                ";
        }

        //responsive
        $js .= "
        }
        ";

        //close on overlay
        if($settings['overlay'] && $settings['closeOnOverlay'] === 'true'){
            $extra_class_for_close = ", .ui-popup-overlay";
        }else{
            $extra_class_for_close = "";
        }
        if(isset($settings['closeOnElement']) && $settings['closeOnElement'] !== ''){
            $extra_class_for_close .= ", ".$settings['closeOnElement'];
        }
        ?>
        <script>
            <?php echo $js; ?>
            document.addEventListener('DOMContentLoaded', function() {
                var closeSelectors = '.ui-popup-close' + '<?php echo $extra_class_for_close; ?>';
                document.querySelectorAll(closeSelectors).forEach(function(el) {
                    el.addEventListener('click', function() {
                        var popup = el.closest('.ui-popup-active');
                        if (popup) {
                            popup.classList.remove('ui-popup-active');
                        }
                        document.documentElement.setAttribute("style", "overflow:unset;");
                        if (typeof ui_animate_lenis !== 'undefined' && ui_animate_lenis) {
                            ui_animate_lenis.start();
                        }
                    });
                });
            });
        </script>
        <?php
    }

    static function get_specific_style($id, $settings)
    {

        $css = null;
        $css_wrapp = null;
        $css_close = null;


        if($settings['width']['mode'] === 'custom'){
            $css .= 'width:' . $settings['width']['size'] . 'px;';
        }elseif($settings['width']['mode'] === 'full'){
            $css .= 'min-width:100vw;';
        }

        if($settings['height']['mode'] === 'custom'){
            $css .= 'max-height:' . $settings['height']['size'] . 'px;';
        }elseif($settings['height']['mode'] === 'full'){
            $css .= 'min-height:100vh;';
        }

        $position = explode(" ", $settings['position']);
        $css_wrapp .= 'align-items:'. self::css_position_filter($position[0]) .';';
        $css_wrapp .= 'justify-content:'. self::css_position_filter($position[1]) .';';

        if($settings['close'] === 'true'){
            $css_close = '.ui-popup-'. $id . ' .ui-popup-close {';
            $css_close .= 'color:' . Settings::color_filter($settings['closeColor']['default']);
            $css_close .= '}';
            $css_close .= '.ui-popup-'. $id . ' .ui-popup-close:hover {';
            $css_close .= 'color:' . Settings::color_filter($settings['closeColor']['hover']);
            $css_close .= '}';
        }
        if($settings['overlay'] && $settings['closeOnOverlay'] === 'true'){
            $css_close .= '.ui-popup-'. $id . ' .ui-popup-overlay {
             cursor: url(\'data:image/svg+xml;base64,PHN2ZyB4bWxucz0iaHR0cDovL3d3dy53My5vcmcvMjAwMC9zdmciIHdpZHRoPSIzMiIgaGVpZ2h0PSIzMiIgdmlld0JveD0iMCAwIDMyIDMyIj48bGluZSB4MT0iOCIgeTE9IjgiIHgyPSIyNCIgeTI9IjI0IiBzdHJva2U9ImJsYWNrIiBzdHJva2Utd2lkdGg9IjIiLz48bGluZSB4MT0iMjQiIHkxPSI4IiB4Mj0iOCIgeTI9IjI0IiBzdHJva2U9ImJsYWNrIiBzdHJva2Utd2lkdGg9IjIiLz48L3N2Zz4=\'), auto;
            }';
        }

        $animation = str_replace(' ', '', ucwords($settings['animation']) );
        $css .= 'animation-name:uicore'.$animation;

        ?>
        <style id="ui-popup-style-<?php echo $id; ?>">
        .ui-popup-<?php echo $id; ?>{
            <?php echo $css_wrapp; ?>
        }
        .ui-popup-<?php echo $id; ?> .ui-popup{
            <?php echo $css; ?>
        }
        <?php echo $css_close; ?>

        </style>
        <?php

    }

    static function get_generic_style()
    {
        static $is_embeded = false;
        if( !$is_embeded ){
            $is_embeded = true;
            ?>
            <style id="ui-popup-style">
            .ui-popup-background, .ui-popup-wrapper, .ui-popup-overlay{
                position: fixed;
                width: 100vw;
                height:100vh;
                top: 0;
                left: 0;
            }
            .ui-popup-overlay{
                background-color: rgba(0, 0 ,0, 40%);
                pointer-events: all;
            }
            .ui-popup-wrapper{
                display: none;
                z-index: 9999;
                animation-name: uicoreFadeIn;
	            animation-timing-function: ease-in-out;
                animation-duration: .4s;
                pointer-events: none;
            }
            .ui-popup-active{
                display: flex;
            }
            .ui-popup{
                display: none;
                position: relative;
                width: 100%;
                max-width: 100vw;
                max-height: 95vh;
                animation-duration: .6s;
				overflow: hidden;
                pointer-events: all;
            }
            .ui-popup-active .ui-popup{
                display: flex;
            }
            .ui-popup-close{
                position: absolute;
                right: 6px;
                top: 6px;
                padding: 10px;
                font-size: 12px;
                z-index: 1;
                line-height: 1;
                cursor: pointer;
                transition: all .3s ease-in-out;
            }
            .ui-popup-wrapper [data-elementor-type=uicore-tb] {
                overflow: hidden auto;
                width: 100%;
            }
            .ui-popup-wrapper [data-elementor-type=uicore-tb] .uicore-section-wrap:not(:empty)+#elementor-add-new-section {
                display: none;
            }
			.elementor-add-section:not(.elementor-dragging-on-child) .elementor-add-section-inner {
				background-color: #fff;
			}
            </style>

        <?php

        }
    }

	function pll_copy_tax( $taxonomies, $sync ) {
		$taxonomies[] = 'tb_type';
		return $taxonomies;
	}

    function init_controls() {
        require_once UICORE_INCLUDES . '/elementor/generic/class-post-filter-control.php';
    }

    function init_utils() {
        require_once UICORE_INCLUDES . '/elementor/generic/query-component.php';
        //Extender
        require UICORE_INCLUDES . '/elementor/class-extender.php';
    }

    function init_widgets() {
        //abstract base class
        require_once UICORE_INCLUDES . '/elementor/class-widget-base.php';

        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-gallery.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-price.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-stock.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-meta.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-content.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/short-description.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-tabs.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-grid.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-add-to-cart.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/breadcrumbs.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/product-rating.php';
        require_once UICORE_INCLUDES . '/theme-builder/elementor/woo-widgets/sale-badge.php';
    }

    function register_widgets_category($elements_manager)
    {
        $elements_manager->add_category('uicore-woo', [
            'title' => __('UiCore Woo', 'uicore-framework'),
            'icon' => 'fa fa-plug',
        ]);
    }
}
new Common();

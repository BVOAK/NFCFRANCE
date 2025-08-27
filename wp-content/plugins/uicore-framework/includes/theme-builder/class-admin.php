<?php
namespace UiCore\ThemeBuilder;
defined('ABSPATH') || exit();

use UiCore\Data as Data;

/**
 * Theme Builder generic functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.0
 */
class Admin
{
    /**
     * Construct Theme Builder generic functions
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 2.0.0
     */
    public function __construct()
    {
        //Add Theme Builde in Admin Menu
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );

        add_filter( 'views_edit-uicore-tb', [ $this, 'admin_print_tabs' ] );
		add_filter('manage_uicore-tb_posts_columns', [ $this,'custom_columns' ] );
		add_filter('manage_uicore-tb_posts_custom_column', [ $this,'display_custom_columns' ] );

        add_action( 'admin_enqueue_scripts',[$this, 'enqueue_scripts_and_style'] );

        //display type in megamenu
        add_filter( 'wp_setup_nav_menu_item', function ($menu_item) {
           if($menu_item->object === 'uicore-tb'){
            $menu_item->type_label = 'Theme Builder Mega Menu';
           }
            return $menu_item;
        });

		//Display only megamenu type in admin menu items
		add_filter('nav_menu_items_uicore-tb', [$this, 'filter_megamenu_in_menu']);
		add_filter('nav_menu_items_uicore-tb_recent', [$this, 'filter_megamenu_in_menu']);

		add_action('elementor/editor/after_enqueue_scripts', [$this, 'popup_extra_in_elementor']);


    }

	function popup_extra_in_elementor()
	{
		?>
		<style>
			#elementor-notice-bar {
				display:none!important;
			}
    	</style>
		<?php
	}

	/**
	 * Keep only mega menu type in list
	 *
	 * @param array $menu_item
	 * @return array
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
	function filter_megamenu_in_menu($menu_item)
	{
		$new_items = array();
		foreach($menu_item as $item){
			if(Common::get_the_type($item->ID) === 'mega menu'){
				$new_items[] = $item;
			}
		}
		return $new_items;
	}

	/**
	 * Register Theme Builder admin menu
	 *
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
    public function register_admin_menu() {
		$icon_url = get_template_directory_uri()."/assets/img/theme-builder-icon.svg";
		$icon_url = apply_filters('uicore_theme_icon_url', $icon_url);

        $hook = add_menu_page(
            __('Theme Builder', 'uicore-framework'),
            __('Theme Builder', 'uicore-framework'),
            'manage_options',
            'edit.php?post_type=uicore-tb',
            '',
            $icon_url,
            3
        );
        add_action('load-' . $hook, function () {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_scripts_and_style'], 1);
        } );
    }

	/**
	 * Theme builder style and scripts enqueue
	 *
	 * @param [type] $hook
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
    public function enqueue_scripts_and_style($hook)
    {
        global $typenow;

        if($hook === 'edit.php' && $typenow === 'uicore-tb'){
            $prefix = (( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) || defined('UICORE_LOCAL')) ? '' : '.min';
            wp_enqueue_script(
                'uicore-tb-vendor',
                UICORE_ASSETS . '/js/vendor' . $prefix . '.js',
                ['jquery'],
                filemtime(UICORE_PATH . '/assets/js/vendor' . $prefix . '.js'),
                true
            );
            wp_enqueue_script(
                'uicore-tb',
                UICORE_ASSETS . '/js/theme-builder' . $prefix . '.js',
                ['jquery'],
                filemtime(UICORE_PATH . '/assets/js/theme-builder' . $prefix . '.js'),
                true
            );

            wp_enqueue_style(
                'uicore-tb-styles',
                UICORE_ASSETS . '/css/theme-builder.css',
                [],
                filemtime(UICORE_PATH . '/assets/css/theme-builder.css')
            );
            wp_enqueue_style('uicore-admin');
            wp_enqueue_style('uicore-admin-icons');
			wp_enqueue_style('uicore-admin-font');
			wp_add_inline_script('uicore-tb', 'var uicore_data = '.json_encode(Data::get_theme_builder_data()), 'before');
        }

    }

	/**
	 * Print Admin Tabs
	 *
	 * @param [type] $views
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
	public function admin_print_tabs( $views )
	{

		$current_type = '';
		$active_class = ' nav-tab-active';

		if ( ! empty( $_REQUEST['tb_type'] ) ) {
			$current_type = $_REQUEST['tb_type'];
			$active_class = '';
		}

		$url_args = [
			'post_type' => 'uicore-tb',
		];

		$baseurl = add_query_arg( $url_args, admin_url( 'edit.php' ) );

		$doc_types = self::get_tb_types();
		?>

        <div id="uicore-tb-wrapp"></div>
		<div id="uicore-theme-builder-tabs" class="nav-tab-wrapper">
			<a class="nav-tab<?php echo $active_class; ?>" href="<?php echo $baseurl; ?>">
				<?php echo  __( 'All', 'uicore-framework' ); ?>
			</a>
			<?php
			foreach ( $doc_types as $type => $type_label ) :
				$active_class = '';

				if ( $current_type === $type ) {
					$active_class = ' nav-tab-active';
				}

				$type_url = add_query_arg( 'tb_type', $type, $baseurl );

				echo "<a class='nav-tab{$active_class}' href='{$type_url}'>{$type_label}</a>";
			endforeach;
			?>
		</div>
		<?php
		return $views;
	}

	public static function get_tb_types()
	{
		$types =  array(
			'_type_header' => __('Header', 'uicore-framework'),
			'_type_footer' => __('Footer', 'uicore-framework'),
			'_type_mm' => __('Mega Menu', 'uicore-framework'),
			'_type_block' => __('Block', 'uicore-framework'),
			'_type_popup' => __('Popup', 'uicore-framework'),
			'_type_pagetitle' => __('Page Title', 'uicore-framework'),
			'_type_single' => __('Single', 'uicore-framework'),
			'_type_archive' => __('Archive', 'uicore-framework'),
		);
		//if woocomerce is active
		if ( class_exists( 'WooCommerce' ) ) {
			$types['_type_productinfo'] = __('Product Info', 'uicore-framework');
		}
		return apply_filters('uicore_tb_types', $types);
	}

	/**
	 * Add Custom Columns in admin view table
	 *
	 * @param [type] $columns
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 6.0.0
	 */
	function custom_columns( $columns )
	{
		$columns['type'] = 'Type';
    	$columns['info'] = 'Info';

    return $columns;
	}

	/**
	 * Admin Custom Columns view table content
	 *
	 * @param [type] $name
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
	function display_custom_columns( $name ) {
		global $post;

		switch ($name) {
			case 'type':
				echo Common::get_the_type($post->ID);
				break;
			case 'info':
				echo $this->get_item_info($post->ID);
				break;
		}
	}


	/**
	 * Get post type
	 *
	 * @param string $default
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
    public function get_current_tab_group( $default = '' ) {
		$current_tabs_group = $default;

		if ( ! empty( $_REQUEST['tb_type'] ) ) {
			$doc_type = \Elementor\Plugin::$instance->documents->get_document_type( $_REQUEST['tb_type'], '' );
			if ( $doc_type ) {
				$current_tabs_group = $doc_type::get_property( 'admin_tab_group' );
			}
		} elseif ( ! empty( $_REQUEST['tabs_group'] ) ) {
			$current_tabs_group = $_REQUEST['tabs_group'];
		}

		return $current_tabs_group;
    }

	/**
	 * Get Item Info to diplay in admin table
	 *
	 * @param int $post_id
	 * @return void
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
	function get_item_info($post_id)
	{
		$type = Common::get_the_type($post_id);
		$info = '';

		if(\in_array($type, ['mega menu', 'block'])){
			$settings = get_post_meta($post_id, 'tb_settings', true);
			if(!\is_array($settings)){
				return '';
			}
		}
		if ($type === 'mega menu') {
			$info = '<b>Width:</b> '.ucfirst( $settings['width'] );
			if($settings['width'] == 'custom'){
				$info .=' ('.$settings['widthCustom'] .'px)';
			}
		}elseif ($type === 'block'){
			if( (isset($settings['blockHook']) && $settings['blockHook']['value'] == 'custom' ) || !isset($settings['blockHook'])) {
				$info = '<input class="wp-ui-text-highlight code" type="text" onfocus="this.select();" readonly="readonly" value="[uicore-block id=&quot;'.$post_id.'&quot;]">';
			}else{
				$info = '<b>Hook:</b> '.$settings['blockHook']['name'].'<br/><i>('.$settings['blockHook']['value'].')</i>';
			}

		}else{
			$info = self::get_pretty_condition('include', $post_id) .'</br>'. self::get_pretty_condition('exclude', $post_id);


		}

		return $info;
	}

	/**
	 * Get pretty condition to display in admin table
	 *
	 * @param string $type
	 * @param [type] $post_id
	 * @return string
	 * @author Andrei Voica <andrei@uicore.co>
	 * @since 2.0.0
	 */
	static function get_pretty_condition($type, $post_id){

		$info = null;
		$include = get_post_meta($post_id, 'tb_rule_'.$type, true);

		if(is_array($include)){
			$lastKey = array_keys($include);
			$lastKey = \end($lastKey);
			$info .= '<b>'.ucfirst( $type ).': </b>';

			foreach($include as $k => $rule){
				if($rule['rule']['value'] === 'specifics'){
					$specific_pages = null;
					if(isset($rule['specific']) && \is_array($rule['specific'])){
						$specificKeys = array_keys($rule['specific']);
						$lastKey2 = end($specificKeys);
						foreach($rule['specific'] as $k2 => $specific){
							$specific_pages .= $specific['text'] . ($lastKey2 === $k2 ? null : ', ') ;
						}

						$info .= 'Specific ('.$specific_pages. ')'. ($lastKey2 === $k2 ? null : ', ') ;
					}

				}else{
					$info .= $rule['rule']['name'] . ($lastKey === $k ? null : ', ') ;
				}
			}
		}
		return $info;
	}
}
new Admin();

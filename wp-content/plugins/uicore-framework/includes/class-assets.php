<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Register Scripts and Styles Class
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 1.0.0
 */
class Assets
{
    /**
     * Add actions
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function __construct()
    {
        if (is_admin()) {
            add_action('admin_enqueue_scripts', [$this, 'register_styles'], 5);
            add_action('admin_enqueue_scripts', [$this, 'register_scripts'], 9);
        } else {
            add_action('wp_enqueue_scripts', [$this, 'register_styles'], 5);
            add_action('wp_enqueue_scripts', [$this, 'register_scripts'], 9);
        }
        // Add filter to async load snippet styles
		add_filter('style_loader_tag', [__CLASS__, 'filter_snippet_asset_tag'], 10, 2);
		add_filter('script_loader_tag', [__CLASS__, 'filter_snippet_asset_tag'], 10, 2);
    }

    /**
     * Register used scripts
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function register_scripts()
    {
        $scripts = $this->get_scripts();
        foreach ($scripts as $handle => $script) {
            $deps = isset($script['deps']) ? $script['deps'] : false;
            $in_footer = isset($script['in_footer']) ? $script['in_footer'] : false;
            $version = isset($script['version']) ? $script['version'] : UICORE_VERSION;

            wp_register_script($handle, $script['src'], $deps, $version, $in_footer);
        }
    }

    /**
     * Register used stylesheets
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function register_styles()
    {
        $styles = $this->get_styles();
        foreach ($styles as $handle => $style) {
            $deps = isset($style['deps']) ? $style['deps'] : false;
            $version =  isset($style['version']) ? $style['version'] : UICORE_VERSION;
            wp_register_style($handle, $style['src'], $deps, $version);
        }
    }

    /**
     * Define Script Array
     *
     * @return array $scripts
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_scripts()
    {
        // $upload_dir = wp_upload_dir();
        $version = Helper::get_option('settings_version', UICORE_VERSION);

        $prefix =  (( defined('SCRIPT_DEBUG') && SCRIPT_DEBUG ) || defined('UICORE_LOCAL')) ? '' : '.min';
        $scripts = [
            'uicore-vendor' => [
                'src' => UICORE_ASSETS . '/js/vendor' . $prefix . '.js',
                'version' => filemtime(UICORE_PATH . '/assets/js/vendor' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-frontend' => [
                'src' => UICORE_ASSETS . '/js/frontend' . $prefix . '.js',
                //'deps' => ['jquery'],
                'version' => filemtime(UICORE_PATH . '/assets/js/frontend' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-admin' => [
                'src' => UICORE_ASSETS . '/js/admin' . $prefix . '.js',
                'deps' => ['uicore-vendor'],
                'version' => filemtime(UICORE_PATH . '/assets/js/admin' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-grid' => [
                'src' => UICORE_ASSETS . '/js/uicore-grid.js',
                //'deps' => ['jquery'],
                'version' => filemtime(UICORE_PATH . '/assets/js/uicore-grid.js'),
                'in_footer' => true,
            ],
            'ui-e-odometer' => [
                'src' => UICORE_ASSETS . '/js/lib/odometer.js',
                'deps' => ['ui-e-counter'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore_global' => [
                'src' => self::get_global("uicore-global.js"),
                //'deps' => ['jquery'],
                'version' => $version,
                'in_footer' => true,
            ],
            'uicore-admin-menu' => [
                'src' => UICORE_ASSETS . '/js/admin-menu' . $prefix . '.js',
                'deps' => ['jquery', 'uicore-vendor'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore-ai' => [
                'src' => UICORE_ASSETS . '/js/ai' . $prefix . '.js',
                'deps' => ['uicore-vendor'],
                'version' => filemtime(UICORE_PATH . '/assets/js/ai' . $prefix . '.js'),
                'in_footer' => true,
            ],
            'uicore-swatches' => [
                'src' => UICORE_ASSETS . '/js/woocommerce/swatches' . $prefix . '.js',
                //'deps' => ['jquery'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore-product-tabs' => [
                'src' => UICORE_ASSETS . '/js/woocommerce/product-tabs' . $prefix . '.js',
                'deps' => ['jquery'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
            'uicore-admin-swatches' => [
                'src' => UICORE_ASSETS . '/js/admin-swatches' . $prefix . '.js',
                'deps' => ['jquery', 'uicore-vendor'],
                'version' => UICORE_VERSION,
                'in_footer' => true,
            ],
        ];

        return $scripts;
    }

    /**
     * Define Style Array
     *
     * @return array $styles
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_styles()
    {
        $upload_dir = wp_upload_dir();
        $version = Helper::get_option('settings_version', UICORE_VERSION);

        $theme_styles = [
            'uicore-frontend' => [
                'src' => UICORE_ASSETS . '/css/frontend.css',
            ],
            'uicore-admin' => [
                'src' => UICORE_ASSETS . '/css/admin.css',
            ],
            'uicore-admin-menu' => [
                'src' => UICORE_ASSETS . '/css/admin-menu.css',
            ],
            'uicore-admin-icons' => [
                'src' => UICORE_ASSETS . '/fonts/admin-icons.css',
            ],
            'uicore-admin-font' => [
                'src' => 'https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500&display=swap',
            ],
            'uicore-icons' => [
                'src' => UICORE_ASSETS . '/fonts/uicore-icons.css',
            ],
            'uicore_global' => [
                'src' => self::get_global("uicore-global.css"),
                'version' => $version
            ],
            'uicore-blog-st' => [
                'src' => self::get_global("uicore-blog.css"),
                'version' => $version
            ],
            'uicore-portfolio-st' => [
                'src' => self::get_global("uicore-portfolio.css"),
                'version' => $version
            ],
            'uicore_rtl' => [
                'src' => UICORE_ASSETS . '/css/frontend-rtl.css'
            ],
            'uicore-ai' => [
                'src' => UICORE_ASSETS . '/css/ai.css'
            ],
            'uicore-admin-swatches' => [
                'src' => UICORE_ASSETS . '/css/admin-swatches.css'
            ],
        ];

        $woo_styles = [
            'uicore-product-tabs-horizontal' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-horizontal.css',
            ],
            'uicore-product-tabs-vertical' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-vertical.css',
            ],
            'uicore-product-tabs-sections' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-sections.css',
            ],
            'uicore-product-tabs-accordion' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/tabs-accordion.css',
            ],
            'uicore-product-gallery-thumbs' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/gallery-thumbs.css',
            ],
            'uicore-product-gallery-columns' => [
                'src' => UICORE_ASSETS . '/css/woocommerce/gallery-columns.css',
            ],
        ];

        $styles = array_merge($theme_styles, $woo_styles);
        return $styles;
    }

    static function get_global($name,$type='url')
    {
        if($type === 'url'){
            $upload_dir = wp_upload_dir();
            $upload_dir = apply_filters('uicore_global_upload_dir', $upload_dir);
            $value = set_url_scheme($upload_dir['baseurl']."/".$name);
        }
        return $value;
    }
    static function print_custom_font_link( $font=null )
    {
        $fonts = Helper::get_option('customFonts');
        $css = '';
        if(\is_array($fonts)){
            foreach($fonts as $font){
                $css .= self::get_font_face_css($font);
            }
        }
        return $css;
    }
    static function get_font_face_css( $font )
    {
            $css = '';
            $font_display = get_option( 'elementor_font_display','auto');
			foreach ( $font['variants'] as $key => $variant ) {

                $links = $variant['src'];

                //Font Style
                if (strpos($variant['type'], 'italic') !== false) {
                    $font_style = 'italic';
                } else {
                    $font_style = 'normal';
                }
                //Font Weight
                if ((strpos($variant['type'], 'regular') !== false) ||(strpos($variant['type'], 'normal') !== false)) {
                    $font_weight = '400';
                } else {
                    if (strlen(str_replace('italic', '', $variant['type'])) < 2) {
                        $font_weight = 'normal';
                    } else {
                        $font_weight = str_replace('italic', '', $variant['type']);
                    }
                }

				$css  .= ' @font-face { font-family:"' . esc_attr( $font['family'] ) . '";';
				$css .= 'src:';
				$arr  = array();
				if ( $links['woff'] ) {
					$arr[] = 'url("' . esc_url( $links['woff'] ) . '") format(\'woff\')';
				}
				if ( $links['ttf'] ) {
					$arr[] = 'url("' . esc_url( $links['ttf'] ) . '") format(\'truetype\')';
				}
				if ( $links['eot'] ) {
					$arr[] = 'url(' . esc_url( $links['eot'] ) . ") format('opentype')";
				}
				if ( $links['svg'] ) {
					$arr[] = 'url(' . esc_url( $links['svg'] ) . '#' . esc_attr( strtolower( str_replace( ' ', '_', $font['family'] ) ) ) . ") format('svg')";
				}
				$css .= join( ', ', $arr );
				$css .= ';';
				$css .= 'font-display:'.$font_display.';font-style:'.$font_style.';font-weight:'.$font_weight.';';
				$css .= '}';
			}

			return $css;

    }

    static function print_typekit_font_link( )
    {
        $typekit =  Helper::get_option('typekit', false );
        $typekit_id = isset($typekit['id']) ? $typekit['id'] : null;

        $kit_url = sprintf('https://use.typekit.net/%s.css', $typekit_id);
        echo '<link rel="stylesheet" type="text/css" href="' . $kit_url . '">';
    }
    	/**
	 * Filter style and script tag for snippet handles to load async
	 */
	public static function filter_snippet_asset_tag($tag, $handle)
	{
		$snippet_style_handles = ['uicore_global'];
		$snippet_script_handles = ['uicore_global'];
        $critical_inline_css = \get_option('uicore_global_critical_css', false);
		// Async CSS for snippet styles
		if (in_array($handle, $snippet_style_handles) && !empty($critical_inline_css)) {
			if (strpos($tag, "rel='stylesheet'") !== false) {
				$tag = str_replace(
					"rel='stylesheet'",
					"rel='stylesheet' media='print' onload=\"this.onload=null;this.media='all'\"",
					$tag
				);
			}
		}

		// Async JS
		if (in_array($handle, $snippet_script_handles)) {
			// Only add defer  if not already present
			if (strpos($tag, 'defer') === false) {
				// Insert async before closing '>' of <script ...>
				$tag = preg_replace('/<script(.*?)>/i', '<script$1 defer>', $tag);
			}
		}
		return $tag;
	}

}

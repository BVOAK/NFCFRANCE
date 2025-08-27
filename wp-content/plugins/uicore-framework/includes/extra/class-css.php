<?php
namespace UiCore;

defined('ABSPATH') || exit();

/**
 *  CSS Util
 */
class CSS
{
    public $settings;
    private $br;
    public $global_animations;
    public $files;
    public $css;
    public $inline_critical_type; //global (if added in global file or global_critical)

    /**
     * Apply the filter to get the class (disabled by default)
     *
     * @param string $item
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function __construct($settings)
    {
        $this->settings = self::migrate($settings);
        if (class_exists('Elementor')) {
            $br_points = \Elementor\Core\Responsive\Responsive::get_breakpoints();
        } else {
            $br_points = [
                'sm' => '480',
                'md' => '767',
                'lg' => '1024',
            ];
        }
        $this->br = $br_points;
        $this->global_animations = ($settings['animations'] === 'true' && $settings['performance_animations'] === 'true');
        $this->inline_critical_type = $settings['performance_inline_critical'] === 'true' ? 'global_critical' : 'global';

        $this->get_the_css_parts();
        $this->get_css_from_settings();
        $this->combine_css();
    }

    /**
     * Add css files parts
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function get_the_css_parts()
    {

        //Blog
        $this->files['blog'][] = UICORE_PATH . '/assets/css/blog.css';
        $this->files['blog'][] = UICORE_PATH . '/assets/css/blog/grid.css';
        if($this->settings['blog_item_style'] != 'simple'){
            $type = str_replace(' ', '-', $this->settings['blog_item_style'] );
            $this->files['blog'][] = UICORE_PATH . '/assets/css/blog/item-style-'.$type.'.css';
        }
        //simple pagetitle breacrumb style fallback
        if($this->settings['pagetitle'] === 'false' && $this->settings['blogs_title'] === 'simple page title' && $this->settings['blogs_breadcrumb'] === 'true'){
            $this->files[$this->inline_critical_type][] = UICORE_PATH . '/assets/css/global/page-title.css';
        }

        //Portfolio
        $this->files['portfolio'][] = UICORE_PATH . '/assets/css/portfolio.css';
        $this->files['portfolio'][] = UICORE_PATH . '/assets/css/portfolio/grid.css';


        $this->files[$this->inline_critical_type][] = UICORE_PATH . '/assets/css/critical-frontend.css';
        $this->files['global'][] = UICORE_PATH . '/assets/css/frontend.css';

        //Top Banner
        if($this->settings['header_top'] === 'true'){
            $this->files[$this->inline_critical_type][] = UICORE_PATH . '/assets/css/global/top-banner.css';
        }
        //Header
        if($this->settings['header'] === 'true'){
            $this->files[$this->inline_critical_type][] = UICORE_PATH . '/assets/css/global/header.css';
        }
        //Page title
        if($this->settings['pagetitle'] === 'true'){
            $this->files[$this->inline_critical_type][] = UICORE_PATH . '/assets/css/global/page-title.css';
        }
        //Footer
        if($this->settings['footer'] === 'true'){
            $this->files['global'][] = UICORE_PATH . '/assets/css/global/footer.css';
        }
        //Copyrights
        if($this->settings['copyrights'] === 'true'){
            $this->files['global'][] = UICORE_PATH . '/assets/css/global/copyrights.css';
        }
        //Animations
        if($this->settings['performance_animations'] === 'true'){

            // this is our general animations file
            $this->files['global'][] = UICORE_PATH . '/assets/css/global/animations.css';

        }
        //sidebars
        if($this->settings['performance_widgets'] === 'true'){
            $this->files['global'][] = UICORE_PATH . '/assets/css/global/sidebars.css';
        }

        //Woocommerce
        if(class_exists('WooCommerce')){
            $this->files['global'][] = UICORE_PATH . '/assets/css/woocommerce.css';
        }

        if(class_exists('\UiCore\Elementor\Core')){
            $this->files['global'][] = UICORE_PATH . '/assets/css/global/elementor.css';
            $upload_dir = wp_upload_dir();
            $kit_id = get_option('elementor_active_kit');
            $this->files['global'][] = $upload_dir['basedir'].'/elementor/css/post-'.$kit_id.'.css';
        }

        //TODO: add a performance option to disable this
		$this->files['global'][] = UICORE_PATH . '/assets/fonts/uicore-icons.css';



        /*
        * add some files to global based on settings or
        * just becase you want to improve the performance and add your files ass a developer
        */
        $this->files['global'] = apply_filters('uicore_css_global_files', $this->files['global'], $this->settings);
        $this->files[$this->inline_critical_type] = apply_filters('uicore_css_global_critical_files', $this->files[$this->inline_critical_type], $this->settings);

    }

    /**
     * Get css content for all category [Theme Options]
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function get_css_from_settings()
    {
        //global includes WooCommerce
        $this->css['global'] = $this->global_css();
        $this->css['blog'] = $this->blog_css();
        $this->css['portfolio'] = $this->portfolio_css();
        if($this->inline_critical_type === 'global'){
            $this->css[$this->inline_critical_type] .= $this->global_critical_css();
        }else{
            $this->css[$this->inline_critical_type] = $this->global_critical_css();
        }
    }

    /**
     * Combine and minify files and Theme Options css
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 6.0.0
     */
    function combine_css()
    {
        $files_ = \apply_filters('uicore-css-files', $this->files);
        foreach($files_ as $type=>$files){

            do_action('uicore_before_generate_'.$type.'_css', $files);

            $available_files = [];
            foreach($files as $file){
                if(@is_file($file)){
                   $available_files[] = $file;
                }
            }

            $minifier = new \MatthiasMullie\Minify\CustomCSS();
			// $minifier = new \MatthiasMullie\Minify\CSS();

            if(count($available_files)){
                $minifier->addFile($available_files);
            }

            if(array_key_exists($type,$this->css)){
                $minifier->add($this->css[$type]);
            }

            $upload_dir = wp_upload_dir();
            $upload_dir = apply_filters('uicore_global_upload_dir', $upload_dir);
            $file = $upload_dir['basedir']."/uicore-".$type.'.css';

            $content = $minifier->minify();
            // https://your-website.com/wp-content/plugins/uicore-framework/assets/fonts/
            $icons_path = \apply_filters('uicore-icons-path', \UICORE_ASSETS . '/fonts/');
            $content = str_replace('uicore-icons.' , $icons_path . 'uicore-icons.' ,$content);
            $content = str_replace('99999999999px' , $this->settings['mobile_breakpoint'].'px',$content);
            $content = \apply_filters('uicore_css_'.$type.'_code',$content,$this);
            if($type === 'global_critical'){
                update_option('uicore_global_critical_css', $content);
            }else{
                $this->save($content,$file);
            }

            do_action('uicore_after_generate_'.$type.'_css', $file,$content);
        }

        if($this->inline_critical_type === 'global'){
            \delete_option('uicore_global_critical_css');
        }

    }

    function save($data,$path)
    {
        $fp = fopen($path, 'w');
        fwrite($fp, $data);
        fclose($fp);
    }

    /**
     * Get Global Theme Options css
     *
     * @return string css markup
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function global_critical_css()
    {
        $global_animations = $this->global_animations;
        $json_settings = $this->settings;
        $br_points = $this->br;
        $translateEase = 'cubic-bezier(0.22, 1, 0.36, 1)';
        $opacityEase = 'cubic-bezier(0, 0, 0.2, 1)';
        $css = null;

        //Top Banner
        if($json_settings['header_top'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/top-banner-css.php';
        }
        //Header
        if($json_settings['header'] === 'true'){
          include UICORE_INCLUDES .'/extra/style/header-css.php';

            //Drawer
            if($json_settings['header_side_drawer'] === 'true'){
                include UICORE_INCLUDES .'/extra/style/drawer-css.php';
            }
        }
        //Page title
        if($json_settings['pagetitle'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/page-title-css.php';
        }

        //typo
        include UICORE_INCLUDES .'/extra/style/typography-css.php';


        //Global
        include UICORE_INCLUDES .'/extra/style/global-css.php';


        return \apply_filters('uicore_css_critical_global_code_string',$css,$this);

    }
    /**
     * Get Global Theme Options css
     *
     * @return string css markup
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function global_css()
    {
        $global_animations = $this->global_animations;
        $json_settings = $this->settings;
        $br_points = $this->br;
        $translateEase = 'cubic-bezier(0.22, 1, 0.36, 1)';
        $opacityEase = 'cubic-bezier(0, 0, 0.2, 1)';
        $css = null;

        //Header
        if($json_settings['header'] === 'true'){
          include UICORE_INCLUDES .'/extra/style/non-critical-header-css.php';
        }

        //Footer
        if($json_settings['footer'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/footer-css.php';
        }
        //Copyrights
        if($json_settings['copyrights'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/copyrights-css.php';
        }

        //Sidebar
        if($json_settings['performance_widgets'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/sidebar-css.php';
        }

        //Animations
        if($json_settings['animations'] === 'true' && $json_settings['performance_animations'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/animations-css.php';
        }
        //force disable all animations
        if($json_settings['performance_animations'] === 'false'){
            $css .= '
            .animated {
                animation: unset !important;
            }
            .elementor-invisible {
                visibility: visible;
            }
            ';
        }

        if($json_settings['gen_cursor'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/cursor-css.php';
        }


        if(class_exists('\UiCore\Elementor\Core')){
            $btn_css_selector = str_replace('{{WRAPPER}}','', Elementor\Core::get_buttons_class());
            include UICORE_INCLUDES .'/extra/style/buttons-css.php';
        }

        if(class_exists('WooCommerce')){
            include UICORE_INCLUDES .'/extra/style/woo-css.php';

            //single product
            if($json_settings['woos_sticky_add_to_cart'] === 'true'){
                include UICORE_INCLUDES .'/extra/style/woo-sticky-add_to_cart-css.php';
                // hide the sticky add to cart button in the editor
                if (class_exists('\Uicore\Elementor\Core')) {
                    $css .= '
                    .elementor-editor-active .uicore-sticky-add-to-cart{
                        display: none;
                    }
                    ';
                }
            }
            //add drawer styles for mobile filters if sidebar is sett
            if($json_settings['woocommerce_sidebar_id'] !== 'none'){
                include UICORE_INCLUDES .'/extra/style/drawer-css.php';
            }
        }

        if(function_exists('tutor_lms')){
            include UICORE_INCLUDES .'/extra/style/tutor-lms-css.php';
        }
        if(defined( 'AWSM_JOBS_PLUGIN_VERSION' )){
            include UICORE_INCLUDES .'/extra/style/wp-job-css.php';
        }
        //Custom CSS
        $css .= $json_settings['customcss'];

        return \apply_filters('uicore_css_global_code_string',$css,$this);

    }

    /**
     * Get Blog Theme Options css
     *
     * @return string css markup
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function blog_css()
    {
        $global_animations = $this->global_animations;
        $json_settings = $this->settings;
        $br_points = $this->br;
        $css = null;

        include UICORE_INCLUDES .'/extra/style/blog-css.php';

        if($json_settings['blogs_author_box'] === 'true'){
            include UICORE_INCLUDES .'/extra/style/blog-author_box-css.php';
        }
        if($json_settings['blogs_related'] === 'true' && $json_settings['blogs_related_style'] === 'list' ){
            include UICORE_INCLUDES .'/extra/style/blog-related_posts-css.php';
        }
        return $css;

    }

    /**
     * Get Portfolio Theme Options css
     *
     * @return string css markup
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function portfolio_css()
    {
        $global_animations = $this->global_animations;
        $json_settings = $this->settings;
        $br_points = $this->br;
        $css = null;

        include UICORE_INCLUDES .'/extra/style/portfolio-css.php';
        return $css;

    }

    /**
     * Helper function used inside css.php files
     *
     * @param mixed $condition
     * @param mixed $return
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function iff($condition, $return)
    {
        if(strlen($condition) != 0 ){
            return $return;
        }else{
            return null;
        }
    }

    /**
     * Helper function used inside css.php files
     *
     * @param mixed $fam
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    static function fam($fam)
    {

        switch ($fam) {
            case "Primary":
                $font = 'var(--uicore-primary-font-family)';
                break;
            case "Secondary":
                $font = 'var(--uicore-secondary-font-family)';
                break;
            case "Text":
                $font = 'var(--uicore-text-font-family)';
                break;
            case "Accent":
                $font = 'var(--uicore-accent-font-family)';
                break;
            default :
                $font = '"'.$fam.'"';
        }
        return $font;

    }

    /**
     * Helper function used inside css.php files
     *
     * @param mixed $for
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    static function st($for)
    {
        if (strpos($for['st'], 'italic') !== false) {
            return 'italic';
        } else {
            return 'normal';
        }
    }

    /**
     * Helper function used inside css.php files
     *
     * @param mixed $for
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    static function wt($for)
    {
        if ((strpos($for['st'], 'regular') !== false) ||(strpos($for['st'], 'normal') !== false)) {
            return 'normal';
        } else {
            if (strlen(str_replace('italic', '', $for['st'])) < 2) {
                return 'normal';
            } else {
                return str_replace('italic', '', $for['st']);
            }
        }
    }

    /**
     * Helper function used inside css.php files
     *
     * @param mixed $color
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    static function color($color)
    {


        //Color + Blur Migrate support
        if(!is_string($color) && (isset($color['type']) || isset($color['blur']))){
            $color = $color['color'];
        }

        if ($color == 'Primary') {
            $color = 'var(--uicore-primary-color)';
        } else if ($color == 'Secondary') {
            $color = 'var(--uicore-secondary-color)';
        } else if ($color == 'Accent') {
            $color = 'var(--uicore-accent-color)';
        } else if ($color == 'Headline') {
            $color = 'var(--uicore-headline-color)';
        } else if ($color == 'Body') {
            $color = 'var(--uicore-body-color)';
        } else if ($color == 'Dark Neutral') {
            $color = 'var(--uicore-dark-color)';
        } else if ($color == 'Light Neutral') {
            $color = 'var(--uicore-light-color)';
        } else if ($color == 'White') {
            $color = 'var(--uicore-white-color)';
        }
        return $color;
    }

    function generateBorderColor($color)
    {
        //Color + Blur Migrate support
        if(isset($color['type']) && !in_array($color['type'],['gradient','image', 'solid'])){
            $color = $color['type'];
        }
        if(isset($color['type']) && $color['type'] == "solid"){
            $color = $color['solid'];
        }
        if(isset($color['color'])) {
            $color = $color['color'];
        }
        if(isset($color['type']) && $color['type'] == 'gradient') {
            $color = $color['gradient']['color1'];
        }
        if(isset($color['type']) && $color['type'] == 'image') {
            $color = $color['solid'];
        }
        if ($color == 'Primary') {
            $color = $this->settings['pColor'];
        } else if ($color == 'Secondary') {
            $color = $this->settings['sColor'];
        } else if ($color == 'Accent') {
            $color = $this->settings['aColor'];
        } else if ($color == 'Headline') {
            $color = $this->settings['hColor'];
        } else if ($color == 'Body') {
            $color = $this->settings['bColor'];
        } else if ($color == 'Dark Neutral') {
            $color = $this->settings['dColor'];
        } else if ($color == 'Light Neutral') {
            $color = $this->settings['lColor'];
        } else if ($color == 'White') {
            $color = $this->settings['wColor'];
        }

        // Extract the RGB values from the color string
        list($r, $g, $b) = sscanf($color, "#%2x%2x%2x");

        // Calculate the color's brightness
        $brightness = ($r * 299 + $g * 587 + $b * 114) / 1000;
        // Adjust the color based on its brightness
        if ($brightness > 200) { // Almost white
            $r -= 20;
            $g -= 20;
            $b -= 20;
        } elseif ($brightness < 55) { // Almost dark
            $r += 20;
            $g += 20;
            $b += 20;
        }

        // Make sure the RGB values are within the valid range (0-255)
        $r = max(0, min(255, $r));
        $g = max(0, min(255, $g));
        $b = max(0, min(255, $b));

        // Generate the adjusted color string
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }


    function generateRGB($color)
    {
         //Color + Blur Migrate support
        if(isset($color['type']) && !in_array($color['type'],['gradient','image', 'solid'])){
            $color = $color['type'];
        }
        if(isset($color['type']) && $color['type'] == "solid"){
            $color = $color['solid'];
        }
        if(isset($color['color'])) {
            $color = $color['color'];
        }
        if(isset($color['type']) && $color['type'] == 'gradient') {
            $color = $color['gradient']['color1'];
        }
        if(isset($color['type']) && $color['type'] == 'image') {
            $color = $color['solid'];
        }
        if ($color == 'Primary') {
            $color = $this->settings['pColor'];
        } else if ($color == 'Secondary') {
            $color = $this->settings['sColor'];
        } else if ($color == 'Accent') {
            $color = $this->settings['aColor'];
        } else if ($color == 'Headline') {
            $color = $this->settings['hColor'];
        } else if ($color == 'Body') {
            $color = $this->settings['bColor'];
        } else if ($color == 'Dark Neutral') {
            $color = $this->settings['dColor'];
        } else if ($color == 'Light Neutral') {
            $color = $this->settings['lColor'];
        } else if ($color == 'White') {
            $color = $this->settings['wColor'];
        }

        //extract rgb and return the value as a astring
        list($r, $g, $b) = array_map(
            function ($c) {
              return hexdec(str_pad($c, 2, $c));
            },
            str_split(ltrim($color, '#'), strlen($color) > 4 ? 2 : 1)
        );
        return $r.','.$g.','.$b;

    }

    /**
     * Helper function used inside css.php files
     *
     * @param mixed $prop
     * @param mixed $class
     * @param mixed $media_query
     * @param mixed $responsive
     * @param mixed $br
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function background($prop, $class, $media_query = false, $responsive = false, $br = null)
    {
        $css = '';
        if($media_query){
                $css .= "\n".'@media only screen and ('.$media_query.') { '."\n";
        }
        if ($prop['type'] == 'solid') {
            $css .= $class .' {
                background-color: ' .
                $prop['solid'] .
                ';
            ';

            if(isset($prop['blur']) && $prop['blur'] === 'true'){
                $css .='
                    backdrop-filter: blur(10px);
                -webkit-backdrop-filter: blur(10px);';
            }

            $css .= '}';
        }elseif ($prop['type'] == 'gradient') {
            $css .= $class .' {
                background-image: linear-gradient(' .
                $prop['gradient']['angle'] .
                'deg,' .
                $prop['gradient']['color1'] .
                ', ' .
                $prop['gradient']['color2'] .
                ');
            } ';
        }elseif ($prop['type'] == 'image') {
            $css .= $class .' {
                background: url(' .
                $prop['image']['url'] .
                ') ' .
                $prop['image']['position']['d'] .
                '/' .
                $prop['image']['size']['d'] .
                ' ' .
                $prop['image']['repeat'] .
                ' ' .
                $prop['image']['attachment'] .
                ' ' .
                $this->color($prop['solid']) .
                ';
            } ';
            if($responsive){
                $css .= '
                @media (max-width: ' . $br['lg'] . 'px) {'
                    . $class .' {
                        background: url(' .
                        $prop['image']['url'] .
                        ') ' .
                        $prop['image']['position']['t'] .
                        '/' .
                        $prop['image']['size']['t'] .
                        ' ' .
                        $prop['image']['repeat'] .
                        ' ' .
                        $prop['image']['attachment'] .
                        ' ' .
                        $this->color($prop['solid']) .
                        ';
                    }

                }
                @media (max-width: ' . $br['md'] . 'px) {
                    '
                    . $class .' {
                        background: url(' .
                        $prop['image']['url'] .
                        ') ' .
                        $prop['image']['position']['m'] .
                        '/' .
                        $prop['image']['size']['m'] .
                        ' ' .
                        $prop['image']['repeat'] .
                        ' ' .
                        $prop['image']['attachment'] .
                        ' ' .
                        $this->color($prop['solid']) .
                        ';
                    }
                }
                ';
            }

        }else{
            $css .= $class . ' {
                background: ' . $this->color($prop['type']) . ';
            }';
        }

        if($media_query){
            $css .= "\n".' }'."\n";
        }
        return $css;
    }

    function shadow($settings, $class)
    {
        //check if is array
        if(!is_array($settings) || (is_array($settings) && count($settings) === 0)){
            return;
        }

        $shadow = [];
        foreach($settings as $key=>$value){
            $shadow[] = ($value['type'] == 'inside' ? 'inset ' : '') .  $value['h_shadow'] . 'px ' . $value['v_shadow'] . 'px ' . $value['blur'] . 'px ' . $value['spread'] . 'px ' . $this->color($value['color']);
        }

        return $class . ' {
            box-shadow: ' . implode(',', $shadow) . ';
        }';

    }

    /**
     * Helper function used inside css.php files
     *
     * @param mixed $type
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function grid_animation($type)
    {

        if(!$this->global_animations){
            return;
        }
        $css = null;
        $json_settings = $this->settings;
        $translateEase = 'cubic-bezier(0.22, 1, 0.36, 1)';
        $opacityEase = 'cubic-bezier(0, 0, 0.2, 1)';

        if($json_settings['animations_'.$type.''] != 'none'){

            //made dellay dynamic so it will work good with grid element different number of columns
            $css .= '
            .uicore-'.$type.'-grid.animate-4 .uicore-animate:nth-child(4n-2){
                animation-delay: '. (int)$json_settings['animations_'.$type.'_delay_child'] .';
            }
            .uicore-'.$type.'-grid.animate-4 .uicore-animate:nth-child(4n-1){
                animation-delay: '. ((int)$json_settings['animations_'.$type.'_delay_child'] * 2) .'ms;
            }
            .uicore-'.$type.'-grid.animate-4 .uicore-animate:nth-child(4n){
                animation-delay: '. ((int)$json_settings['animations_'.$type.'_delay_child'] * 3) .'ms;
            }
            .uicore-'.$type.'-grid.animate-3 .uicore-animate:nth-child(3n-1){
                animation-delay: '. ((int)$json_settings['animations_'.$type.'_delay_child']) .'ms;
            }
            .uicore-'.$type.'-grid.animate-3 .uicore-animate:nth-child(3n){
                animation-delay: '. ((int)$json_settings['animations_'.$type.'_delay_child'] * 2) .'ms;
            }
            .uicore-'.$type.'-grid.animate-2 .uicore-animate:nth-child(2n){
                animation-delay: '. ((int)$json_settings['animations_'.$type.'_delay_child']) .'ms;
            }
			';

			if($type === 'blog') {
				$css .= '
				.ui-simple-creative .uicore-animate,
				';
			}
			$css .= '
            .uicore-'.$type.'-animation .uicore-animate{';

            if($json_settings['animations_'.$type.''] === 'fade'){
                $css .= '
                    opacity: 0;
                    animation-fill-mode: forwards;
                    animation-duration: 1s;
                    animation-name: uicoreFadeIn;
                    animation-play-state: paused;
                    animation-timing-function: '.$opacityEase.';
                ';
            }
            if($json_settings['animations_'.$type.''] === 'fade down'){
                $css .= '
                    opacity: 0;
                    animation-fill-mode: forwards;
                    animation-duration: 1.8s;
                    animation-name: uicoreFadeInDown, uicoreFadeIn;
                    animation-play-state: paused;
                    animation-timing-function: '.$translateEase.','. $opacityEase.';
                ';
            }
            if($json_settings['animations_'.$type.''] === 'fade up'){
                $css .= '
                    opacity: 0;
                    animation-fill-mode: forwards;
                    animation-duration: 1.8s;
                    animation-name: uicoreFadeInUp, uicoreFadeIn;
                    animation-play-state: paused;
                    animation-timing-function: '.$translateEase.','. $opacityEase.';
                ';
            }
            if( $json_settings['animations_'.$type.'_duration'] === 'fast'){
                $css .= '
                    animation-duration: 1.3s;
                ';
            }
            if( $json_settings['animations_footer_duration'] === 'slow'){
                $css .= '
                    animation-duration: 2.7s;
                ';
            }
            $css .= '}';
        }

        return $css;
    }

    /**
     * Helper function to get skin stylesheet
     *
     * @param mixed $skin
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function add_skin($skin)
    {
        $settings = $this->settings['skin_'.$skin];
        $required = false;

        if(isset($settings['conditions'])){
            foreach($settings['conditions'] as $condition=>$value){
                if($this->settings[$condition] === $value){
                    $required = true;
                }
            }
        }else{
            $required = true;
        }
        if($settings['value'] != 'clean' && $required){
            $folder = ($settings['value'] === 'default') ? str_replace(' ', '_', strtolower(UICORE_THEME_NAME)) : $settings['value'];
            $skin = isset($settings['dynamic']) ? $skin . '_' . str_replace(' ', '_', $this->settings[$settings['dynamic']]) : $skin;
            $type = isset($settings['type']) ? $settings['type'] : 'global';
            $this->files[$type][] = get_template_directory() . '/assets/skin/' . $folder .'/'. $skin .'.css';
        }

    }


    static function migrate($settings)
    {
        if(isset($settings['submenu_color']['m'])){
            $main = $settings['submenu_color']['m'];
            $hover = $settings['submenu_color']['h'];
            $settings['submenu_color'] = $settings['menu_typo'];
            $settings['submenu_color']['c'] = $main;
            $settings['submenu_color']['ch'] = $hover;
        }

        return $settings;
    }

    
    static function generate_typography_css($element, $settings)
    {
        if (!isset($settings[$element])) {
            return '';
        }
        $value = $settings[$element];
        $css  = '--uicore-typography--' . $element . '-f: ' . self::fam($value['f'], $settings) . ';' . "\n";
        $css .= '--uicore-typography--' . $element . '-w: ' . self::wt($value) . ';' . "\n";
        $css .= self::get_size_and_unit($value['h']['d'], '--uicore-typography--' . $element . '-h') . "\n";
        $css .= self::get_size_and_unit($value['ls']['d'], '--uicore-typography--' . $element . '-ls') . "\n";
        $css .= '--uicore-typography--' . $element . '-t: ' . $value['t'] . ';' . "\n";
        $css .= '--uicore-typography--' . $element . '-st: ' . self::st($value) . ';' . "\n";
        $css .= '--uicore-typography--' . $element . '-c: ' . self::color($value['c']) . ';' . "\n";
        $css .= self::get_size_and_unit($value['s']['d'], '--uicore-typography--' . $element . '-s') . "\n";

        return $css;
    }

    static function get_size_and_unit($size, $prop)
    {
        if ($size['value'] === '') {
            //if the value is empty, return an empty string
            return null;
        }
        if (isset($size['unit']) && $size['unit'] == 'ct') {
            //custom unit, return only the value
            return  $prop . ':'. $size['value'].';';
        }
        return $prop . ':'. $size['value'] . $size['unit'] . ';';
    }



}

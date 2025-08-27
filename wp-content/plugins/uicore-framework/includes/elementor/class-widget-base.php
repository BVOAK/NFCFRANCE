<?php
namespace UiCore\Elementor;

use Elementor\Widget_Base;

defined('ABSPATH') || exit();

/**
 * Elementor's widgets base that extends Elementor `Widget_Base`
 *
 * @since 6.0.0
 * @abstract
 */
abstract class TB_Widget_Base extends Widget_Base {

    /**
     * Get widget categories.
     *
     * custom_condition need to always return true if $this->is_edit_mode() is true
     * @return object ['assets-name' => ['condition' => ['key' => 'value'] , 'deps' => ['global-handler-name','other-handler-name'], 'external' => true]]
     */
    public abstract function get_styles();

    /**
     * Get widget categories.
     *
     * custom_condition need to always return true if $this->is_edit_mode() is true
     * @return object ['assets-name' => ['condition' => ['key' => 'value'] ,'custom_condition' => true , 'deps' => ['global-handler-name','other-handler-name'], 'external' => true]]
     */
    public abstract function get_scripts();

    public function get_style_depends()
    {
        $assets = $this->get_styles();
        $final_list = $this->parse_asset_list($assets, 'style');

        return $final_list;
    }

    public function get_script_depends()
    {
        $assets = $this->get_scripts();
        $final_list = $this->parse_asset_list($assets, 'script');

        return $final_list;
    }

    private function parse_asset_list($list, $type = 'style'){
        $final_list = [];

        foreach ($list as $key => $value) {
            $deps       = (isset($value) && isset($value['deps'])) ? $value['deps'] : [];
            $external   = (isset($value) && isset($value['external'])) ? $value['external'] : false;
            if(!$external && $type !== 'style'){
                //just to make sure that we have the manifest loaded
                //eg: all compiled scripts are loaded from the manifest
                //the upgrade to node 20+ removed manifest (test if is still working)
                // $deps[] = 'uicore-manifest';
            }
            $name       = $this->get_asset_name($key,$value);

            //if name is not empty then we need to add it to the list
            if($name){
                $method_name  = "register_widget_$type";
                $final_list[] = self::$method_name($name, $deps, $external);
            }
        }

        return $final_list;
    }

    // TODO: update with $prefix for min versions
    static function register_widget_style($name, $deps=[], $external=false)
    {
        $handle = (!$external ? 'uicore-' : '' ). $name;
         //if name contains / then we need to set a custom path
         if(strpos($name,'/') !== false){
            $path ='';
        }else{
            $path = 'elements/';
        }
        wp_register_style($handle, UICORE_ASSETS . '/css/' . $path . $name . '.css', $deps, UICORE_VERSION);
        return $handle;
    }
    static function register_widget_script($name,$deps=[],$external=false)
    {
        $handle = (!$external ? 'uicore-' : '' ). $name;
        //if name contains / then we need to set a custom path
        if(strpos($name,'/') !== false){
            $path ='';
        }else{
            $path = 'elements/';
        }
        wp_register_script($handle, UICORE_ASSETS . '/js/' . $path . $name . '.js', $deps, UICORE_VERSION, true);
        return $handle;
    }

    private function get_asset_name($key,$value){
        //check the condition/s
        if (\is_array($value) && !empty($value)) {

            // Custom conditions
            if( isset($value['custom_conditions']) ){

                $custom_result  = false;
                $control_result = false;
                $conditions     = $value['custom_conditions'];

                if( isset($conditions['direct_condition']) && $conditions['direct_condition'] ) {
                    $custom_result = true;
                }
                if( isset($conditions['controls_condition']) && ($this->is_edit_mode() || $this->is_control_visible($value, $this->get_settings())) ) {
                    $control_result = true;
                }

                if( isset($conditions['relation']) && $conditions['relation'] === 'AND') {
                    $result = $custom_result && $control_result;
                } else {
                    $result = $custom_result || $control_result;
                }

                return $result ? $key : '';
            }

            //check if the condition is true using elementor's function (always return true if we are in edit mode)
            if($this->is_edit_mode() || $this->is_control_visible($value,$this->get_settings())){
                return $key;
            }else{
                return '';
            }
        }
        // if list is only declaring the assets without any condition then we need to return the key
        return $value;

    }

    /**
     * Retrieves the post ID, in editor, from the Select Post control.
     *
     * @return string The post ID.
     */
    public function get_editor_post_id() {
        return $this->get_settings_for_display('post_id');
    }

    /**
     * Get the product data.
     *
     * @author Lucas Marini <lucas@uicore.co>
     *
     * @return object|bool Product data or false if not found.
     */
    public function get_product_data()
    {
        global $product;

        if ( !is_object($product) ) {
            // If edit mode, get the product id from the product preview control. Get the current item id otherwise.
            $product_id = $this->is_edit_mode() ? $this->get_editor_post_id() : get_the_ID();
            $product = wc_get_product($product_id);

            if ( !is_object($product) ) {
                return false;
            }
        }

        return $product;
    }

    /**
     * Returns a list of posts to be used in a Elementor Select Control field
     *
     * @param string $post_type The post type to be returned.
     * @param int $post_qty The number of posts to be returned.
     * @param bool $random_default If true, removes the `Select {post type}` option so it can't be randomly selected.
     *
     * @return array List of posts with ID as `key` and post title as `value`.
     */
    public function get_posts_list($post_type, $post_qty, $random_default) {


        $list  = [];
        $posts = get_posts([
            'numberposts' => $post_qty,
            'post_type' => $post_type,
        ]);

        // First option from list should be empty
        if($random_default === false){
            $list = ['' => esc_html__('Select ' . $post_type, 'uicore-framework')];
        }

        foreach ($posts as $data) {
            $list[esc_html($data->ID)] = esc_attr($data->post_title);
        }

        return $list;
    }

    /**
     * Register a Select control with product options for preview purposes.
     *
     * @param object $post_type The type of posts to be returned. Default is 'post'.
     * @param int $post_qty The number of posts to be returned. Default is `20`.
     * @param bool $randon_default If false, removes the `Select {post type}` default option and returns a random one as default value. Default is `true`.
     *
     * @return void Register the control and offers the choosen product ID in `product_id` key.
     */
    public function register_post_list($post_type = 'post', $post_qty = 20, $randon_default = true) {
        $options = $this->get_posts_list($post_type, $post_qty, $randon_default);
        if(empty($options)){
            return;
        }
        $default = $randon_default ? array_rand($options) : $options[0];

        $this->add_control(
            'post_id',
            [
                'label' => sprintf(__( 'Preview %s', 'uicore-framework' ), $post_type),
                'type' => \Elementor\Controls_Manager::SELECT,
                'default' => $default,
                'options' => $options,
                'separator' => 'after',
                'description' => sprintf(
                    __('Has no impact on frontend. Returns a %s here in the editor for preview and editing purposes.', 'uicore-framework'),
                    $post_type
                ),
            ]
        );
    }

    /**
     * Prints the woocommerce div wrappers, wich are not present in the editor context, and are used by several css classes to style different woocommerce components.
     *
     * @param bool $closer If true, return the </div> closures.
     * @param string $woo_classes Adds extra classes to the `.woocommerce` div. Default is empty.
     */
    public function render_woo_wrapper($closer = false, string $woo_classes = '') {

        if($closer) {
            ?> </div> </div> <?php
            return;
        }

        ?>
            <div class="woocommerce <?php echo esc_attr($woo_classes);?>">
            <div class="product uicore-tb-product">
        <?php
    }

    /**
     * Verifies if Woocommerce is available. If is not, prints a warning and returns false.
     *
     * @param bool $register_control If true, registers a warning control instead of printing a message.
     *
     * @since [currentVersion]
     * @return bool True if Woocommerce is NOT available, false otherwise.
     */
    protected function no_woo_fallback($register_control = false) {

        if( !class_exists('WooCommerce') ){

            $message = __('Please enable WooCommerce to use this widget.', 'uicore-framework');

            if($register_control){

                $this->start_controls_section(
                    'section_fallback',
                    [
                        'label' => esc_html__('Content', 'uicore-elements'),
                    ]
                );
                    $this->add_control(
                        'woocommerce_warning',
                        [
                            'type' => \Elementor\Controls_Manager::ALERT,
                            'alert_type' => 'warning',
                            'content' => $message,
                        ]
                    );
                $this->end_controls_section();

            } else if($this->is_edit_mode()){
                echo esc_html($message);
            }

            // Triggers the parent function abortion
            return true;
        }

        return false;
    }

    /**
     * Return a list of active breakpoints in Elementor editor, with the breakpoints size values and labels.
     */
    public function return_breakpoints_options(){

        $options = [
            '0' => esc_html__( 'None', 'uicore-framework' ),
        ];

        $excluded_breakpoints = [
            'laptop',
            'tablet_extra',
            'widescreen',
        ];

        $breakpoints = \Elementor\Plugin::$instance->breakpoints->get_active_breakpoints();

        foreach ( $breakpoints as $key => $instance ) {
            // Exclude the larger breakpoints from the dropdown selector.
            if ( in_array( $key, $excluded_breakpoints, true ) ) {
                continue;
            }

            $options[ $instance->get_value() ] = $instance->get_label();
        }
    }

    protected function is_edit_mode() {
        $elementor_instance = \Elementor\Plugin::instance();
        if ( $elementor_instance->preview->is_preview_mode() || $elementor_instance->editor->is_edit_mode() ) {
            return true;
        }

        return false;
    }

    /**
     * Sanitizes text strings, but allowing some html tags usefull for styling and manipulating texts.
     *
     * @param string $content The content to be sanitized
     * @return string The sanitized string content.
     */
    public function esc_string($content) {

        $allowed_tags = [
            'strong' => array(),
            'em' => array(),
            'b' => array(),
            'i' => array(),
            'u' => array(),
            's' => array(),
            'sub' => array(),
            'sup' => array(),
            'span' => array(),
            'br' => array()
        ];

        return wp_kses( $content, $allowed_tags );
    }
}
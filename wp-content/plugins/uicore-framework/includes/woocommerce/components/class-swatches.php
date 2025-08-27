<?php
namespace UiCore\WooCommerce;
use UiCore\Helper;

defined('ABSPATH') || exit();


/**
 * Woocommerce Swatch Component. Here you'll find both front and backend functions.
 *
 * @author Lucas Marini <lucas@uicore.co
 * @since 6.0.0
 */
class Swatches
{

    public function __construct()
    {
        $this->render_swatch_fields();
        $this->register_save_actions();

        // Enqueue styles and scripts
        if( is_admin() && isset( $_GET['taxonomy'], $_GET['post_type'] ) && $_GET['post_type'] === 'product') {
            add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_assets']);
        }
    }

    /**
     * Replace the Woo variation template for the swatch component.
     * @return void
     */
    public static function init()
    {
        add_filter( 'woocommerce_dropdown_variation_attribute_options_html', [self::class, 'print_swatches_form'], 10, 2 );
    }

    /**
     * Enqueues the Swatch assets for the admin dashboard funcionalities.
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     */
    function enqueue_admin_assets() {
        wp_enqueue_style('uicore-admin-swatches');
        wp_enqueue_script('uicore-admin-swatches');

        //required for upload
        wp_enqueue_media();

        //required for color picker
        wp_enqueue_style('uicore-admin');
        wp_enqueue_style('uicore-admin-icons');
        wp_enqueue_style('uicore-admin-font');
    }

    /**
     * Render the swatch fields in the admin dashboard pages. The pages are create/edit attributes and create/edit attribute terms.
     * @return void
     */
    function render_swatch_fields() {

        // Attributes pages
        add_action('woocommerce_after_add_attribute_fields', [$this, 'create_attribute_fields']);
        add_action('woocommerce_after_edit_attribute_fields', [$this, 'edit_attribute_fields']);

        // Attribute terms pages
        $product_att_terms = wc_get_attribute_taxonomies();
        foreach ( $product_att_terms as $term ) {
            $term_name = 'pa_' . $term->attribute_name; // `pa` stands for product attribute
            add_action("{$term_name}_add_form_fields", [$this, 'create_term_fields']); // Add fields to the term creation form
            add_action("{$term_name}_edit_form_fields", [$this, 'edit_term_fields'], 10, 2); // Add fields to the term edit form
        }
    }
    /**
     * Get the attribute swatch type.
     * @return string The swatch type.
     */
    public static function get_attribute_swatch_type($attribute_slug) {

        $attribute_id = wc_attribute_taxonomy_id_by_name($attribute_slug);
        $attribute_data = wc_get_attribute($attribute_id);
        $swatch_type = ( $attribute_data) ?
                        get_term_meta( $attribute_id, 'uicore_swatch_select', true ) :
                        '';
        return $swatch_type;
    }
    /**
     * Create the fields for the attribute terms creation form.
     * @return void
     */
    function create_term_fields($taxonomy) {

        $swatch_type = $this->get_attribute_swatch_type($taxonomy);

        if ( !empty($swatch_type) ) :
        ?>

            <div class="form-field term-uicore-label-wrap">
                <label for="uicore_label"><?php _e('Label', 'uicore-framework'); ?></label>
                <input type="text" name="uicore_label" id="uicore_label" value="">
                <p class="description"><?php _e('Enter the label value.', 'uicore-framework'); ?></p>
            </div>

        <?php endif; ?>
        <?php if( $swatch_type === 'color') : ?>

            <div class="form-field term-uicore-color-wrap" data-color-picker="true">
                <label for="uicore_color"><?php _e('Color', 'uicore-framework'); ?></label>
                <input type="hidden" name="uicore_color" id="uicore_color" value="#000000">
                <span class="ui-color-preview" style="--ui-swatches-bg:black"></span>
                <input type="button" class="button select_icon_color_button" value="<?php _e( 'Select Color', 'uicore-framework'); ?>" onCLick="uicore_set_color()" />
                <p class="description"><?php _e('Enter the color value.', 'uicore-framework'); ?></p>
            </div>

            <div class="form-field term-uicore-second-color-wrap" data-color-picker="true">
                <label for="uicore_second_color"><?php _e('Second Color', 'uicore-framework'); ?></label>
                <input type="hidden" name="uicore_second_color" id="uicore_second_color" value="">
                <span class="ui-color-preview" id="uicore_second_color_preview" ></span>
                <input type="button" class="button select_icon_color_button" value="<?php _e( 'Select Color', 'uicore-framework'); ?>" onCLick="uicore_set_color('second')" />
                <input type="button" class="button remove_color" value="<?php _e( 'Remove Color', 'uicore-framework'); ?>" onCLick="uicore_remove_color()" />
                <p class="description"><?php _e('Enter the second color value.', 'uicore-framework'); ?></p>
            </div>

        <?php elseif ( $swatch_type === 'image' || $swatch_type === 'button') : ?>

            <div class="form-field term-uicore-image-wrp">
                <label for="uicore_image"><?php _e('Image', 'uicore-framework'); ?></label>
                <input type="hidden" name="uicore_image" id="uicore_image" value="">
                <span class="ui-color-preview" id="uicore_image_preview"></span>
                <input type="button" class="button upload_image_button" value="<?php _e( 'Select Image', 'uicore-framework'); ?>" onCLick="uicore_set_image()" />
                <input type="button" class="button remove_image_button" value="<?php _e( 'Remove Image', 'uicore-framework'); ?>" onCLick="uicore_remove_image()" />
                <p class="description"><?php _e('Select image', 'uicore-framework'); ?></p>
            </div>

        <?php
        endif;
    }
    /**
     * Create the fields for the attribute terms edit form.
     * @return void
     */
    function edit_term_fields($term, $taxonomy) {

        // Get the attribute swatch type
        $swatch_type = $this->get_attribute_swatch_type($taxonomy);

        // Get the attribute terms data
        $label       = get_term_meta($term->term_id, 'uicore_label', true);
        $color       = get_term_meta($term->term_id, 'uicore_color', true);
        $color_2     = get_term_meta($term->term_id, 'uicore_second_color', true);
        $image       = get_term_meta($term->term_id, 'uicore_image', true);

        if ( !empty($swatch_type) ) :
        ?>

            <tr class="form-field term-uicore-label-wrap">
                <th scope="row" valign="top">
                    <label for="uicore_label"><?php _e('Label', 'uicore-framework'); ?></label>
                </th>
                <td>
                    <input type="text" name="uicore_label" id="uicore_label" value="<?php echo esc_attr($label); ?>">
                    <p class="description"><?php _e('Enter the label value.', 'uicore-framework'); ?></p>
                </td>
            </tr>

        <?php endif; ?>
        <?php if ( $swatch_type === 'color') : ?>

            <tr class="form-field uicore-field term-uicore-color-wrap">
                <th scope="row" valign="top">
                    <label for="uicore_color"><?php _e('Color', 'uicore-framework'); ?></label>
                </th>
                <td>
                    <input type="hidden" name="uicore_color" id="uicore_color" value="<?php echo esc_attr($color); ?>">
                    <span class="ui-color-preview" id="uicore_color_preview" style="--ui-swatches-bg:<?php echo esc_attr($color); ?>"></span>
                    <input type="button" class="button select_icon_color_button" value="<?php _e( 'Select Color', 'uicore-framework'); ?>" onCLick="uicore_set_color()" />
                    <p class="description"><?php _e('Enter the color value.', 'uicore-framework'); ?></p>
                </td>
            </tr>

            <tr class="form-field uicore-field term-uicore-second-color-wrap">
                <th scope="row" valign="top">
                    <label for="uicore_second-color"><?php _e('Secondary Color', 'uicore-framework'); ?></label>
                </th>
                <td>
                    <input type="hidden" name="uicore_second_color" id="uicore_second_color" value="<?php echo esc_attr($color_2); ?>">
                    <span class="ui-color-preview" id="uicore_second_color_preview" style="--ui-swatches-bg:<?php echo esc_attr($color_2); ?>"></span>
                    <input type="button" class="button select_icon_color_button" value="<?php _e( 'Select Color', 'uicore-framework'); ?>" onCLick="uicore_set_color('second')" />
                    <input type="button" class="button remove_color" value="<?php _e( 'Remove Color', 'uicore-framework'); ?>" onCLick="uicore_remove_color()" />
                    <p class="description"><?php _e('Enter the second color value.', 'uicore-framework'); ?></p>
                </td>
            </tr>

        <?php elseif ( $swatch_type === 'image' || $swatch_type === 'button') : ?>

            <tr class="form-field uicore-field term-uicore-image-wrap">
                <th scope="row" valign="top">
                    <label for="uicore_image"><?php _e('Image', 'uicore-framework'); ?></label>
                </th>
                <td>
                    <input type="hidden" name="uicore_image" id="uicore_image" value="<?php echo esc_url($image); ?>">
                    <span class="ui-color-preview" id="uicore_image_preview" style="<?php echo $image ? '--ui-swatches-bg: url('.\esc_url($image).') no-repeat center center/cover;' : '' ?>"></span>
                    <input type="button" class="button upload_image_button" value="<?php _e( 'Select Image', 'uicore-framework'); ?>" onCLick="uicore_set_image()" />
                    <input type="button" class="button remove_image_button" value="<?php _e( 'Remove Image', 'uicore-framework'); ?>" onCLick="uicore_remove_image()" />
                    <p class="description"><?php _e('Select an image.', 'uicore-framework'); ?></p>
                </td>
            </tr>

        <?php
        endif;
    }
    /**
     * Create the fields for the attribute creation form.
     * @return void
     */
    function create_attribute_fields() {
         // Select field
         ?>
         <div class="form-field uicore-swatch-select-wrap">
             <label for="uicore_swatch_select"> <?php _e('Swatch type', 'uicore-framework'); ?> </label>
             <select name="uicore_swatch_select" id="uicore_swatch_select">
                 <option value=""><?php _e('Select', 'uicore-framework'); ?></option>
                 <option value="color"><?php _e('Color', 'uicore-framework'); ?></option>
                 <option value="image"><?php _e('Image', 'uicore-framework'); ?></option>
                 <option value="label"><?php _e('Label', 'uicore-framework'); ?></option>
                 <option value="button"><?php _e('Button', 'uicore-framework'); ?></option>
             </select>
             <p class="description"><?php _e('Determines the variation swatch type.', 'uicore-framework'); ?></p>
         </div>
     <?php
    }
    /**
     * Create the fields for the attribute edit form.
     * @return void
     */
    function edit_attribute_fields() {

        $select = get_term_meta($_GET['edit'], 'uicore_swatch_select', true)

        ?>
        <tr class="form-field term-uicore-swatch-select-wrap">
            <th scope="row" valign="top">
                <label for="uicore_swatch_select"><?php _e('Swatch Type', 'uicore-framework'); ?></label>
            </th>
            <td>
                <select name="uicore_swatch_select" id="uicore_swatch_select">
                    <option value="" <?php selected($select, ''); ?>> <?php _e('Select', 'uicore-framework'); ?> </option>
                    <option value="color" <?php selected($select, 'color'); ?>> <?php _e('Color', 'uicore-framework'); ?> </option>
                    <option value="image" <?php selected($select, 'image'); ?>> <?php _e('Image', 'uicore-framework'); ?> </option>
                    <option value="label" <?php selected($select, 'label'); ?>> <?php _e('Label', 'uicore-framework'); ?> </option>
                    <option value="button" <?php selected($select, 'button'); ?>> <?php _e('Button', 'uicore-framework'); ?> </option>
                </select>
                <p class="description"><?php _e('Determines the variation swatch type.', 'uicore-framework'); ?></p>
            </td>
        </tr>
        <?php
    }

    /**
     * Save the swatch data in the database.
     * @return void
     */
    public function save_data($term_id) {
        $fields = [
            'uicore_swatch_select',
            'uicore_label',
            'uicore_color',
            'uicore_second_color',
            'uicore_image_inherit',
            'uicore_image'
        ];

        foreach ($fields as $field) {
            if (isset($_POST[$field])) {
                $value = ( $field === 'uicore_image' ) ?
                                esc_url_raw($_POST[$field]) :
                                sanitize_text_field($_POST[$field]);

                update_term_meta($term_id, $field, $value);
            } else {
                // If the value is not set, delete the meta from db
                delete_term_meta($term_id, $field);
            }
        }

    }

    /**
     * Register the actions to save the swatch data in the database, for all attribute and attribute terms updates.
     */
    public function register_save_actions() {
        // Attribute updates
        add_action('woocommerce_attribute_updated', [$this, 'save_data'], 10, 2);
        add_action('woocommerce_attribute_added', [$this, 'save_data'], 10, 2);

        // Attribute terms updates
        $product_att_terms = wc_get_attribute_taxonomies();
        foreach ($product_att_terms as $term) {
            $term_name = 'pa_' . $term->attribute_name; // `pa` stands for product attribute
            add_action("created_{$term_name}", [$this, 'save_data'], 10, 2);
            add_action("edited_{$term_name}", [$this, 'save_data'], 10, 2);
        }
    }

    /**
     * Print the archive/loop templates Swatch component.
     *
     * @param WC_Product $product The product object. Optional, but required for Uicore Elements
     *                            widgets that makes use of this component.
     * @return string The swatches HTML markup.
     */
    public static function print_swatches($product = null){

        if(  ! ( $product instanceof \WC_Product )){
            $product = wc_get_product( get_the_ID() );

            if(  ! ( $product instanceof \WC_Product )){
                return;
            }
        }

        $product_id   = $product->get_id();
        $attributes   = wc_get_attribute_taxonomies();
        $fallback_ref = end($attributes);

        foreach ( $attributes as $attribute ) {

            $att_name    = 'pa_' . $attribute->attribute_name; // `pa` stands for product attribute
            $terms       = get_the_terms($product_id, $att_name);
            $swatch_type = self::get_attribute_swatch_type($att_name);

            if ( !empty($terms) && !is_wp_error($terms) && $product !== false ) {

                // If the current swatch type is empty, it means is using the woo default
                if( empty($swatch_type) ) {

                    // If is the last attribute iteration, fallback to woo default
                    if ($attribute === $fallback_ref) {
                        return;
                    }

                    continue;  // Otherwise skip to the next $attribute iteration to check for the swatch type
                }

                // Prevent button swatches from being printed for UI purposes
                if( $swatch_type === 'button' ) {
                    continue;
                }

                wp_enqueue_script('uicore-swatches'); // If we got this far, swatches will be used so is safe to enqueue the script

                $att_hash = 'attribute_' . $att_name; // the url param filter for product attribute terms is `attribute_pa_` and  $att_name already comes with `pa_` prefix

                ?>
                    <div class="uicore-swatches-wrp" name="<?php echo esc_attr($att_hash);?>">
                        <?php
                            foreach($terms as $term) {

                                $content = self::get_swatch_item_content($term, $swatch_type);
                                $title = get_term_meta($term->term_id, 'uicore_label', true);
                                $url = add_query_arg(
                                    $att_hash,
                                    $term->slug,
                                    $product->get_permalink()
                                );
                                ?>
                                    <a  href="<?php echo esc_url($url); ?>"
                                        value="<?php echo esc_attr($term->slug);?>"
                                        title="<?php echo esc_html($title); ?>"
                                        class="uicore-swatch uicore-swatch--<?php echo esc_attr($swatch_type)?>"
                                        >
                                        <?php if ( $swatch_type === 'color' || $swatch_type === 'image' ) : ?>
                                            <div style="<?php echo esc_html($content);?>"></div>
                                        <?php else : ?>
                                            <div> <?php echo esc_html($content);?> </div>
                                        <?php endif; ?>
                                    </a>
                                <?php
                            }
                        ?>
                    </div>
                <?php

                return; // Return on the first attribute found to avoid bloating if product has multiple attributes
            }
        }
    }

    /**
     * Print the single product Swatch component form.
     *
     * @param string $default_swatch The default woocommerce variation markup.
     * @param array $args The arguments to build the swatches form.
     *
     * @return string The swatches HTML markup.
     */
    public static function print_swatches_form($default_swatch, $args) {

        $swatch_type = self::get_attribute_swatch_type($args['attribute']);

        // If the current swatch type is empty, it means is using the woo default
        if( empty($swatch_type) ) {
            return $default_swatch;
        }
        if(empty($args['options'])) {
            return $default_swatch;
        }

        wp_enqueue_script('uicore-swatches'); // If we got this far, swatches will be used so is safe to enqueue the script

        $attribute_ids = [];
        $terms = wc_get_product_terms(
            $args['product']->get_id(),
            $args['attribute'],
            array(
                'fields' => 'all',
            )
        );

        ob_start();
        ?>
            <ul class="uicore-swatches-wrp" >
                <?php
                    foreach ( $terms as $term ) {
                        if ( !in_array( $term->slug, $args['options'], true ) ) {
                            continue;
                        }
                        $title   = get_term_meta($term->term_id, 'uicore_label', true);
                        $title   = $title ? $title : $term->name;
                        $content = self::get_swatch_item_content($term, $swatch_type);
                        ?>
                            <li data-value="<?php echo esc_attr($term->slug);?>"
                                data-attribute-name="<?php echo esc_attr($args['attribute']);?>"
                                title="<?php echo esc_html($title); ?>"
                                class="uicore-swatch uicore-swatch--<?php echo esc_attr($swatch_type) . ( sanitize_title( $args['selected'] ) == $term->slug ? ' selected' : '' ); ?>"
                            >
                                <?php if ( $swatch_type === 'image' || $swatch_type === 'color' ) : ?>
                                    <div style="<?php echo esc_html($content);?>"></div>
                                <?php else : ?>
                                    <div> <?php echo wp_kses_post($content); ?> </div>
                                <?php endif; ?>
                            </li>
                        <?php
                    }
                ?>
            </ul>
        <?php
        $html = ob_get_clean();

        // Regex pattern to find the entire <select> element with id="pa_color"
        $pattern = '/(<select[^>]*id="' . esc_attr($args['attribute']) . '"[^>]*)(>)(.*?<\/select>)/is';

        // Modify the matched <select> element to include the inline style and add the new markup
        $markup = preg_replace($pattern, '$1 style="display:none;"$2$3' . $html, $default_swatch);

        //add uicore-is-$swatch_type class to the select element
        $markup = preg_replace('/<select/', '<select class="uicore-is-' . $swatch_type . '"', $markup);

        return $markup;
    }

    /**
     * Return the content for each Swatch item based on the type.
     *
     * @param WP_Term $term The term object.
     * @param object $swatch_type The product attribute swatch type.
     *
     * @return string The content of the swatch item.
     */
    public static function get_swatch_item_content($term, $swatch_type) {

        // Get the proper content and fallback for each type
        switch( $swatch_type ){
            case 'color':
                // Get both colors
                $colors = [
                    get_term_meta($term->term_id, 'uicore_color', true),
                    get_term_meta($term->term_id, 'uicore_second_color', true)
                ];

                // Search among both colors in case user sets the second but not the first
                $data = isset($colors[0]) ?
                        $colors[0] :
                        ( isset($colors[1]) ?
                            $colors[1] :
                            ''
                        );

                // Build bicolor style if there's two colors set
                $content = ( !empty($colors[0]) && !empty($colors[1]) ) ?
                                'background: linear-gradient(-45deg, ' . $colors[0] . ' 50%, '. $colors[1] . ' 50%);' :
                                'background-color:' . $data;
                $fallback = 'background-color: #c8d6e5';
                break;

            case 'image':

                $data = self::get_image_for_frontend($term);

                // Build the CSS background image property
                $content = 'background-image: url(' . esc_url($data) . ');';
                $fallback = 'background-image: url(' . esc_url(wc_placeholder_img_src()) . ');';
                break;

            case 'button':

                $image_data = self::get_image_for_frontend($term);
                $title = get_term_meta($term->term_id, 'uicore_label', true);
                $title = $title ? $title : $term->name;

                //create $data as an html that contains the image, the label and the description
                $data = '';
                $data .= $image_data ? '<img class="uicore-button-img" src="' . esc_url($image_data) . '" alt="' . esc_html($title) . '" />' : '';
                $data .= '<div class="uicore-button-text">';
                    $data .= '<div class="uicore-button-label">' . esc_html($title) . '</div>';
                    $data .= '<div class="uicore-button-desc">' . esc_html($term->description) . '</div>';
                $data .= '</div>';

                $content = $data;
                break;


            // Default case is `label`
            default:
                $data     = get_term_meta($term->term_id, 'uicore_label', true);
                $content  =  $data;
                $fallback = esc_html($term->name);
                break;

        }

        if ( empty($data) ) {
            return $fallback;
        }

        return $content;
    }

    /**
     * Get current product variation custom image or the attribute term image.
     * @param WP_Term $term The term object.
     *
     * @return string The image URL.
     */
    static function get_image_for_frontend($term) {

        // Check if the term should inherit the image from the product variation
        if (Helper::get_option('woo_swatch_inherit_image') === 'true') {

            global $product;
            if ($product && $product->is_type('variable')) {

                // Loop through variations
                foreach ($product->get_available_variations() as $variation) {
                    $variation_id  = $variation['variation_id'];
                    $attributes    = $variation['attributes'];
                    $attribute_key = 'attribute_' . $term->taxonomy;

                    // Check if this variation matches the current term
                    if ( isset($attributes[$attribute_key]) && $attributes[$attribute_key] == $term->slug ) {
                        // Get the variation image ID
                        $variation_image_id = get_post_thumbnail_id($variation_id);
                        if ($variation_image_id) {
                            $data = wp_get_attachment_url($variation_image_id);
                            break;
                        }
                    }
                }
            }

            // Fallback to term image if no variation image was found
            if (empty($data)) {
                $data = get_term_meta($term->term_id, 'uicore_image', true);
            }

        // Get term image otherwhise
        } else {
            $data = get_term_meta($term->term_id, 'uicore_image', true);
        }

        return $data;
    }
}
new Swatches();

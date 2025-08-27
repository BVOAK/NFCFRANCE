<?php

namespace UiCore;


use WP_Error;

defined('ABSPATH') || exit();

/**
 * Brisk Core Utils Functions
 */
class Import
{
    /**
     * The request data
     *
     * @var      array
     */
    public $response = [];
    /**
     * The request data
     *
     * @var      array
     */
    public $response_data = null;

    /**
     * The request data
     *
     * @var      object
     */
    protected $tmgpa;

    /**
     * The request data
     *
     * @var      array
     */
    protected $tgmpa_plugins = [
        // This is an example of how to include a plugin bundled with a theme.
        [
            'name' => 'Elementor', // The plugin name.
            'slug' => 'elementor', // The plugin slug (typically the folder name).
            'required' => true, // If false, the plugin is only 'recommended' instead of required.
        ],

        // This is an example of how to include a plugin bundled with a theme.
        [
            'name' => 'Element Pack', // The plugin name.
            'slug' => 'bdthemes-element-pack', // The plugin slug (typically the folder name).
            'required' => true, // If false, the plugin is only 'recommended' instead of required.
        ],

        // This is an example of how to include a plugin bundled with a theme.
        [
            'name' => 'MetForm', // The plugin name.
            'slug' => 'metform', // The plugin slug (typically the folder name).
            'required' => true, // If false, the plugin is only 'recommended' instead of required.
        ],

        [
            'name' => 'WooCommerce', // The plugin name.
            'slug' => 'woocommerce', // The plugin slug (typically the folder name).
            'required' => false, // If false, the plugin is only 'recommended' instead of required.
        ],

        [
            'name' => 'Tutor', // The plugin name.
            'slug' => 'tutor', // The plugin slug (typically the folder name).
            'required' => false, // If false, the plugin is only 'recommended' instead of required.
        ],
    ];

    /**
     * The request data
     *
     * @var      array
     */
    private $imported_media = [];

    /**
     * The request data
     *
     * @var      array
     */
    private $imported_posts = [];

    /**
     * The request data
     *
     * @var      array
     */
    private $imported_menus = [];

    /**
     * imported_demos
     *
     * @var undefined
     */
    private $imported_demos = [];

    /**
     * imported_demos
     *
     * @var undefined
     */
    private $uicore_no_media = null;

    /**
     * imported_demos
     *
     * @var undefined
     */
    private $uicore_no_media_id = true;

    /**
     * WP_Error Class
     *
     * @var undefined
     */
    private $errors;

    private $slug;

    private $theme;

    /**
     * __construct
     *
     * @return void
     */
    public function __construct($request)
    {
        $this->errors = new WP_Error();


        //Set Imported
        $this->set_imported();

        //If import is runing without media
        if ($request['no_media'] && !$this->uicore_no_media_id) {
            $this->uicore_no_media = true;
            $this->import_media([1 => ['url' => UICORE_ASSETS . '/img/default.png', 'path' => '']]);
            $this->fake_import_media($request['no_media']);
        }

        if($request['theme']){
            $this->theme = $request['theme'];
        }

        if ($request['slug']) {
            $this->slug = $request['slug'];
            if($this->slug === 'inner'){
                update_option('uicore_imported_inner',true,false);
            }
        }

        //Import activate, install or update plugins
        if ($request['theme']) {
            $this->installTheme($request['theme']);
        }
        //Import activate, install or update plugins
        if ($request['child']) {
            $this->install_child($request['child']);
        }


        //Import activate, install or update plugins
        if ($request['plugin']) {
            $this->import_plugin($request['plugin']);
        }
        //Import Media if exist
        if ($request['media']) {
            $this->import_media($request['media']);
        }
        //Import Met Forms
        if ($request['met_forms']) {
            $this->import_posts($request['met_forms'], 'metform-form');
        }
        // //Import Pages if exist
        if ($request['pages']) {
            $this->import_posts($request['pages'], 'page', $request['user']);
        }
        //Import Posts if exist
        if ($request['posts']) {
            $this->import_posts($request['posts'], 'post', $request['user']);
        }
        //Import Portfolios if exist
        if ($request['portfolio']) {
            $this->import_posts($request['portfolio'], 'portfolio', $request['user']);
        }
        //import woo attributes
        if ($request['woocommerce_attributes']) {
            $this->import_woocommerce_attributes($request['woocommerce_attributes']);
        }
        //import woo terms
        if ($request['woocommerce_terms']) {
            $this->import_woocommerce_terms($request['woocommerce_terms']);
        }
        //import woo categories
        if ($request['woocommerce_categories']) {
            $this->import_woocommerce_categories($request['woocommerce_categories']);
        }
        //Import Products if exist
        if ($request['products']) {
            $this->import_posts($request['products'], 'product', $request['user']);
        }
        //Import TB Header if exist
        if ($request['tb_header']) {
            $this->import_posts($request['tb_header'], 'tb_header', $request['user']);
        }
        //Import TB Header if exist
        if ($request['tb_footer']) {
            $this->import_posts($request['tb_footer'], 'tb_footer', $request['user']);
        }
        //Import TB Header if exist
        if ($request['tb_mm']) {
            $this->import_posts($request['tb_mm'], 'tb_mm', $request['user']);
        }
        //Import TB Header if exist
        if ($request['tb_block']) {
            $this->import_posts($request['tb_block'], 'tb_block', $request['user']);
        }
        //Import TB Header if exist
        if ($request['tb_popup']) {
            $this->import_posts($request['tb_popup'], 'tb_popup', $request['user']);
        }
        if ($request['tb_archive']) {
            $this->import_posts($request['tb_archive'], 'tb_archive', $request['user']);
        }
        if ($request['tb_single']) {
            $this->import_posts($request['tb_single'], 'tb_single', $request['user']);
        }
        if ($request['tb_pagetitle']) {
            $this->import_posts($request['tb_pagetitle'], 'tb_pagetitle', $request['user']);
        }

        if ($request['settings']) {
            $this->import_settings($request['settings']);
        }

        if ($request['menu']) {
            $this->import_menu($request['menu'], $request['slug']);
        }

        if ($request['widgets']) {
            $assets = isset($request['widgets_assets']) ? $request['widgets_assets'] : [];
            $this->import_sidebar($request['widgets'], $assets);
        }

        $this->globals();

        if (isset($request['type'])) {
            //clear all frontend transients
            Helper::delete_frontend_transients();
            $this->clean_globals();
            Helper::activate_ep();
        }

        if($this->errors->get_error_code()){
            $this->response = $this->errors;
            if(!$request['nolog']){
                $this->imported_demos['Import:' . $request['slug'].' - '.date('Y-m-d')]['errors'][] = $this->response;
            }
        }else{
            $this->response = [
                'status' => 'succes',
                'data' => $this->response_data
            ];
        }
        // $this->response['extra'] = $this->imported_menus;

    }

    function set_imported()
    {
        //Get storred demos if exists
        $this->imported_demos = get_option('uicore_imported_demos', []);

        //Get storred demos if exists
        $this->slug = get_transient('uicore_slug');

        //Get storred media if exists
        $this->imported_media = get_option('uicore_imported_media', []);

        //Get storred menus if exists
        $this->imported_menus = get_option('uicore_imported_menus', []);

        //Get storred posts if exists
        $this->imported_posts = get_option('uicore_imported_posts', []);

        $this->uicore_no_media = get_transient('uicore_no_media');
        $this->uicore_no_media_id = get_transient('uicore_no_media_id');
    }

    function globals()
    {
        if($this->slug){
            set_transient('uicore_slug', $this->slug, HOUR_IN_SECONDS);
        }
        update_option('uicore_imported_demos', $this->imported_demos);
        update_option('uicore_imported_media', $this->imported_media);
        update_option('uicore_imported_menus', $this->imported_menus);
        update_option('uicore_imported_posts', $this->imported_posts);
        set_transient('uicore_no_media', $this->uicore_no_media, HOUR_IN_SECONDS);
        set_transient('uicore_no_media', $this->uicore_no_media_id, HOUR_IN_SECONDS);

    }

    function clean_globals()
    {
        update_option('uicore_imported_media', []);
        update_option('uicore_imported_menus', []);
        update_option('uicore_imported_posts', []);
        delete_transient('uicore_no_media');
        delete_transient('uicore_no_media_id');
        delete_transient('uicore_slug');
    }

    function import_plugin($plugin)
    {
        require_once ABSPATH . 'wp-admin/includes/plugin.php';
        if ($plugin['status'] == 'uninstalled' || $plugin['status'] == 'installing') {
            $this->installPlugin($plugin);

            //if plugin is framework clear cache after update
            if(strpos($plugin['slug'],'uicore-framework') !== false){
                Settings::clear_cache(true);
            }
        } else {
            $silent = true;
            //silent false if is woocommerce plugin; we need it to call his activation hooks to create tables
            if ($plugin['path'] === 'woocommerce/woocommerce.php') {
                $silent = false;
            }
            $activate = activate_plugin($plugin['path'], '', false, $silent);
            if (is_wp_error($activate)) {
                $this->errors->add('1001','Error on activating plugin - '.$plugin['path']);            }
        }
    }

    function installPlugin($plugin)
    {
        $url = '';

        //for demo import
        if(isset($plugin['name'])){
            foreach ($this->tgmpa_plugins as $tgmpa_plugin) {
                if ($tgmpa_plugin['name'] == $plugin['name']) {
                    $slug = $tgmpa_plugin['slug'];
                    break;
                }
            }
        }else{
            $slug = $plugin['slug'];
        }

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/plugin-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';

        //metform is in public api without download url so we need to skip this and get it form local folder
        $api = new \WP_Error();
        if($slug != 'metform-pro'){
            //get the plugin
            $api = plugins_api('plugin_information', [
                'slug' => $slug,
                'fields' => [
                    'short_description' => false,
                    'sections' => false,
                    'requires' => false,
                    'rating' => false,
                    'ratings' => false,
                    'downloaded' => false,
                    'last_updated' => false,
                    'added' => false,
                    'tags' => false,
                    'compatibility' => false,
                    'homepage' => false,
                    'donate_link' => false,
                ],
            ]);
        }
        if (!is_wp_error($api) && ($slug != 'bdthemes-element-pack' OR $slug != 'envato-market' OR $slug != 'metform-pro')) {
            $url = $api->download_link;
        } else {
            if($slug == 'envato-market'){
                $url = 'https://envato.github.io/wp-envato-market/dist/envato-market.zip';
            } elseif (file_exists($local = get_template_directory() . '/inc/plugins/' . $slug . '.zip')) {
                $url = $local;

            } else {
                $this->errors->add('1002','Can\'t find the plugin download url - '.$local. file_exists($local));
                return;
            }
        }

        ob_start();

		$args = ["overwrite_package" => true ];
        $skin = new Quiet_Skin();
        $upgrader = new \Plugin_Upgrader($skin);
        $result = $upgrader->install($url, $args);

        ob_clean();

        if ($result) {
            $activate = activate_plugin($plugin['path'], '', false, true);
            if (is_wp_error($activate)) {
                $this->errors->add('1003','Plugin was installed but can\'t be activated - ' . $plugin['path']);
            }
        } else {
            $this->errors->add('1004','Plugin was not installed - '.$plugin['path']);
        }
    }

    /**
     * import_media
     *
     * @param  array $media
     *
     * @return void
     */
    private function import_media($media)
    {
        //Required for media import
        if (!function_exists('wp_tempnam')) {
            include_once ABSPATH . 'wp-admin/includes/image.php';
            include_once ABSPATH . 'wp-admin/includes/file.php';
            include_once ABSPATH . 'wp-admin/includes/media.php';
        }

        $settings = ThemeOptions::get_admin_db_options();

        //Declare blanck array's
        $temp_name = $files = $headers = [];

        //Preparing requests
        foreach ($media as $id => $item) {
            $url = $item['url'];
            $file = basename(parse_url($url, PHP_URL_PATH));

            //Check if is default img
            if ( $file === 'default.png' ) {
                continue;
            }

            //Add Proxy Server
            if(isset($settings['proxy']) && $settings['proxy'] === 'true'){
                $headers['Proxy-Auth'] = 'Bj5pnZEX6DkcG6Nz6AjDUT1bvcGRVhRaXDuKDX9CjsEs2';
                $headers['Proxy-Target-URL'] = $url;
                $url = 'https://proxy.uicore.co/get.php?url=' . $url;
            }

            //Generate the temp file
            $temp_name[$url] = wp_tempnam($file);
            //Request Options
            $options = array(
                'timeout'   => 600,
                'stream'=> true,
                'filename'=>$temp_name[$url],
            );

            //Do a single request
            \WpOrg\Requests\Requests::request($url,$headers,[],'GET',$options);

            //Add file to import array
            $files[$id] = [
                'url' => $url,
                'tmp_name' => $temp_name[$url],
            ];
        }
        //Do multiple requests at a time to speed up the process
        // \Requests::request_multiple($requests);


        //Move from temp to media folder and add in database
        foreach ($files as $id => $item) {

            //Check if item have his temp file setted
            if (isset($item['tmp_name']) || !empty($item['tmp_name'])) {
                //Add Media
                $this->import_media_item($id, $item['url'], $item['tmp_name']);
            }else{
                $this->errors->add('2001', 'Error on storing media to temp - '.$item['url'] );
            }
        }

        //return imported media in response data
        $this->response_data = $this->imported_media;

    }

    /**
     * Check if media exist
     *
     * @param   string $filename
     *
     * @return  boolean
     */
    private function media_exist($filename)
    {
        global $wpdb;

        //remove extension from filename
        $title = preg_replace('/\.[^.]+$/', '', $filename);

        return $wpdb->get_var("
            SELECT COUNT(*) FROM
            $wpdb->posts    AS p,
            $wpdb->postmeta AS m
            WHERE
            p.ID = m.post_id
            AND p.post_type = 'attachment'
            AND p.post_title LIKE '$title'
        ");
    }

    /**
     * Import media item
     *
     * @param   integer $old_id
     * @param   string  $url
     * @param   string $file_name
     *
     * @return  Integer
     */
    private function import_media_item($old_id, $old_url, $file_name)
    {

        //Require for media_handle_sideload
        if (!function_exists('media_handle_sideload')) {
            require_once ABSPATH . 'wp-admin/includes/image.php';
            require_once ABSPATH . 'wp-admin/includes/file.php';
            require_once ABSPATH . 'wp-admin/includes/media.php';
        }

        if( is_wp_error( $file_name ) ){
            // download failed, handle error
            $this->errors->add('2001', 'Error on storing media to temp - '. $file_name );
            return;
        }


        //Define the file that will be added
        $file_array = [
            'name' => wp_basename($old_url),
            'tmp_name' => $file_name
        ];

        //Move file to media folder and add it to db
        $id = media_handle_sideload($file_array);

        if ( is_wp_error($id) ) {
            // If error storing permanently, unlink
            @unlink($file_array['tmp_name']);
            $this->errors->add('2002', 'Error on uploading media to site - '.$file_array['name'], [$id ,$file_array]);
        } else {
            //import with media
            if (!$this->uicore_no_media) {
                //Store item for future id and url replacement
                $this->imported_media[$id] = [
                    'old_id' => $old_id,
                    'old_url' => $old_url,
                ];
            } else {
                $this->uicore_no_media_id = $id;
            }
        }
    }

    /**
     * Fake Import media item
     *
     * @param   integer $old_id
     * @param   string  $url
     * @param   string $file_name
     *
     * @return  Integer
     */
    private function fake_import_media($media_array)
    {
        foreach ($media_array as $id => $media) {
            //Store item for future id and url replacement
            array_push($this->imported_media, [
                'old_id' => $id,
                'old_url' => $media['url'],
            ]);
        }
    }

    private function import_posts($pages, $type = 'post', $user = 1)
    {
        foreach ($pages as $page) {
            $this->import_post_item($page, $type, $user);
        }
    }

    private function import_post_item($post, $type, $user)
    {
        //TB Type Handle
        $tb_type = false;
        if (substr( $type, 0, 3 ) === "tb_") {
            $tb_type = str_replace('tb_','', $type);
            $type = 'uicore-tb';
        }

        //Decode content and title
        $content = base64_decode($post['post_content']);
        $title = base64_decode($post['post_title']);
    

        // Check if item already exist
        if ($this->post_exist($title, $type, $post['ID'])) {
            //get post id by title from db
            $post_from_db = get_page_by_title($title, OBJECT, $type);
            if($post_from_db){
                $this->imported_posts[$post_from_db->ID] = $post['ID'];
            }
            return;
        }
        try {
            $content = $this->content_fix($content);
            $content = mb_convert_encoding($content, "UTF-8", "UTF-8");
            $content = wp_encode_emoji($content);
           //fix for gutenberg css var in attributes (maybe use wp_slash on al post data but did not had time to test this with elementor)
            $content = str_replace('(\u002d\u002d', '(--', $content);
        } catch (\Exception $e) {
            $this->errors->add('3000', 'Error on decoding post content - '.$title, $e);
            return;
        }

        $post_id = wp_insert_post([
            'post_title' => $title,
            'post_content' => $content,
            'post_excerpt' => $post['post_excerpt'],
            'post_date' => $post['post_date'],
            'post_type' => $type,
            'post_author' => $user,
            'post_status' => 'publish',
        ],true);


        if (is_wp_error($post_id)) {
            $this->errors->add('3001', 'Error on inserting post - '.$title, $post_id);
            $this->response_data = $post_id->get_error_message();
            return;
        } else {
            if (!empty($post['post_meta'])) {   
                //Remove those for now
                if (isset($post['post_meta']['post_tag'])) {
                    unset($post['post_meta']['post_tag']);
                }
                if (isset($post['post_meta']['category'])) {
                    unset($post['post_meta']['category']);
                }

                // Add post meta data
                foreach ($post['post_meta'] as $meta_key => $meta_value) {
                    // Unserialize when data is serialized
                    if (isset($meta_value[0])) {
                        $meta_value = \maybe_unserialize($meta_value[0]);
                    }
                    switch ($meta_key) {
                        case '_elementor_data':
                            // Update elementor data
                            $meta_value = $this->elementor_fix($meta_value);

                            // We need the `wp_slash` in order to avoid the unslashing during the `update_post_meta`
                            $meta_value = wp_slash($meta_value);
                            break;
                        case 'page_options':
                            $meta_value = $this->update_url($meta_value);
                            break;

                        case'_thumbnail_id':
                            $meta_value = $this->get_new_media_id($meta_value);
                            break;
                        case'_product_image_gallery':
                            $meta_value = explode(',', $meta_value);
                            $meta_value = array_map([$this, 'get_new_media_id'], $meta_value);
                            $meta_value = implode(',', $meta_value);
                            break;
                        case '_uicore_block_critical_styles':
                        case '_uicore_block_styles':
                            $meta_value = $this->update_url($meta_value);
                            $meta_value = str_replace('.uicore-bl-'.$post['ID'], '.uicore-bl-'.$post_id, $meta_value);
                            break;
                    }
                    update_post_meta($post_id, $meta_key, $meta_value);
                }
            }


            if (!empty($post['attributes'])) {
                $product = \wc_get_product($post_id);
                $attributes = array();

                foreach ($post['attributes'] as $attribute_data) {
                    $attribute = new \WC_Product_Attribute();

                    // If your attributes are global (taxonomy-based)
                    if (!empty($attribute_data['id'])) {
                        $attribute->set_id($attribute_data['id']);
                    }

                    // Set the name (taxonomy for global attributes, name for custom)
                    $attribute->set_name($attribute_data['name']);

                    // Set the options (terms or custom values)
                    // transform from slug to id
                    $options = $attribute_data['value'];
                    if(\is_array($options)){
                        $options = array_map(function($option)use($attribute_data){
                            $term = get_term_by('slug', $option, $attribute_data['name']);
                            return $term->term_id;
                        }, $options);
                    }

                    $attribute->set_options($options);

                    // Set visibility, position, and variation usage
                    $attribute->set_visible(isset($attribute_data['visible']) ? $attribute_data['visible'] : true);
                    $attribute->set_position(isset($attribute_data['position']) ? $attribute_data['position'] : 0);
                    $attribute->set_variation(isset($attribute_data['variation']) ? $attribute_data['variation'] : false);

                    $attributes[] = $attribute;
                }

                // Set the attributes and save the product
                $product->set_attributes($attributes);
                $product->save();
            }



            if(!empty($post['variations'])){
                foreach ($post['variations'] as $variation_data) {
                    $variation_attr = $variation_data['attributes'] ?? [];
                    $variation = new \WC_Product_Variation();
                    $variation->set_parent_id($post_id);
                    $variation->set_status('publish');
                    $variation->set_regular_price($variation_data['regular_price']);
                    $variation->set_sale_price($variation_data['sale_price']);
                    $variation->set_image_id($this->get_new_media_id($variation_data['post_thumb']));
                    $variation->set_attributes($variation_attr);
                    // Save the changes
                    $id = $variation->save();
                    if(is_wp_error($id)){
                        $this->errors->add('3002', 'Error on inserting variation - '.$title, $id);
                    }
                }
                $product = new \WC_Product_Variable($post_id);
                $product->save();
                \WC_Product_Variable::sync( $post_id );
            }

            //Set Tb Stype if is TB
            if($tb_type){
                wp_set_post_terms($post_id, '_type_' . $tb_type, 'tb_type');
            }

            //Store item for future id and url replacement
            $this->imported_posts[$post_id] = $post['ID'];

            if ($post['post_thumb'] != '') {
                /* Get Attachment ID */
                $attachment_id = $this->get_new_media_id($post['post_thumb']);

                if ($attachment_id) {
                    set_post_thumbnail($post_id, $attachment_id);
                }
            }
            //if is product and has categories
            if ($type == 'product' && !empty($post['categories'])) {
                $terms = [];
                foreach ($post['categories'] as $category) {
                    $term = get_term_by('name', $category, 'product_cat');
                    if ($term) {
                        $terms[] = $term->term_id;
                    }
                }
                wp_set_object_terms($post_id, $terms, 'product_cat');
            }

            //Check if is Frontpage Or Home
            if ($type == 'page') {
                if ($post['front'] == 'front') {
                    update_option('page_on_front', $post_id);
                    update_option('show_on_front', 'page');
                }
                if ($post['front'] == 'home') {
                    update_option('page_for_posts', $post_id);
                }
            }

            $this->maybe_flush_post($post_id);
        }
    }

    /**
     * check post existence
     *
     * @param   string  $title
     * @param   integer $post_ID
     * @param   string  $content
     * @param   string  $date
     *
     * @return  0 | post ID
     */
    public function post_exist($title, $type)
    {
        global $wpdb;

        $sql = $wpdb->prepare("
            SELECT ID FROM {$wpdb->posts}
            WHERE post_title = %s
            AND post_type = %s
            LIMIT 1
        ", $title, $type);

        $result = $wpdb->get_var($sql);

        return !empty($result);
    }

    private function update_url($content)
    {
        /*---------------------------------------------------------
        * 1.  Build a single search/replace map for every media item
        *--------------------------------------------------------*/
        $search  = [];
        $replace = [];
        $img_ext = '/\.(jpe?g|gif|png|webp)$/i';

        foreach ($this->imported_media as $id => $media) {
            // If “no-media” mode is on, force every item to the same ID
            $media_id = $this->uicore_no_media ? $this->uicore_no_media_id : $id;

            // Strip the extension so all size variants match
            $old = preg_replace($img_ext, '', $media['old_url']);
            $new = preg_replace($img_ext, '', wp_get_attachment_url($media_id));

            $search[]  = $old;
            $replace[] = $new;
        }

        /*---------------------------------------------------------
        * 2.  Recursive walker — handles strings, arrays, objects
        *--------------------------------------------------------*/
        $walker = function (&$node) use (&$walker, $search, $replace) {
            if (is_array($node)) {
                foreach ($node as &$item) {
                    $walker($item);
                }
            } elseif (is_object($node)) {
                foreach ($node as &$item) {        // public properties only
                    $walker($item);
                }
            } elseif (is_string($node)) {
                $node = str_replace($search, $replace, $node);
            }
        };

        $walker($content);
        return $content;
    }

    private function get_new_media_id($old_id)
    {
        if ($this->uicore_no_media) {
            return $this->uicore_no_media_id;
        }
        if (isset($old_id) && $old_id != null) {
            //Loop trough all imported media
            foreach ($this->imported_media as $new_id => $media) {
                if ($media['old_id'] == $old_id) {
                    return $new_id;
                }
            }
        }
        //Return null if id was not finded
        return null;
    }

    private function get_new_post_id($old_id)
    {
        if (isset($old_id) && $old_id != null) {
            //Loop trough all imported media
            foreach ($this->imported_posts as $new_id => $old) {
                if ($old == $old_id) {
                    return $new_id;
                }
            }
        }
        //Return null if id was not finded
        return null;
    }

    private function import_woocommerce_attributes($attributes)
    {
        foreach ($attributes as $attribute) {
            //Check if item already exist and if not create it
            if(\taxonomy_exists($attribute['name'])){
                continue;
            }

            $args = [
                'name' => $attribute['name'],
                'slug' => $attribute['slug'],
                'type' => $attribute['type'],
                'order_by' => $attribute['order_by'],
            ];
            //create attribute
            $result = wc_create_attribute($args);

            //if it worked add the terms
            if ($result) {
                $this->response_data[] = 'created attribute - ' . $attribute['slug'];
                //Register it as a wordpress taxonomy for just this session. Later on this will be loaded from the woocommerce taxonomy table.
                register_taxonomy(
                    'pa_'.$attribute['slug'],
                    apply_filters( 'woocommerce_taxonomy_objects_' . 'pa_'.$attribute['slug'], array( 'product' ) ),
                    apply_filters( 'woocommerce_taxonomy_args_' . 'pa_'.$attribute['slug'], array(
                        'labels'       => array(
                            'name' => $attribute['name'],
                        ),
                        'hierarchical' => true,
                        'show_ui'      => false,
                        'query_var'    => true,
                        'rewrite'      => false,
                    ) )
                );

                //Clear caches
                delete_transient( 'wc_attribute_taxonomies' );

                //add meta
                if (!empty($attribute['meta'])) {
                    foreach ($attribute['meta'] as $meta_key => $meta_value) {
                        if (isset($meta_value[0])) {
                            $meta_value = \maybe_unserialize($meta_value[0]);
                        }
                        $res = add_term_meta($result, $meta_key, $meta_value);
                        if(is_wp_error($res)){
                            $this->errors->add('4002', 'Error on adding attribute meta - '.$meta_key.' - '.$meta_value, $res);
                        }
                    }
                }
            }
        }
    }

    private function import_woocommerce_categories($categories) {
        if ( empty($categories) || ! is_array($categories) ) {
            return;
        }

        foreach ($categories as $category) {
            // Check if the category already exists by slug.
            $term = term_exists($category['slug'], 'product_cat');

            if ( $term === 0 || $term === null ) {
                // Create a new category if it does not exist.
                $inserted_term = wp_insert_term(
                    $category['name'],
                    'product_cat',
                    array(
                        'slug'        => $category['slug'],
                        'description' => $category['description']
                    )
                );
                if ( is_wp_error( $inserted_term ) ) {
                    // Log or handle the error as needed.
                    continue;
                }
                $term_id = $inserted_term['term_id'];
            } else {
                // Term exists: update description if necessary.
                $term_id = is_array($term) ? $term['term_id'] : $term;
                wp_update_term(
                    $term_id,
                    'product_cat',
                    array(
                        'description' => $category['description']
                    )
                );
            }

            // Update term meta.
            if ( ! empty( $category['meta'] ) && is_array( $category['meta'] ) ) {
                foreach ( $category['meta'] as $meta_key => $meta_values ) {
                    // Typically, get_term_meta returns an array, so we'll update using the first value.
                    if ( is_array( $meta_values ) && ! empty( $meta_values ) ) {
                        update_term_meta( $term_id, $meta_key, maybe_unserialize( $meta_values[0] ) );
                    }
                }
            }
        }
    }

    private function import_woocommerce_terms($terms)
    {
        foreach ($terms as $term) {
            //Check if item already exist and if not create it
            if(\term_exists($term['name'], $term['taxonomy'])){
                continue;
            }

            //check if taxonomy exist and if not register it
            if(!taxonomy_exists($term['taxonomy'])){
                register_taxonomy(
                    $term['taxonomy'],
                    apply_filters( 'woocommerce_taxonomy_objects_' . $term['taxonomy'], array( 'product' ) ),
                    apply_filters( 'woocommerce_taxonomy_args_' . $term['taxonomy'], array(
                        'labels'       => array(
                            'name' => $term['taxonomy'],
                        ),
                        'hierarchical' => true,
                        'show_ui'      => false,
                        'query_var'    => true,
                        'rewrite'      => false,
                    ) )
                );
            }

            //create attribute
            $result = wp_insert_term($term['name'], $term['taxonomy'], [
                'slug' => $term['slug'],
                'description' => $term['description']
            ]);

            //if it worked add the terms
            if (!is_wp_error($result)) {
                //add meta
                if (!empty($term['meta'])) {
                    foreach ($term['meta'] as $meta_key => $meta_value) {
                        if (isset($meta_value[0])) {
                            $meta_value = \maybe_unserialize($meta_value[0]);
                        }
                        if($meta_key === 'uicore_image'){
                            //replace with our url
                            $meta_value = $this->update_url($meta_value);
                        }
                        if(!$meta_value){
                            continue;
                        }
                        $res = add_term_meta($result['term_id'], $meta_key, $meta_value);
                        if(is_wp_error($res)){
                            $this->errors->add('4002', 'Error on adding term meta - '.$meta_key.' - '.$meta_value, $res);
                        }
                    }
                }
            }else{
                $this->errors->add('4001', 'Error on creating term - '.$term['name'], $result);
            }
        }
    }
    public function content_fix($content)
    {
        $content = $this->update_url($content);
        
        //replace image id:
        //36||Custom
        //wp-image-36
        //data-bl-image="39"
        //"image":{"url":"https://cdn.gtbg.uicore.pro/2025/05/Billing-Company-Team-BG.webp","id":43}

        //36||Custom
        $content = preg_replace_callback('/\b(\d+)\|\|Custom/', function ($matches) {
            $new_id = $this->get_new_media_id($matches[1]);
            return $new_id . '||Custom';
        }, $content);

        $content = preg_replace_callback('/data-bl-image="(\d+)"/', function ($matches) {
            $new_id = $this->get_new_media_id($matches[1]);
            return 'data-bl-image="' . $new_id . '"';
        }, $content);

        $content = preg_replace_callback('/wp-image-(\d+)/', function ($matches) {
            $new_id = $this->get_new_media_id($matches[1]);
            return 'wp-image-' . $new_id;
        }, $content);

        $content = preg_replace_callback('/"image":\{"url":"(.*?)","id":(\d+)\}/', function ($matches) {
            $new_id = $this->get_new_media_id($matches[2]);
            $new_url = wp_get_attachment_url($new_id);
            return '"image":{"url":"' . $new_url . '","id":' . $new_id . '}';
        }, $content);

        // \error_log('===============================');
        // \error_log('==========Content Fix==========');
        // \error_log($content);
        return $content;
    }
    public function elementor_fix($meta)
    {
        $matches = [];
        $attach_keys = ['image', 'img', 'photo', 'poster', 'media', 'src'];

        foreach ($attach_keys as $attach_key) {
            preg_match_all('/\s*"\b\w*' . $attach_key . '\w*\"\s*:\{.*?\}/', $meta, $image);
            if (isset($image) && !empty($image)) {
                $matches = array_merge($matches, $image);
            }
        }

        preg_match_all('/"wp_gallery":(\[.*?\])/', $meta, $wp_gallery, PREG_SET_ORDER);
        if (!empty($wp_gallery)) {
            foreach ($wp_gallery as $gallery_key => $gallery_val) {
                preg_match_all('/\{\"id":.*?\}/', $gallery_val[0], $gallery);
                $matches = !empty($gallery) ? array_merge($matches, $gallery) : $matches;
            }
        }

        // remove empties
        $matches = array_filter($matches);
        foreach ($matches as $images) {
            foreach ($images as $image) {
                $isIntegerValue = false;
                preg_match('/(?:"id":")(.*?)(?:")/', $image, $image_id);
                if (!isset($image_id[1]) || empty($image_id[1])) {
                    // This is a fixup for integer values of elementor json data value.
                    preg_match('/\"id":(\d*)/', $image, $image_id);
                    if (!isset($image_id[1]) || empty($image_id[1])) {
                        continue;
                    }
                    $isIntegerValue = true;
                }
                $image_id = strval($image_id[1]);

                preg_match('/(?:"url":")(.*?)(?:")/', $image, $image_url);
                if (!isset($image_url[1]) || empty($image_url[1])) {
                    continue;
                }
                $image_url = $image_url[1];

                $new_image_id = $new_image_url = '';

                $new_image_id = $this->get_new_media_id($image_id);
                $new_image_url = wp_get_attachment_url($new_image_id);

                if (!empty($new_image_id) && !empty($new_image_url)) {
                    if ($isIntegerValue) {
                        $new_image = str_replace('"id":' . $image_id, '"id":' . $new_image_id, $image);
                    } else {
                        $new_image = str_replace('"id":"' . $image_id . '"', '"id":"' . $new_image_id . '"', $image);
                    }
                    $new_image = str_replace(
                        '"url":"' . $image_url,
                        '"url":"' . str_replace('/', '\/', $new_image_url),
                        $new_image
                    );
                    $meta = str_replace($image, $new_image, $meta);
                }
            }
        }

        preg_match('/(?:"mf_form_id":")(.*?)(?:")/', $meta, $form_id);
        if (isset($form_id[1]) || !empty($form_id[1])) {
            $pieces = explode('*', $form_id[1]);
            $form_id = isset($pieces[0]) ? $pieces[0] : null;

            $new_form_id = $this->get_new_post_id($form_id);
            $meta = str_replace('"mf_form_id":"' . $form_id, '"mf_form_id":"' . $new_form_id, $meta);
        }

        return $this->replace_uicore_url($meta);
    }

    /**
     * Flush post data
     *
     * @param   Integer $post_id
     *
     * @return  String
     */
    private function maybe_flush_post($post_id)
    {
        if (class_exists('\Elementor\Core\Files\CSS\Post') && get_post_meta($post_id, '_elementor_version', true)) {
            $post_css_file = new \Elementor\Core\Files\CSS\Post($post_id);
            $post_css_file->update();
        }
    }

    private function replace_uicore_url($old_string, $slug=null)
    {
        $slug = $slug ? $slug : $this->slug;
        if ($this->theme === 'uicore-pro') {
            $is_gutenberg = get_option('uicore_is_gutenberg', null) === 'true' ? true : false;
            $path = $is_gutenberg ? 'gutenberg-templates/' : 'templates/';
            $base = 'https://uicore.pro/' . $path;
        } else {
            $base = 'https://' . $this->theme . '.uicore.co/';
        }
        $old_url = $base . $slug;
        $new_url = get_site_url();
        //replace the old url with the new one
        $new_string = \str_replace($old_url, $new_url, $old_string);
        //replace old escaped url
        $new_string = \str_replace(addcslashes($old_url, "/"), addcslashes($new_url, '/'), $new_string);
        
        if($is_gutenberg){
            //replace also cdn url
            $cdn_base = 'https://cdn.gtbg.uicore.pro/';
            $new_string = \str_replace($cdn_base, $new_url, $new_string);
            $new_string = \str_replace(addcslashes($cdn_base, "/"), addcslashes($new_url, '/'), $new_string);
        }

        return $new_string;
    }

    private function import_settings($new_settings)
    {
        $old_settings = Settings::current_settings();
        $keep_setings = [
            'scheme',
            'presets',
            'advanced_mode',
            'gen_maintenance_page',
            'gen_404',
            'purchase_info',
            'proxy',
            'admin_customizer',
            'theme_name',
            'admin_icon',
            'to_logo',
            'to_color',
            'to_content',
            'wp_background',
            'wp_form_background',
            'wp_logo',
            'performance_emojy',
            'performance_fa',
            'performance_block_style',
            'performance_eicon',
            'performance_animations',
            'performance_fonts',
            'performance_embed',
            'performance_preload_img',
            'performance_preload'
            // 'logoMobile',
            // 'logoSMobile',
        ];

        $new_settings = json_encode($new_settings, JSON_UNESCAPED_SLASHES);
        //fix assets url
        $new_settings = $this->update_url($new_settings);
        $new_settings = json_decode($new_settings, JSON_UNESCAPED_SLASHES);

        if ($new_settings != null) {
            foreach ($keep_setings as $key) {
                if(isset($old_settings[$key])){
                    $new_settings[$key] = $old_settings[$key];
                }
            }

            //update portfolio post id
            if(isset($new_settings['portfolio_page']['id']) && $new_settings['portfolio_page']['id']){
                $new_post = $this->get_new_post_id($new_settings['portfolio_page']['id']);
                $new_settings['portfolio_page'] = [
                    'id' => $new_post,
                    'name' => \get_the_title($new_post)
                ];
            }
            $new_settings = SettingsMigration::migrate($new_settings, true);
            //update settings, style and transients
            Settings::update_settings($new_settings);
            $new_settings = ThemeOptions::update_all($new_settings);
        }
        $this->response_data = $new_settings;
    }

    private function import_menu($menus, $slug)
    {
        if (!is_array($menus) && !is_object($menus)) {
            return;
        }
        foreach ($menus as $menu) {
            $menu_exists = wp_get_nav_menu_object($menu['name']);
            // If it doesn't exist, let's create it.
            if (!$menu_exists) {
                $menu_id = wp_create_nav_menu($menu['name']);

                $this->imported_menus[$menu_id] = $menu['id'];

                $items_ids = [];
                foreach ($menu['menu_items'] as $menuitem) {
                    $item_data = [
                        'menu-item-title' => $menuitem['menu-item-title'],
                        'menu-item-url' => $this->replace_uicore_url($menuitem['menu-item-url'], $slug),
                        'menu-item-position' => $menuitem['menu-item-position'],
                        'menu-item-type' => $menuitem['menu-item-type'],
                        'menu-item-status' => 'publish',
                        'menu-item-object' => $menuitem['menu-item-object'],
                        'menu-item-object-id' => 0,
                        'menu-item-parent-id' => (int) $menuitem['menu-item-menu_item_parent'],
                        'menu-item-description' => $menuitem['menu-item-description']
                    ];

                    if ($menuitem['menu-item-type'] != 'custom') {
                        unset($item_data['menu-item-url']);
                        $item_data['menu-item-object-id'] = (int) $this->get_new_post_id(
                            $menuitem['menu-item-object-id']
                        );
                    }
                    if (
                        $menuitem['menu-item-menu_item_parent'] != null &&
                        $menuitem['menu-item-menu_item_parent'] != '0'
                    ) {
                        foreach ($items_ids as $new_id => $old_id) {
                            if ($menuitem['menu-item-menu_item_parent'] == (int) $old_id) {
                                $item_data['menu-item-parent-id'] = (int) $new_id;
                                // break;
                            }
                        }
                    }
                    // \error_log(print_r($item_data, true));
                    $item_new_id = wp_update_nav_menu_item($menu_id, 0, $item_data);
                    if (!is_wp_error($item_new_id)) {
                        $items_ids[$item_new_id] = (int) $menuitem['menu-item-object-id'];
                    }

                    //V$ - Import menu items extras
                    if(isset($menuitem['extras']) && is_array($menuitem['extras'])){
                        foreach($menuitem['extras'] as $custom_prop => $value){
                            if($custom_prop == 'url'){
                                $value = $this->replace_uicore_url($value, $slug);
                            }
                            if($value){
                                update_post_meta( $item_new_id, '_menu_item_'.$custom_prop, sanitize_text_field($value) );
                            }
                        }
                    }

                    //End item loop
                }
                if ($menu['position'] !== null) {
                    $locations = get_theme_mod('nav_menu_locations');
                    $locations[$menu['position']] = $menu_id;
                    set_theme_mod('nav_menu_locations', $locations);
                }
            }
        }
    }

    private function import_sidebar($data, $assets = [])
    {
        global $wp_registered_sidebars;
        global $wp_registered_widget_controls;
        $widget_controls = $wp_registered_widget_controls;
        $available_widgets = [];

        foreach ($widget_controls as $widget) {
            // No duplicates.
            if (!empty($widget['id_base']) && !isset($available_widgets[$widget['id_base']])) {
                $available_widgets[$widget['id_base']]['id_base'] = $widget['id_base'];
                $available_widgets[$widget['id_base']]['name'] = $widget['name'];
            }
        }

        // Get all existing widget instances.
        $widget_instances = [];

        foreach ($available_widgets as $widget_data) {
            $widget_instances[$widget_data['id_base']] = get_option('widget_' . $widget_data['id_base']);
        }

        // Loop import data's sidebars.
        foreach ($data as $sidebar_id => $widgets) {
            // Check if sidebar is available on this site. Otherwise add widgets to inactive, and say so.

            if (isset($wp_registered_sidebars[$sidebar_id])) {
                $sidebar_available = true;
                $use_sidebar_id = $sidebar_id;
            } else {
                $sidebar_available = false;
                $use_sidebar_id = 'wp_inactive_widgets'; // Add to inactive if sidebar does not exist in theme.
            }

            // Loop widgets.
            foreach ($widgets as $widget_instance_id => $widget) {
                $fail = false;

                // Get id_base (remove -# from end) and instance ID number.
                $id_base = preg_replace('/-[0-9]+$/', '', $widget_instance_id);
                $instance_id_number = str_replace($id_base . '-', '', $widget_instance_id);

                // Does site support this widget?
                if (!$fail && !isset($available_widgets[$id_base])) {
                    $fail = true;
                }

                if (isset($widget['nav_menu'])) {
                    $old = $widget['nav_menu'];
                    foreach ($this->imported_menus as $new_id => $old_id) {
                        if ($old == $old_id) {
                            $widget['nav_menu'] = $new_id;
                        }
                    }
                }

                //replace the old urls
                $widget = $this->update_url($widget); //media urls
                $widget = $this->replace_uicore_url($widget); //links

                // Update uicore block widgets IDs
                foreach ($widget as $key => $value) {
                    if (is_string($value) && strpos($value, 'uicore-block') !== false) {
                        if (preg_match('/id="(.*?)"/', $value, $block_id) && !empty($block_id[1])) {
                            $block_id = explode('*', $block_id[1])[0] ?? null;
                            $new_block_id = $this->get_new_post_id($block_id);
                            if ($new_block_id) {
                                $widget[$key] = str_replace(' id="' . $block_id, ' id="' . $new_block_id, $value);
                            }
                        }
                    }
                }

                // Convert multidimensional objects to multidimensional arrays.
                // Some plugins like Jetpack Widget Visibility store settings as multidimensional arrays.
                // Without this, they are imported as objects and cause fatal error on Widgets page.
                // If this creates problems for plugins that do actually intend settings in objects then may need to consider other approach: https://wordpress.org/support/topic/problem-with-array-of-arrays.
                // It is probably much more likely that arrays are used than objects, however.
                $widget = json_decode(wp_json_encode($widget), true);

                // Does widget with identical settings already exist in same sidebar?
                if (!$fail && isset($widget_instances[$id_base])) {
                    // Get existing widgets in this sidebar.
                    $sidebars_widgets = get_option('sidebars_widgets');
                    $sidebar_widgets = isset($sidebars_widgets[$use_sidebar_id])
                        ? $sidebars_widgets[$use_sidebar_id]
                        : []; // Check Inactive if that's where will go.

                    //Clear old widgets
                    // update_option('sidebars_widgets', []);

                    // Loop widgets with ID base.
                    $single_widget_instances = !empty($widget_instances[$id_base]) ? $widget_instances[$id_base] : [];
                    foreach ($single_widget_instances as $check_id => $check_widget) {
                        // Is widget in same sidebar and has identical settings?
                        if (
                            in_array("$id_base-$check_id", $sidebar_widgets, true) &&
                            (array) $widget == $check_widget
                        ) {
                            $fail = true;
                            break;
                        }
                    }
                }

                // No failure.
                if (!$fail) {
                    // Add widget instance.
                    $single_widget_instances = get_option('widget_' . $id_base); // All instances for that widget ID base, get fresh every time.
                    $single_widget_instances = !empty($single_widget_instances)
                        ? $single_widget_instances
                        : ['_multiwidget' => 1]; // Start fresh if have to.
                    $single_widget_instances[] = $widget; // Add it.

                    // Get the key it was given.
                    end($single_widget_instances);
                    $new_instance_id_number = key($single_widget_instances);

                    // If key is 0, make it 1.
                    // When 0, an issue can occur where adding a widget causes data from other widget to load, and the widget doesn't stick (reload wipes it).
                    if ('0' === strval($new_instance_id_number)) {
                        $new_instance_id_number = 1;
                        $single_widget_instances[$new_instance_id_number] = $single_widget_instances[0];
                        unset($single_widget_instances[0]);
                    }

                    // Move _multiwidget to end of array for uniformity.
                    if (isset($single_widget_instances['_multiwidget'])) {
                        $multiwidget = $single_widget_instances['_multiwidget'];
                        unset($single_widget_instances['_multiwidget']);
                        $single_widget_instances['_multiwidget'] = $multiwidget;
                    }

                    // Update option with new widget.
                    update_option('widget_' . $id_base, $single_widget_instances);

                    // Assign widget instance to sidebar.
                    $sidebars_widgets = get_option('sidebars_widgets'); // Which sidebars have which widgets, get fresh every time.
                    if (!$sidebars_widgets) {
                        $sidebars_widgets = [];
                    }
                    $new_instance_id = $id_base . '-' . $new_instance_id_number; // Use ID number from new widget instance.
                    $sidebars_widgets[$use_sidebar_id][] = $new_instance_id; // Add new instance to sidebar.

                    update_option('sidebars_widgets', $sidebars_widgets); // Save the amended data.

                }
            }
            if(isset($assets[$use_sidebar_id])){
                $data = $assets[$use_sidebar_id];
                if(isset($data['styles']) && !empty($data['styles'])){
                   update_option('_uicore_widget_block_styles_' . $use_sidebar_id, $data['styles']);
                }
                if(isset($data['fonts']) && !empty($data['fonts'])){
                   update_option('_uicore_widget_block_fonts_' . $use_sidebar_id, $data['fonts']);
                }
            }
        }
    }

    function install_child($url ){
        $this->installTheme($url);
        $theme_name = wp_get_theme();
        $theme_name = str_replace('-child', '', $theme_name->get('TextDomain'));
        $child_theme = wp_get_theme( $theme_name.'-child' );
        if ( $child_theme->exists() ){
            switch_theme( $theme_name.'-child' );
            return;
        }
        $this->response_data = 'error';

    }
    function installTheme($url)
    {

        $current_theme = wp_get_theme();
        $current_theme_slug = $current_theme->get('TextDomain');
        $url = str_replace('/v1/uicore-pro', '/v1/'.$current_theme_slug , $url);

        include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
        include_once ABSPATH . 'wp-admin/includes/theme-install.php';
        require_once ABSPATH . 'wp-admin/includes/file.php';
        require_once ABSPATH . 'wp-admin/includes/misc.php';

        //Try first using copy (some hosts have issues with the temp files downloaded by upgrader ans dome have problem with copy function)
        $upload_dir = wp_upload_dir();
	    $file = $upload_dir['basedir'].'/uicore-theme-update.zip';
        if(function_exists('copy') && @copy($url,$file)){
            $url = $file;
        }
		if(!file_exists($url)){
			Helper::handle_connect('remove');
            $this->response_data = 'not-connected';
		}

        //get the plugin
        ob_start();

        $args = ["overwrite_package" => true ];
        $skin = new Quiet_Skin();
        $upgrader = new \Theme_Upgrader($skin);
        $result = $upgrader->install($url,$args);

        ob_clean();
        $this->response_data = $result;
    }
}

include_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
class Quiet_Skin extends \WP_Upgrader_Skin
{
    public function feedback($string, ...$args)
    {
        // just keep it quiet
    }
}

<?php
namespace UiCore\ThemeBuilder;
defined('ABSPATH') || exit();

use WP_Error;
use WP_REST_Response;

class Api
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'add_route']);
    }

    /**
     * Add routes
     */
    public function add_route()
    {
        register_rest_route('uicore/v1', 'specific-search/', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'get_specific'],
                'show_in_index' => false,
            ],
        ]);
        register_rest_route('uicore/v1', 'theme-builder/', [
            [
                'methods' => 'POST',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'cp_handle'],
                'show_in_index' => false,
            ],
        ]);
        register_rest_route('uicore/v1', 'theme-builder/(?P<id>\d+)', [
            [
                'methods' => 'GET',
                'permission_callback' => [$this, 'check_for_permission'],
                'callback' => [$this, 'cp_get_info'],
                'show_in_index' => false,
            ],
        ]);
    }

    public function check_for_permission()
    {
        return current_user_can('manage_options');
    }
 /**
     * Do Admin Utility functions from 'admin' API endpoint
     *
     * @param \WP_REST_Request $request
     * @return array Action Response
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_specific(\WP_REST_Request $request)
    {
        if ($request['term']) {
          
            $search_string = isset( $request['term'] ) ? sanitize_text_field( $request['term']) : '';
            $search_type   = isset( $request['type'] ) ? sanitize_text_field( $request['type'] ) : '';
            $data          = array();
            $result        = array();

            if($search_type != 'product info'){
                $args = array(
                    'public'   => true,
                    '_builtin' => false,
                );

                $output     = 'names'; // names or objects, note names is the default.
                $operator   = 'and'; // also supports 'or'.
                $post_types = get_post_types( $args, $output, $operator );

                unset( $post_types['elementor-hf'] ); //Exclude EHF templates.

                $post_types['Posts'] = 'post';
                $post_types['Pages'] = 'page';
            }else{
                $post_types = ['Products'=>'product'];
            }

            foreach ( $post_types as $key => $post_type ) {
                $data = array();

                add_filter( 'posts_search', array( $this, 'search_only_titles' ), 10, 2 );

                $query = new \WP_Query(
                    array(
                        's'              => $search_string,
                        'post_type'      => $post_type,
                        'posts_per_page' => - 1,
                        'suppress_filters' => true,
                    )
                );

                if ( $query->have_posts() ) {
                    while ( $query->have_posts() ) {
                        $query->the_post();
                        $title  = get_the_title();
                        $title .= ( 0 != $query->post->post_parent ) ? ' (' . get_the_title( $query->post->post_parent ) . ')' : '';
                        $id     = get_the_id();
                        $data[] = array(
                            'id'   => 'post-' . $id,
                            'text' => $title,
                        );
                    }
                }

                if ( is_array( $data ) && ! empty( $data ) ) {
                    $result[] = array(
                        'label'     => $key,
                        'value' => $data,
                    );
                }
            }

            $data = array();

            wp_reset_postdata();

            
            $args = array(
                'public' => true,
            );

            $output     = 'objects'; // names or objects, note names is the default.
            $operator   = 'and'; // also supports 'or'.
            $taxonomies = get_taxonomies( $args, $output, $operator );

            foreach ( $taxonomies as $taxonomy ) {
                //before continue check if is is_woocommerce_core_taxonomy
                if($search_type === 'product info' && !(strpos($taxonomy->name, 'pa_') === 0 || strpos($taxonomy->name, 'product_') === 0)){
                    continue;
                }

                $terms = get_terms(
                    $taxonomy->name,
                    array(
                        'orderby'    => 'count',
                        'hide_empty' => 0,
                        'name__like' => $search_string,
                    )
                );

                $data = array();

                $label = ucwords( $taxonomy->label );

                if($search_type === 'product info'){
                    $prefix = 'Products from ';
                }else{
                    $prefix = 'All singulars from ';
                }

                if ( ! empty( $terms ) ) {
                    foreach ( $terms as $term ) {
                        $term_taxonomy_name = ucfirst( str_replace( '_', ' ', $taxonomy->name ) );

                        //we need archive page for all taxonomies except for product info
                        if($search_type != 'product info'){
                            $data[] = array(
                                'id'   => 'tax-' . $term->term_id,
                                'text' => $term->name . ' archive page',
                            );
                        }

                        $data[] = array(
                            'id'   => 'tax-' . $term->term_id . '-single-' . $taxonomy->name,
                            'text' => $prefix . $term->name,
                        );
                    }
                }

                if ( is_array( $data ) && ! empty( $data ) ) {
                    $result[] = array(
                        'label'     => $label,
                        'value' => $data,
                    );
                }
            }
            $result[] = array(
                'label'     => 'test',
                'value' => $taxonomies,
            );
            // taxonomies

            // return the result in json.
            return $result;

        }

        return array();
    }

    
	/**
	 * Return search results only by post title.
	 * This is only run from hfe_get_posts_by_query()
	 *
	 * @param  (string)   $search   Search SQL for WHERE clause.
	 * @param  (WP_Query) $wp_query The current WP_Query object.
	 *
	 * @return (string) The Modified Search SQL for WHERE clause.
	 */
	function search_only_titles( $search, $wp_query ) {
		if ( ! empty( $search ) && ! empty( $wp_query->query_vars['search_terms'] ) ) {
			global $wpdb;

			$q = $wp_query->query_vars;
			$n = ! empty( $q['exact'] ) ? '' : '%';

			$search = array();

			foreach ( (array) $q['search_terms'] as $term ) {
				$search[] = $wpdb->prepare( "$wpdb->posts.post_title LIKE %s", $n . $wpdb->esc_like( $term ) . $n );
			}

			if ( ! is_user_logged_in() ) {
				$search[] = "$wpdb->posts.post_password = ''";
			}

			$search = ' AND ' . implode( ' AND ', $search );
		}

		return $search;
    }
    

     /**
     * Do Admin Utility functions from 'admin' API endpoint
     *
     * @param \WP_REST_Request $request
     * @return array Action Response
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function cp_handle(\WP_REST_Request $request)
    {
        if (isset($request['id'])) {
            $args = [
                'ID'=> $request['id'],
                'post_title' => $request['name'],
                'post_type' => 'uicore-tb',
                'post_status' => ($request['enabled'] == 'true') ? 'publish' : 'draft',
            ];
            $post = get_post($request['id']);

            //if post exists we need to keep the content
            if($post){
                $args['post_content'] = $post->post_content;
            }

            $post_id = wp_insert_post(\wp_slash($args));

            if (is_wp_error($post_id)) {

                return ['status'=> 'error', 'message'=> $post_id->get_error_message() ];
            }else{
                wp_set_post_terms($post_id, ($request['type'] === 'mega menu') ? '_type_' .'mm' : '_type_' .str_replace(' ','',$request['type']), 'tb_type');
                // wp_set_post_terms($post_id, 'tb_type', ($request['type'] === 'mega menu') ? '_type_' .'mm' : '_type_' .$request['type']);

                //Save Settings based on type (width or display rules)
                if($request['type'] === 'mega menu'){
                    $mm_settings = [
                        'width'=>$request['width'],
                        'widthCustom'=>$request['widthCustom'],
                    ];
                    update_post_meta($post_id, 'tb_settings', wp_slash($mm_settings));  
                }elseif($request['type'] === 'popup'){
                    update_post_meta($post_id, 'tb_rule_include', wp_slash($request['rule']['include']));
                    update_post_meta($post_id, 'tb_rule_exclude', wp_slash($request['rule']['exclude']));
                    update_post_meta($post_id, 'tb_settings', wp_slash($request['popupSettings']));  
                }elseif($request['type'] === 'product info'){
                    update_post_meta($post_id, 'tb_rule_include', wp_slash($request['rule']['include']));
                    update_post_meta($post_id, 'tb_rule_exclude', wp_slash($request['rule']['exclude']));
                    update_post_meta($post_id, 'tb_prod_settings', wp_slash($request['productSettings']));

                    if($request['productSettings']['display'] === 'popup'){
                        update_post_meta($post_id, 'tb_settings', wp_slash($request['popupSettings']));  
                    }

                }else{
                    if($request['type'] != 'block' || ($request['type'] == 'block' && isset($request['blockHook']['value']))){
                        $sett =['keep_default'=>$request["keepDefault"],'fixed'=>$request["fixed"]];
                        if($request['type'] == 'block' && isset($request['blockHook']['value'])){
                            $sett['blockHook'] = $request['blockHook'];
                        }
                        update_post_meta($post_id, 'tb_rule_include', wp_slash($request['rule']['include']));
                        update_post_meta($post_id, 'tb_rule_exclude', wp_slash($request['rule']['exclude']));
                        update_post_meta($post_id, 'tb_settings', \wp_slash($sett)); 
                    }else{
                        delete_post_meta($post_id, 'tb_rule_include');
                        delete_post_meta($post_id, 'tb_rule_exclude');
                        if($request['type'] == 'block' && isset($request['blockHook']['value'])){
                            $sett['blockHook'] = $request['blockHook'];
                            update_post_meta($post_id, 'tb_settings', \wp_slash($sett));
                        }else{
                            delete_post_meta($post_id, 'tb_settings'); 
                        }
                        
                    }
                }

                //wpml
                if (defined('ICL_SITEPRESS_VERSION')) {
                    global $sitepress;
                    $sitepress->set_element_language_details($post_id, 'post_uicore-tb', null, ICL_LANGUAGE_CODE);
                }

                //set the post status based on $request['enabled']

                
                return ['status'=>'success', 'id'=>$post_id];
            }
        }
        return ['status'=>'error'];
    }

     /**
     * Do Admin Utility functions from 'admin' API endpoint
     *
     * @param \WP_REST_Request $request
     * @return array Action Response
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function cp_get_info(\WP_REST_Request $request)
    {
        $post = get_post((int)$request['id']);
        $type = Common::get_the_type($post->ID);
        $settings = get_post_meta($post->ID, 'tb_settings', true);
        $include = get_post_meta($post->ID, 'tb_rule_include', true);
        $exclude = get_post_meta($post->ID, 'tb_rule_exclude', true);
        $response = array(
            'id'=>$post->ID,
            'name'=>$post->post_title,
            'type'=> $type,
            'rule'=> [
                'include'=>$include ? $include : array(['rule'=>'','specific'=>'']),
                'exclude'=>$exclude ? $exclude : []
            ],
            'width'=> isset($settings['width']) ? $settings['width'] : null,
            'fixed'=> isset($settings['fixed']) ? $settings['fixed'] : null,
            'widthCustom'=> isset($settings['widthCustom']) ? $settings['widthCustom'] : null,
            'keepDefault'=> isset($settings['keep_default']) ? $settings['keep_default'] : null,
            'blockHook'=> isset($settings['blockHook']) ? $settings['blockHook'] : null,
            'enabled'=> $post->post_status === 'publish' ? 'true' : 'false',
        );
        if($type === 'popup'){
            $response['popupSettings'] = $settings;
        }
        if($type === 'productinfo'){
            $response['productSettings'] = get_post_meta($post->ID, 'tb_prod_settings', true);
            if($response['productSettings']['display'] === 'popup'){
                $response['popupSettings'] = $settings;
            }
        }
        return $response;

    }

}
new Api();
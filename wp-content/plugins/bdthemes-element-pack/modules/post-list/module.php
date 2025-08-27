<?php
namespace ElementPack\Modules\PostList;

use ElementPack\Base\Element_Pack_Module_Base;
use ElementPack\Traits\Global_Terms_Query_Controls;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {
    use Global_Terms_Query_Controls;

	public function __construct() {
        parent::__construct();

        add_action('wp_ajax_bdt_post_list', [$this, 'bdt_post_list_callback']);
        add_action('wp_ajax_nopriv_bdt_post_list', [$this, 'bdt_post_list_callback']);
    }

	public function get_name() {
		return 'post-list';
	}

	public function get_widgets() {

		$widgets = [
			'Post_List',
		];
		
		return $widgets;
	}

	public function get_tab_output($output) {
        $tags = [
            'div'  => ['class' => [], 'data-separator' => [], 'id' => []],
            'a'    => ['href'  => [], 'target'      => [], 'class' => [], 'data-bdt-tooltip' => []],
            'span' => ['class' => [], 'style' => []],
            'i'    => ['class' => [], 'aria-hidden' => []],
            'img'  => ['src'   => [], 'class' => []],
            'h3'   => [
                'class' => []
            ],
        ];

        if (isset($output)) {
            echo wp_kses($output, $tags);
        }
    }

    function bdt_post_list_callback() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'element-pack-site')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
            exit;
        }

        $settings = $_POST['settings'] ?? [];

        // Restrict Allowed Post Types
        $allowed_post_types = ['post', 'page', 'campaign', 'lightbox_library', 'tribe_events', 'product', 'portfolio', 'faq', 'bdthemes-testimonial', 'bdthemes-testimonial', 'knowledge_base'];
        $post_type = isset($_POST['post_type']) ? sanitize_text_field($_POST['post_type']) : 'post';

        if (!in_array($post_type, $allowed_post_types)) {
            wp_send_json_error(['message' => 'Invalid post type'], 403);
            exit;
        }

        // Restrict posts_per_page to Prevent DoS
        $posts_per_page = isset($settings['posts_per_page']) ? intval($settings['posts_per_page']) : 6;
        $posts_per_page = min($posts_per_page, 50); // Max 50
    
        $category_slug = sanitize_text_field($_POST['category']);
    
        // Visibility
        $show_title = isset($_POST['showHide']['show_title']) ? sanitize_text_field($_POST['showHide']['show_title']) : '';
        $show_category = isset($_POST['showHide']['show_category']) ? sanitize_text_field($_POST['showHide']['show_category']) : '';
        $show_image = isset($_POST['showHide']['show_image']) ? sanitize_text_field($_POST['showHide']['show_image']) : '';
        $icon = isset($_POST['showHide']['icon']) ? sanitize_text_field($_POST['showHide']['icon']) : '';
        $show_date = isset($_POST['showHide']['show_date']) ? sanitize_text_field($_POST['showHide']['show_date']) : '';
        $bdt_link_new_tab = isset($_POST['showHide']['bdt_link_new_tab']) ? sanitize_text_field($_POST['showHide']['bdt_link_new_tab']) : '';
        $human_diff_time = isset($_POST['showHide']['human_diff_time']) ? sanitize_text_field($_POST['showHide']['human_diff_time']) : '';
        $human_diff_time_short = isset($_POST['showHide']['human_diff_time_short']) ? sanitize_text_field($_POST['showHide']['human_diff_time_short']) : '';
    
        // Settings
        $taxonomy = sanitize_text_field($settings['taxonomy']);
        $order = sanitize_text_field($settings['order']);
        $orderby = sanitize_text_field($settings['orderby']);
    
        // Create a unique transient key
        // $transient_key = 'bdt_post_list_' . md5(serialize([$category_slug, $post_type, $order, $orderby, $posts_per_page]));

        // Try to get cached response
        // $response = get_transient($transient_key);
    
        // If no cached response, proceed with the query and cache it
        // if (false === $response) {
            $ajaxposts = $this->bdt_get_posts_by_ajax($post_type, $order, $orderby, $posts_per_page, $taxonomy, $category_slug);
            $response = '';
    
            if ($ajaxposts->have_posts()) {
                $item_index = 1;
                while ($ajaxposts->have_posts()) : 
                    if ($item_index > $posts_per_page) {
                        break;
                    }
                    $ajaxposts->the_post();
    
                    $post_link = get_permalink();
                    $image_src = wp_get_attachment_image_url(get_post_thumbnail_id(), 'full');
                    $category = element_pack_get_category_list($post_type, ', ');
                    
                    if ($human_diff_time == 'yes') {
                        $date = element_pack_post_time_diff(($human_diff_time_short == 'yes') ? 'short' : '');
                    } else {
                        $date = get_the_date();
                    }
    
                    $placeholder_image_src = \Elementor\Utils::get_placeholder_image_src();
                    $image_src = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');
                    if (!$image_src) {
                        $image_src = $placeholder_image_src;
                    } else {
                        $image_src = $image_src[0];
                    }

                    if ($bdt_link_new_tab == 'yes') {
                        $target = '_blank';
                    } else {
                        $target = '_self';
                    }
    
                    // Output structure for each post
                    $response .= '<div class="bdt-item-wrap bdt-flex">';
                    $response .= '<div class="bdt-item bdt-flex bdt-flex-middle">';
    
                    if ($icon) {
                        $response .= '<div class="bdt-list-icon">';
                        $response .= '<i class="'. esc_attr($icon) .'"></i>';
                        $response .= '</div>';
                    }
    
                    if ('yes' == $show_image) {
                        $response .= '<div class="bdt-image bdt-flex">';
                        $response .= '<a href="' . esc_url($post_link) . '"><img src="' . esc_url($image_src) . '" alt="' . get_the_title() . '"></a>';
                        $response .= '</div>';
                    }
    
                    $response .= '<div class="bdt-content">';
    
                    if ('yes' == $show_title) {
                        $response .= '<h3 class="bdt-title"><a href="' . esc_url($post_link) . '" class="bdt-link" target="'. $target .'">' . get_the_title() . '</a></h3>';
                    }
    
                    if ('yes' == $show_category || 'yes' == $show_date) {
                        $response .= '<div class="bdt-meta bdt-subnav bdt-flex-middle">';
                        if ($show_date == 'yes') {
                            $response .= '<span class="bdt-date">' . $date . '</span>';
                        }
                        if ($show_category == 'yes') {
                            $response .= '<span class="bdt-category">' . $category . '</span>';
                        }
                        $response .= '</div>';
                    }
    
                    $response .= '</div>';
                    $response .= '</div>';
                    $response .= '</div>';
    
                    $item_index++;
                endwhile;
    
                // Set the transient with the generated response
                // set_transient($transient_key, $response, 6 * HOUR_IN_SECONDS); // Cache for 6 hours
            } else {
                $response = 'empty';
            }
    
            wp_reset_postdata();
        // }
    
        $this->get_tab_output($response);
        exit();
    }
    
}

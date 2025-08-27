<?php
namespace ElementPack\Modules\PostGallery;

use ElementPack\Base\Element_Pack_Module_Base;
use ElementPack\Traits\Global_Terms_Query_Controls;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {
    use Global_Terms_Query_Controls;

	public function __construct() {
        parent::__construct();

        add_action('wp_ajax_bdt_post_gallery', [$this, 'bdt_post_gallery_callback']);
        add_action('wp_ajax_nopriv_bdt_post_gallery', [$this, 'bdt_post_gallery_callback']);
    }

	public function get_name() {
		return 'post-gallery';
	}

	public function get_widgets() {

		$widgets = [
			'Post_Gallery',
		];
		
		return $widgets;
	}

	public function get_tab_output($output) {
        $tags = [
            'div'  => ['class' => [], 'data-separator' => [], 'id' => [], 'data-tilt' => [], 'data-tilt-scale' => []],
            'a'    => ['href'  => [], 'target'      => [], 'class' => [], 'data-elementor-open-lightbox' => []],
            'span' => ['class' => [], 'style' => []],
            'i'    => ['class' => [], 'aria-hidden' => []],
            'img'  => ['src'   => [], 'class' => []],
            'h1'   => ['class' => []],
            'h2'   => ['class' => []],
            'h3'   => ['class' => []],
            'h4'   => ['class' => []],
            'h5'   => ['class' => []],
            'h6'   => ['class' => []],
        ];

        if (isset($output)) {
            echo wp_kses($output, $tags);
        }
    }

    function bdt_get_title($show_title, $title_tag, $show_title_link, $target) {
        if ('yes' === $show_title) {
            $title_link = ('yes' === $show_title_link) ? get_permalink() : '';
        
            return sprintf(
                '<a class="bdt-post-gallery-title-link" href="%s" %s>
                    <%s class="bdt-gallery-item-title bdt-margin-remove">%s</%s>
                </a>',
                esc_url($title_link),
                esc_attr($target),
                esc_attr($title_tag),
                esc_html(get_the_title()),
                esc_attr($title_tag)
            );
        }
    }

    function bdt_get_excerpt($show_excerpt, $excerpt_limit, $strip_shortcode) {

        if ('yes' === $show_excerpt) {
            $excerpt = has_excerpt() ? get_the_excerpt() : element_pack_custom_excerpt($excerpt_limit, $strip_shortcode);
        
            if (!empty($excerpt)) {
                return sprintf(
                    '<div class="bdt-post-gallery-excerpt">%s</div>',
                    wp_kses_post($excerpt)
                );
            }
        }
    }

    function bdt_get_category($show_category, $post_type) {
        if ('yes' === $show_category) {
            global $post;
        
            $tags = get_the_terms($post->ID, $post_type . '_tag');
        
            if (!empty($tags) && !is_wp_error($tags)) {
                $separator = '<span class="bdt-gallery-item-tag-separator"></span>';
                $tags_array = array_map(function($tag) {
                    return '<span class="bdt-gallery-item-tag">' . esc_html($tag->name) . '</span>';
                }, $tags);
        
                return sprintf(
                    '<div class="bdt-gallery-item-tags">%s</div>',
                    wp_kses_post(implode($separator, $tags_array))
                );
            }
        }
    }

    function bdt_get_link($show_link, $image_src, $lightbox_link_text, $post_link_text, $link_type, $target) {
        if ('none' !== $show_link) {
            $response = '';
            $response .= '<div class="bdt-flex-inline bdt-gallery-item-link-wrapper">';
        
            // Lightbox link
            if ('lightbox' === $show_link || 'both' === $show_link) {
                $response .= '<a class="bdt-gallery-item-link bdt-gallery-lightbox-item ' . ( 'icon' == $link_type ? 'bdt-link-icon' : 'bdt-link-text' ) . '" data-elementor-open-lightbox="no" data-caption="' . wp_kses_post(get_the_title()) . '" href="' . esc_url($image_src) . '">';
                if ('icon' === $link_type) {
                    $response .= '<i class="ep-icon-search" aria-hidden="true"></i>';
                } elseif ('text' === $link_type && !empty($lightbox_link_text)) {
                    $response .= '<span>' . esc_html($lightbox_link_text) . '</span>';
                }
                $response .= '</a>';
            }
        
            // Post link
            if ('post' === $show_link || 'both' === $show_link) {
                $link_type_class = ('icon' === $link_type) ? ' bdt-link-icon' : ' bdt-link-text';
        
                $response .= '<a class="bdt-gallery-item-link' . esc_attr($link_type_class) . '" href="' . esc_url(get_permalink()) . '" ' . esc_attr($target) . '>';
                if ('icon' === $link_type) {
                    $response .= '<i class="ep-icon-link" aria-hidden="true"></i>';
                } elseif ('text' === $link_type && !empty($post_link_text)) {
                    $response .= '<span>' . esc_html($post_link_text) . '</span>';
                }
                $response .= '</a>';
            }
        
            $response .= '</div>';

            return $response;
        }
    }

    function bdt_get_image($show_image, $image_src) {
        if (!$show_image || empty($image_src)) {
            return '';
        }
    
        return sprintf(
            '<div class="bdt-gallery-thumbnail">
                <img src="%s" alt="%s" />
            </div>',
            esc_url($image_src),
            esc_attr(get_the_title())
        );
    }

    function bdt_post_gallery_callback() {
        if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'element-pack-site')) {
            wp_send_json_error(['message' => 'Security check failed'], 403);
            exit;
        }

        $settings = $_POST['settings'] ?? [];

        // Restrict Allowed Post Types
        $allowed_post_types = ['post', 'page', 'campaign', 'lightbox_library', 'tribe_events', 'product', 'portfolio', 'faq', 'bdthemes-testimonial', 'bdthemes-testimonial', 'knowledge_base'];
        $post_type = isset($settings['post-type']) ? sanitize_text_field($settings['post-type']) : 'post';

        if (!in_array($post_type, $allowed_post_types)) {
            wp_send_json_error(['message' => 'Invalid post type'], 403);
            exit;
        }

        // Restrict posts_per_page to Prevent DoS
        $posts_per_page = isset($settings['posts_per_page']) ? intval($settings['posts_per_page']) : 6;
        $posts_per_page = min($posts_per_page, 50); // Max 50

        $category_slug = sanitize_text_field($_POST['category']);
        $_skin = sanitize_text_field($_POST['_skin']);

        $defaults = [
            'show_title'          => '',
            'title_tag'           => '',
            'show_title_link'     => '',
            'show_excerpt'        => '',
            'excerpt_limit'       => '',
            'strip_shortcode'     => '',
            'show_category'       => '',
            'show_link'           => '',
            'external_link'       => '',
            'lightbox_link_text'  => '',
            'post_link_text'      => '',
            'link_type'           => '',
            'tilt_show'            => '',
            'tilt_scale'           => '',
            'show_image'          => '',
            'columns'             => '',
            'columns_tablet'      => '',
            'columns_mobile'      => '',
            'overlay_animation'   => '',
        ];
        
        foreach ($defaults as $key => $default) {
            $$key = isset($settings[$key]) ? sanitize_text_field($settings[$key]) : $default;
        }

        // Sanitize settings   
        $taxonomy       = sanitize_text_field($settings['taxonomy'] ?? '');
        $order          = sanitize_text_field($settings['order'] ?? '');
        $orderby        = sanitize_text_field($settings['orderby'] ?? '');
        
        $ajaxposts = $this->bdt_get_posts_by_ajax($post_type, $order, $orderby, $posts_per_page, $taxonomy, $category_slug);
        $response = '';

        if ($ajaxposts->have_posts()) {
            $item_index = 1;
            
            while ($ajaxposts->have_posts()) : 
                if ($item_index > $posts_per_page) {
                    break;
                }

                $ajaxposts->the_post();
                $image_src = wp_get_attachment_image_url(get_post_thumbnail_id(), 'full');
                $placeholder_image_src = \Elementor\Utils::get_placeholder_image_src();
                $image_src = wp_get_attachment_image_src(get_post_thumbnail_id(), 'large');

                if (!$image_src) {
                    $image_src = $placeholder_image_src;
                } else {
                    $image_src = $image_src[0];
                }

                $target = ($external_link) ? 'target=_blank' : '';

                
                if ( $tilt_show ) {
                    $data_tilt = ' data-tilt';
                    if ( 'true' === $tilt_scale ) {
                        $data_tilt .= ' data-tilt-scale="1.2"';
                    }
                } else {
                    $data_tilt = '';
                }

                if ('abetis' === $_skin || 'fedara' === $_skin) {
                    $response .= '<div class="bdt-gallery-item bdt-transition-toggle bdt-width-1-'.$columns_mobile.' bdt-width-1-'.$columns.'@m bdt-width-1-'.$columns_tablet.'@s">';
                    $response .=    '<div ' . $data_tilt . '>';
                    $response .=        '<div class="bdt-post-gallery-inner">';
                    $response .=            $this->bdt_get_image($show_image, $image_src);
                    $response .=            '<div class="bdt-position-cover bdt-overlay bdt-overlay-default bdt-transition-' . $overlay_animation . '">';
                    $response .=                '<div class="bdt-post-gallery-content">';
                    $response .=                    '<div class="bdt-gallery-content-inner">';
                    $response .=                        $this->bdt_get_link($show_link, $image_src, $lightbox_link_text, $post_link_text, $link_type, $target);
                    $response .=                    '</div>'; 
                    $response .=                '</div>'; 
                    $response .=            '</div>'; 
                    $response .=        '</div>'; 
                    $response .=        '<div class="bdt-post-gallery-skin-'.$_skin.'-desc bdt-padding-small">';
                    $response .=            $this->bdt_get_title($show_title, $title_tag, $show_title_link, $target);
                    $response .=            $this->bdt_get_excerpt($show_excerpt, $excerpt_limit, $strip_shortcode);
                    $response .=            $this->bdt_get_category($show_category, $post_type);
                    $response .=        '</div>';
                    $response .=    '</div>'; 
                    $response .= '</div>'; 
                } else if ('trosia' === $_skin) {
                    $response .= '<div class="bdt-gallery-item bdt-transition-toggle bdt-width-1-'.$columns_mobile.' bdt-width-1-'.$columns.'@m bdt-width-1-'.$columns_tablet.'@s">';
                    $response .=    '<div class="bdt-post-gallery-inner" ' . $data_tilt . '>';
                    $response .=        $this->bdt_get_image($show_image, $image_src);
                    $response .=        '<div class="bdt-position-top-right bdt-margin bdt-margin-right">';
                    $response .=            '<div class="bdt-post-gallery-content">';
                    $response .=               '<div class="bdt-gallery-content-inner bdt-transition-fade">';
                    $response .=                    $this->bdt_get_link($show_link, $image_src, $lightbox_link_text, $post_link_text, $link_type, $target);
                    $response .=                '</div>'; 
                    $response .=            '</div>'; 
                    $response .=        '</div>'; 

                    $response .=        '<div class="bdt-post-gallery-desc bdt-text-left bdt-position-z-index bdt-position-bottom">';
                    $response .=            '<div class="bdt-post-gallery-content">';
                    $response .=                '<div class="bdt-gallery-content-inner">';
                    $response .=                    $this->bdt_get_title($show_title, $title_tag, $show_title_link, $target);
                    $response .=                    $this->bdt_get_excerpt($show_excerpt, $excerpt_limit, $strip_shortcode);
                    $response .=                '</div>';
                    $response .=            '</div>';
                    $response .=        '</div>';
                    
                    $response .=        '<div class="bdt-position-top-left">';
                    $response .=             $this->bdt_get_category($show_category, $post_type);                   
                    $response .=        '</div>';
                    $response .=    '</div>'; 
                    $response .= '</div>';
                } else {
                    $response .= '<div class="bdt-gallery-item bdt-transition-toggle bdt-width-1-'.$columns_mobile.' bdt-width-1-'.$columns.'@m bdt-width-1-'.$columns_tablet.'@s">';
                    $response .=    '<div class="bdt-post-gallery-inner" ' . $data_tilt . '>';
                    $response .=        $this->bdt_get_image($show_image, $image_src);
                    $response .=        '<div class="bdt-position-cover bdt-overlay bdt-overlay-default bdt-transition-' . $overlay_animation . '">';
                    $response .=            '<div class="bdt-post-gallery-content">';
                    $response .=                '<div class="bdt-gallery-content-inner">';
                    $response .=                    $this->bdt_get_title($show_title, $title_tag, $show_title_link, $target);
                    $response .=                    $this->bdt_get_excerpt($show_excerpt, $excerpt_limit, $strip_shortcode);
                    $response .=                    $this->bdt_get_category($show_category, $post_type);
                    $response .=                    $this->bdt_get_link($show_link, $image_src, $lightbox_link_text, $post_link_text, $link_type, $target);
                    $response .=                '</div>'; 
                    $response .=            '</div>'; 
                    $response .=        '</div>'; 
                    $response .=    '</div>'; 
                    $response .= '</div>'; 
                }

                $item_index++;
            endwhile;

        } else {
            $response = 'empty';
        }

        wp_reset_postdata();
    
        $this->get_tab_output($response);
        exit();
    }
    
}

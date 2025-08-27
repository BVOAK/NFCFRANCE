<?php

namespace ElementPack\Includes\Builder;

use Elementor\Widget_Base;



if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

abstract class Builder_Widget_Base extends Widget_Base {

    public $__temp_query = null;
    public $__product_data = false;


    /**
     * Check if we currently in Elementor mode
     *
     * @return void
     */
    public function in_elementor() {
        $result = false;

        if (wp_doing_ajax()) {
            $result = $this->is_elementor_ajax;
        } elseif (
            \Elementor\Plugin::instance()->editor->is_edit_mode()
            || \Elementor\Plugin::instance()->preview->is_preview_mode()
        ) {
            $result = true;
        }

        return apply_filters('bdthemes-templates-builder/in-elementor', $result);
    }

    protected function is_ultimate_builder_editor() {

        if ( !wp_doing_ajax()) return;
        // if (!$this->in_elementor() && !wp_doing_ajax()) return;
        if (get_post_type() !== Meta::POST_TYPE) return;

        return true;
    }


    /**
     * Set editor data for ajax save only
     */
    public function bdt_set_single_post_preview_data() {

        if (!$this->is_ultimate_builder_editor()) {
            return;
        }

        global $post;

        $templateId = get_transient('bdthemes_builder_template_id_' . get_current_user_id());
        $posts      = get_transient('bdthemes_builder_template_sample_post_' . get_current_user_id());

        if ($posts instanceof \WP_Query && $posts->have_posts() && $templateId == $post->ID) {
            foreach ($posts->posts as $post) {
                $GLOBALS['post'] = $post;
                setup_postdata($post);
            }
        }
    }
}

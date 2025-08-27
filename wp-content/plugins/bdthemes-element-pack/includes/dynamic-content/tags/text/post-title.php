<?php

use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;

if (! defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

/**
 * Element Pack Dynamic Tag - Post Title
 *
 * Elementor dynamic tag that returns post title with advanced options
 *
 * @since 1.0.0
 */
class ElementPack_Dynamic_Tag_Post_Title extends \Elementor\Core\DynamicTags\Tag
{
    use ElementPack\Includes\Traits\UtilsTrait;

    /**
     * Get dynamic tag name.
     *
     * Retrieve the name of the server variable tag.
     *
     * @since 1.0.0
     * @access public
     * @return string Dynamic tag name.
     */
    public function get_name(): string
    {
        return 'element-pack-post-title';
    }

    /**
     * Get dynamic tag title.
     *
     * Returns the title of the server variable tag.
     *
     * @since 1.0.0
     * @access public
     * @return string Dynamic tag title.
     */
    public function get_title(): string
    {
        return esc_html__('Post Title', 'bdthemes-element-pack');
    }

    /**
     * Get dynamic tag groups.
     *
     * Retrieve the list of groups the server variable tag belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Dynamic tag groups.
     */
    public function get_group(): array
    {
        return ['element-pack-post'];
    }

    /**
     * Get dynamic tag categories.
     *
     * Retrieve the list of categories the server variable tag belongs to.
     *
     * @since 1.0.0
     * @access public
     * @return array Dynamic tag categories.
     */
    public function get_categories(): array
    {
        return [
            \Elementor\Modules\DynamicTags\Module::TEXT_CATEGORY,
        ];
    }

    public function is_settings_required() {
		return true;
	}

    protected function register_controls(): void
    {
        $this->common_post_controls();
    }

    protected function register_advanced_section()
    {
        $this->advanced_controls();
    }
    /**
     * Render tag output on the frontend.
     *
     * Written in PHP and used to generate the final HTML.
     *
     * @since 1.0.0
     * @access public
     * @return void
     */
    public function render(): void
    {
        $value = '';

        $post_id = $this->get_post_id();

        // If we have a valid post ID, get the title; otherwise, fallback to an empty value
        if ($post_id) {
            $value = get_the_title($post_id);
        }

        // Output the sanitized value
        echo wp_kses_post($this->apply_word_limit($value));
    }
}

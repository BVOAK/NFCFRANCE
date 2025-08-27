<?php

namespace UiCoreElements;

use Elementor\Plugin;

defined('ABSPATH') || exit();
/**
 * UiCore Utils Functions
 */
class Helper
{

    public static function get_separator()
    {
        return '<span class="uicore-meta-separator"></span>';
    }

    public static function get_taxonomies($custom = true, $only_products = false)
    {

        // Woo only taxonomies
        if ($only_products) {
            $taxonomies = [
                'product_cat' => 'Product Categories',
                'product_tag' => 'Product Tags'
            ];

            $attributes = wc_get_attribute_taxonomies();
            foreach ($attributes as $att) {
                $taxonomies['pa_' . $att->attribute_name] = $att->attribute_label;
            }

            // All taxonomies
        } else {

            $taxonomies = get_taxonomies(['public' => true], 'objects');
            $exclusions = ['nav_menu', 'link_category', 'post_format']; // Exclude these taxonomies from the list

            $taxonomies = array_filter($taxonomies, function ($taxonomy) use ($exclusions) {
                return !in_array($taxonomy->name, $exclusions);
            });

            $taxonomies = array_map(function ($taxonomy) {
                if ('portfolio_category' === $taxonomy->name) {
                    return 'Portfolio Category';
                }
                return $taxonomy->label;
            }, $taxonomies);
        }

        if ($custom) {
            $taxonomies = array_merge(['custom' => __('Custom Meta', 'uicore-elements')], $taxonomies);
        }

        return $taxonomies;
    }

    static function get_taxonomy($name)
    {
        global $post;
        $categories = get_the_terms($post->ID, $name);

        if (! $categories || is_wp_error($categories)) {
            return false;
        }

        $categories = array_values($categories);
        foreach ($categories as $t) {
            $term_name[] =
                '<a href="' . get_term_link($t) . '" title="View ' . \esc_attr($t->name) . ' posts">' . esc_html($t->name) . '</a>';
        }
        $category = implode(', ', $term_name);

        return $category;
    }

    static function get_custom_meta($name)
    {
        global $post;

        $meta = get_post_meta($post->ID, $name, true);

        // If is an array means meta is not valid since we are expecting a single value string
        if (! $meta || is_array($meta)) {
            return false;
        }

        return $meta;
    }

    static function get_reading_time()
    {
        global $post;

        $the_content = $post->post_content;
        $words = str_word_count(wp_strip_all_tags($the_content)); // count the number of words
        $minute = floor($words / 200); // rounding off and deviding per 200 words per minute

        return $minute;
    }

    static function get_site_domain()
    {
        return str_ireplace('www.', '', parse_url(home_url(), PHP_URL_HOST));
    }

    /**
     * Returns the Uicore Elements settings page URL. You may pass a message so it'll be wrapped under a <a> tag.
     *
     * @param string $message Optional. A clickable message to be displayed that'll redirect, in a new tab, to settings page.
     *
     * @return string Uicore Elements settings page URL or an <a> tag HTML with the url and the passed message.
     */
    static function get_admin_settings_url(string $message = '')
    {
        $url = admin_url('options-general.php?page=uicore-elements');
        return !empty($message)
            ? '<a href="' . esc_url($url) . '" target="_blank">' . esc_html($message) . '</a>'
            : $url;
    }

    static function register_widget_style($name, $deps = [], $external = false)
    {
        $handle = (!$external ? 'ui-e-' : '') . $name;
        wp_register_style($handle, UICORE_ELEMENTS_ASSETS . '/css/elements/' . $name . '.css', $deps, UICORE_ELEMENTS_VERSION);
        return $handle;
    }
    static function register_widget_script($name, $deps = [], $external = false)
    {
        $handle = (!$external ? 'ui-e-' : '') . $name;
        //if name contains / then we need to set a custom path
        if (strpos($name, '/') !== false) {
            $path = '';
        } else {
            $path = 'elements/';
        }
        wp_register_script($handle, UICORE_ELEMENTS_ASSETS . '/js/' . $path . $name . '.js', $deps, UICORE_ELEMENTS_VERSION, true);
        return $handle;
    }

    /**
     * Build the related posts query in REST API context.
     *
     * @param int $page_number The current page number for pagination.
     * @param int $per_page The number of posts to retrieve per page.
     * @param string/int $pageID The current post/page ID.
     *
     * @return \WP_Query Returns a WP_Query object.
     */
    public static function get_related_ajax(int $page_number, int $per_page, $pageID)
    {
        $args = [
            'post__not_in' => [$pageID],
            'posts_per_page' => $per_page,
            'paged' => $page_number,
        ];

        $categories = get_the_category($pageID);
        $tags = wp_get_post_tags($pageID);

        $tax_query = [];

        if ($categories) {
            $category_ids = array_map(fn($cat) => $cat->term_id, $categories);
            $tax_query[] = [
                'taxonomy' => 'category',
                'field'    => 'term_id',
                'terms'    => $category_ids,
                'operator' => 'IN',
            ];
        }

        if ($tags) {
            $tag_ids = array_map(fn($tag) => $tag->term_id, $tags);
            $tax_query[] = [
                'taxonomy' => 'post_tag',
                'field'    => 'term_id',
                'terms'    => $tag_ids,
                'operator' => 'IN',
            ];
        }

        if (!empty($tax_query)) {
            $args['tax_query'] = [
                'relation' => 'OR',
                ...$tax_query,
            ];
        }

        return new \WP_Query($args);
    }

    /**
     * Returns an array of related products IDs.
     */
    public static function get_product_related($limit, $ID)
    {
        if (empty($ID)) {
            global $post;
            $ID = $post->ID;
        }

        return wc_get_related_products($ID, $limit);
    }

    /*
    * Get the current post id (used in Theme Builder - UiCore Framework)
    */
    static function get_current_meta_id()
    {
        if (\class_exists('\UiCore\Blog\Frontend') && \UiCore\Blog\Frontend::is_blog() && !is_singular('post')) {
            $post_id = get_option('page_for_posts', true);
        } elseif (\class_exists('\UiCore\Portfolio\Frontend') && \UiCore\Portfolio\Frontend::is_portfolio() && !is_singular('portfolio')) {
            $post_id = \UiCore\Portfolio\Frontend::get_portfolio_page_id();
        } else {
            $post_id = get_queried_object_id();
        }

        return $post_id;
    }

    /**
     * Return a list of textual html tags for Elementor Controls Options
     */
    public static function get_title_tags()
    {

        $tags = [
            'h1'   => 'H1',
            'h2'   => 'H2',
            'h3'   => 'H3',
            'h4'   => 'H4',
            'h5'   => 'H5',
            'h6'   => 'H6',
            'div'  => 'div',
            'span' => 'span',
            'p'    => 'p',
        ];

        return $tags;
    }


    /**
     * Formats a date meta value according to the specified format.
     *
     * @param mixed  $date    The date value to format.
     * @param string $format  The format to use for formatting the date. Can be 'custom' or a predefined format.
     * @param string $custom  The custom format to use if $format is set to 'custom'.
     *
     * @return string  The formatted date value.
     */
    public static function format_date($date, $format, $custom)
    {

        if ('custom' === $format) {
            $date_format = $custom;
        } else if ('default' === $format) {
            $date_format = get_option('date_format');
        } else {
            $date_format = $format;
        }

        $value = date_i18n($date_format, $date);

        return wp_kses_post($value);
    }

    /**
     * Sanitizes HTML tags. Since is a custom sanitizer, require us, by wp plugin repo security standards,
     * to add `//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` comment.
     *
     * @param mixed  $raw_tag  The HTML tag to sanitize.
     * @param string $default  Default HTML value to fallback to in case an invalid tag is passed. Default is 'h3'.
     * @return string  The sanitized HTML tag.
     *
     * @since 1.3.5
     */
    public static function esc_tag($raw_tag, $default = 'h3')
    {

        $allowed_tags_names = ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'span', 'p', 'a'];
        $tag = sanitize_key(strtolower($raw_tag)); // normalizes to [a-z0-9_]

        // fallback if tag not whitelisted
        if (! in_array($tag, $allowed_tags_names, true)) {
            $tag = $default;
        }

        return $tag;
    }

    /**
     * Sanitizes SVG content, also allowing `post` tags and atts. Since is a custom sanitizer, require us,
     * by wp plugin repo security standards, to add `//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` comment.
     *
     * @param string $svg The raw SVG content to be sanitized.
     * @return string The sanitized SVG content, with only allowed tags and attributes.
     *
     * @since 1.0.2
     */
    public static function esc_svg($svg)
    {
        $default = wp_kses_allowed_html('post');

        $args = array(
            'svg'   => array(
                'class' => true,
                'aria-hidden' => true,
                'aria-labelledby' => true,
                'role' => true,
                'xmlns' => true,
                'width' => true,
                'height' => true,
                // has to be lowercase
                'viewbox' => true,
                'preserveaspectratio' => true
            ),
            'g'     => array('fill' => true),
            'title' => array('title' => true),
            'path'  => array(
                'd'               => true,
                'fill'            => true
            )
        );
        $allowed_tags = array_merge($default, $args);

        return wp_kses($svg, $allowed_tags);
    }

    /**
     * Sanitizes text strings, but allowing some html tags usefull for styling and manipulating texts. Since is a custom sanitizer,
     * require us, by wp plugin repo security standards, to add `//phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped` comment.
     *
     * @param string $content The content to be sanitized
     * @return string The sanitized string content.
     *
     * @since 1.0.3
     */
    public static function esc_string($content)
    {

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
            'br' => array(),
            'a' => [
                'href' => true,
                'title' => true,
                'target' => true,
                'rel' => true
            ]
        ];

        return wp_kses($content, $allowed_tags);
    }

    /**
     * Retrieves the available image sizes.
     *
     * @return array An array of image sizes.
     *
     * @since 1.0.0
     */
    public static function get_images_sizes()
    {
        $sizes = [];
        foreach (get_intermediate_image_sizes() as $size) {
            $sizes[$size] = $size;
        }
        return $sizes;
    }

    /**
     * @since 1.0.5
     */
    private static function get_element_recursive($elements, $form_id)
    {

        foreach ($elements as $element) {
            if ($form_id === $element['id']) {
                return $element;
            }

            if (!empty($element['elements'])) {
                $element = self::get_element_recursive($element['elements'], $form_id);

                if ($element) {
                    return $element;
                }
            }
        }

        return false;
    }

    /**
     * Retrieves the settings of a specific widget without relying on transient data.
     *
     * @param int $post_id The ID of the post or page.
     * @param int $widget_id The ID of the widget.
     * @return array|string The settings of the widget, or an error message if the request is invalid.
     *
     * @since 1.0.5
     * @deprecated Deprecated since version 1.0.11 Use the transient method instead.
     */
    public static function get_widget_settings($post_id, $widget_id)
    {

        if (!$post_id || !$widget_id) {
            return false;
        }

        $elementor = Plugin::$instance;
        $pageMeta  = $elementor->documents->get($post_id);

        if (!$pageMeta) {
            return false;
        }
        $metaData = $pageMeta->get_elements_data();
        if (!$metaData) {
            return false;
        }

        $widget_data = self::get_element_recursive($metaData, $widget_id);
        $settings    = [];

        if (is_array($widget_data)) {
            $widget   = $elementor->elements_manager->create_element_instance($widget_data);
            $settings = $widget->get_settings();
        }

        return $settings;
    }

    /**
     * Get the `posts per page` value from Framework or WordPress settings last case scenario.
     * Usefull if we're using `current` post type and don't have posts per page option.
     *
     * @param string $post_type The post type slug.
     *
     * @since 1.2.1
     * @return int The number of posts per page.
     */
    public static function get_framework_visible_posts(string $post_type)
    {
        // Get from Uicore Framework, if available
        if (defined('UICORE_ASSETS')) {

            switch ($post_type) {
                case 'product':
                    return \Uicore\Helper::get_option('woocommerce_posts_number');

                case 'post':
                    return \Uicore\Helper::get_option('blog_posts_number');

                case 'portfolio':
                    return \Uicore\Helper::get_option('portfolio_posts_number');

                default:
                    return get_option('posts_per_page');
            }
        }

        return get_option('posts_per_page');
    }
    /**
     * Retrieves the list of Uicore Framework registered popups.
     *
     * @return array
     *
     * @since 1.3.3
     */
    public static function get_uicore_popups(): array
    {
        // get elementor popups posts
        $args = [
            'post_type' => 'uicore-tb',
            'post_status' => 'publish',
            'posts_per_page' => 50,
            'tax_query' => [
                [
                    'taxonomy' => 'tb_type',
                    'field' => 'slug',
                    'terms' => '_type_popup',
                ]
            ]
        ];

        $query = new \WP_Query($args);
        $popups = [];
        if ($query->have_posts()) {
            while ($query->have_posts()) {
                $query->the_post();
                $popups[esc_html(get_the_ID())] = esc_html(html_entity_decode(get_the_title()));
            }
            wp_reset_postdata();
        }

        return $popups;
    }
}

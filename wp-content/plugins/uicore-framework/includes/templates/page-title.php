<?php

namespace UiCore;

defined('ABSPATH') || exit();

/**
 * Here we generate the header
 */
class PageTitle
{
    private $type; //Page type
    private $data; //Page title data
    private $portfolio_id;
    private $blog_id;
    private $shop_id;

    private $pic_url;
    private $with_pic = false;

    /**
     * __construct
     *
     * @return void
     */
    function __construct()
    {
        $this->pic_url = ''; //$settings['pagetitle_bg']['image']['url'];

        //Hook this to init to get is_user_logged_in() -> for maintenance mode
        add_action('wp', function () {
            $post = get_post();
            if ($this->init($post) && !Helper::is_full()) {
                //Now we know for sure the pic_url so let's preload the picure
                add_action('wp_head', [$this, 'preload_feature_img'], 3);
                add_action('uicore_before_content', [$this, 'display_page_title']);
            }
        });
    }

    /**
     * do the required checks than get data and display it
     *
     * @param object $post
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function init($post)
    {
        $post_id = $post->ID ?? 0;

        //continue only if is not in maintenance mode or 404 page
        $is_maintenance = Helper::get_option('gen_maintenance') === 'false';
        if ((!$is_maintenance && !is_user_logged_in()) || is_404()) {
            return false;
        }
        //continue only if page title is not off
        if (!Helper::po('pagetitle', 'pagetitle', 'true', $post_id) === 'true') {
            return false;
        }
        if (!apply_filters('uicore_is_pagetitle', true)) {
            return false;
        }

        //get data and display it
        $this->set_data($post);

        return true;
    }

    /**
     * Get and Set Required Data
     *
     * @param object $post
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function set_data($post)
    {
        $post_id = $post->ID ?? 0;

        if (is_search()) {
            $this->type = 'search';

            $title = sprintf(
                esc_html_x('Search Results for: %s', 'Frontend - Page title', 'uicore-framework'),
                '<span>' . get_search_query() . '</span>'
            );
            $description = false;
        } elseif (
            is_post_type_archive('post') ||
            is_home() ||
            is_category() ||
            is_day() ||
            is_month() ||
            is_author() ||
            is_year() ||
            is_tag()
        ) {
            $this->type = 'blog_archieve';
            $this->blog_id = get_option('page_for_posts');
            $this->get_feature_from_archive($this->blog_id);

            if ($this->blog_id != 0 && is_home()) {
                $title = get_the_title($this->blog_id);
            } elseif (is_home()) {
                $title = get_bloginfo(); // Post Page not set
            } else {
                $title = get_the_archive_title();
            }
            if (is_category()) {
                $description = $this->get_the_description(get_the_category(), 'category');
                if ($description === null) {
                    $description = $this->get_the_description($this->blog_id);
                }
            } else {
                $description = $this->get_the_description($this->blog_id);
            }
        } elseif (is_post_type_archive('product') || (class_exists('WooCommerce') && is_product_taxonomy())) {
            $this->type = 'shop_archieve';
            $this->shop_id = get_option('woocommerce_shop_page_id');
            $this->get_feature_from_archive($this->shop_id);
            if (is_shop()) {
                $title = get_the_title($this->shop_id);
            } else {
                $title = single_cat_title(null, false);
                if (is_product_category()) {
                    global $wp_query;
                    $cat = $wp_query->get_queried_object();
                    $thumbnail_id = get_term_meta($cat->term_id, 'thumbnail_id', true);
                    // get the image URL
                    $this->pic_url = wp_get_attachment_url($thumbnail_id);
                    $this->with_pic = true;
                }
            }
            $description = $this->get_the_description($this->shop_id);
        } elseif (is_post_type_archive('portfolio') || is_tax('portfolio_category')) {
            $this->type = 'portfolio_archieve';
            $this->portfolio_id = Helper::get_option('portfolio_page')['id'] ?? 0;
            $this->portfolio_id = apply_filters( 'wpml_object_id', $this->portfolio_id , 'post', true );
            $this->get_feature_from_archive($this->portfolio_id);
            if (is_post_type_archive('portfolio')) {
                if ($this->portfolio_id != 0) {
                    $title = get_the_title($this->portfolio_id);
                } else {
                    $title = esc_html_x('Portfolio', 'Frontend - Page title', 'uicore-framework');
                }
            } else {
                $title = sprintf(esc_html_x('Category: %s', 'Frontend - Page title', 'uicore-framework'), single_term_title(null, false));
            }
            $description = $this->get_the_description($this->portfolio_id);
        } elseif (is_post_type_archive()) {
            $this->type = 'generic_archive';
            $title = post_type_archive_title(null, false);
            $description = null;
        } elseif (is_tax()) {
            $this->type = 'generic_tax';
            $title = single_term_title(null, false);
            $description = null;
        } elseif (is_singular('post')) {
            $this->type = 'post';
            $this->blog_id = get_option('page_for_posts');
            $this->get_feature_from_archive(get_the_ID());
            $title = single_post_title(null, false);
            $description = $this->get_the_meta($post);
        } elseif (is_singular('page')) {
            $this->type = 'page';
            $this->get_feature_from_archive(get_the_ID());
            $title = single_post_title(null, false);
            $description = $this->get_the_description(get_the_ID());
        } elseif (is_singular('portfolio')) {
            $this->type = 'portfolio';
            $this->portfolio_id = Helper::get_option('portfolio_page')['id'] ?? 0;
            $this->get_feature_from_archive(get_the_ID());
            $title = single_post_title(null, false);
            $description = $this->get_the_description(get_the_ID());
        } elseif (is_singular('product')) {
            $this->type = 'product';
            $this->shop_id = get_option('woocommerce_shop_page_id');
            $this->get_feature_from_archive(get_the_ID());
            $title = single_post_title(null, false);
            $description = $this->get_the_description(get_the_ID());
        } else {
            $this->type = 'other';
            $title = get_the_title(); //single_post_title(null, false);
            $description = $this->get_the_description(get_the_ID());
            $this->get_feature_from_archive(get_the_ID());
        }
        $this->data = [
            'title' => \apply_filters('uicore_pagetitle_title', $title, $post),
            'description' => \apply_filters('uicore_pagetitle_description', $description, $post),
        ];
        if (Helper::po('breadcrumbs', 'pagetitle_breadcrumbs', 'false', $post_id) == 'true') {
            $this->data['breadcrumb'] = $this->get_the_breadcrumb($title);
        }
        // print_r($this->data);
        // print_r($this->portfolio_id);
    }

    /**
     * Get the description and return it as markup
     * Since 3.1.1 $type was added in order to get the description for other terms
     *
     * @param object $post
     * @return html $description
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_the_description($post_id, $type = "post")
    {
        $description = null;
        if ($post_id == !0) {
            if ($type === 'post') {
                $meta = get_post_meta($post_id, 'page_description', true);
            } else {
                $meta = isset($post_id[0]->description) ? $post_id[0]->description : false;
            }
            if ($meta) {
                $description .= '<div class="uicore-animate uicore-desc-wrapp">';
                $description .= '<p class="uicore-description">';
                $description .= $meta;
                $description .= '</p>';
                $description .= '</div>';
            }
        }
        return $description;
    }

    /**
     * Get the post meta and return it as markup
     *
     * @param object $post
     * @return html $description
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_the_meta($post)
    {
        $description = '<div class="uicore-animate uicore-entry-meta">';

        if (Helper::get_option('blogs_author') === 'true') {
            $userID = $post->post_author;
            $description .= '<div>';
            $description .= '<a href="' . esc_url(get_author_posts_url($userID)) . '">';
            $description .= get_the_author_meta('display_name', $userID);
            $description .= '</a>';
            if (Helper::get_option('blogs_date') === 'true' || Helper::get_option('blogs_category') === 'true') {
                $description .= Helper::get_separator();
            }
            $description .= '</div>';
        }

        if (Helper::get_option('blogs_date') === 'true') {
            $date = get_the_date() ?? '';
            $update_date = get_the_modified_date() ?? '';
            if (Helper::get_option('blogs_date_type') === 'published' || Helper::get_option('blogs_date_type') === 'both') {
                $date = get_the_date() ?? '';
                $description .= '<span class="ui-blog-date ui-published">';
                if (Helper::get_option('blogs_date_type') === 'both' && $date != $update_date) {
                    $description .= esc_attr_x('Posted On:', 'Frontend - Blog Meta', 'uicore-framework');
                }
                $description .= $date;
                $description .= ' </span>';
            }
            if (Helper::get_option('blogs_date_type') === 'updated' || Helper::get_option('blogs_date_type') === 'both') {
                if (Helper::get_option('blogs_date_type') === 'updated' || $date != $update_date) {
                    $date = get_the_modified_date() ?? '';
                    $description .= Helper::get_separator();
                    $description .= '<span class="ui-blog-date ui-updated">';
                    if (Helper::get_option('blogs_date_type') === 'both') {
                        $description .= esc_attr_x('Updated On:', 'Frontend - Blog Meta', 'uicore-framework');
                    }
                    $description .= $date;
                    $description .= ' </span>';
                }
            }
        }

        if (Helper::get_option('blogs_category') === 'true') {
            if (Helper::get_option('blogs_date') === 'true') {
                $description .= Helper::get_separator();
            }
            $description .= '<div class="uicore-post-category uicore-body">';
            ob_start();
            the_category(', ');
            $description .= ob_get_contents();
            ob_end_clean();
            $description .= '</div>';
        }

        if(Helper::get_option('blogs_readtime') == 'true') {
            $description .= Helper::get_separator();
            $description .= '<span class="ui-blog-date">';
            $description .= Helper::get_reading_time();
            $description .= '</span>';
        }
        $description .= '</div>';

        return $description;
    }

    /**
     * Get the post meta and return it as markup
     *
     * @param string $post_title Current Post title
     * @return array $breadcrumb
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function get_the_breadcrumb($post_title)
    {
        $type = $this->type; //Shorter
        $breadcrumb = [];

        if (!is_front_page()) {
            if (\apply_filters('uicore-show-home-in-breadcrumb', true)) {

                $home_label = \apply_filters('uicore-breadcrumb-home-label', _x('Home', 'Frontend - Page title', 'uicore-framework'));

                $breadcrumb[] = [
                    // Default
                    'title' => $home_label,
                    'url' => get_site_url(),
                ];
            }

            if ($type === 'blog_archieve' || $type === 'post') {
                $breadcrumb[] = [
                    // Add Blog Title if is blog
                    'title' => get_the_title($this->blog_id),
                    'url' => get_the_permalink($this->blog_id),
                ];

                if (is_day()) {
                    $title = get_the_date();
                } elseif (is_month()) {
                    $title = get_the_date('F Y');
                } elseif (is_year()) {
                    $title = get_the_date('Y');
                } elseif (is_author()) {
                    $title = get_the_author();
                } elseif (is_tag()) {
                    $title = single_tag_title(null, false);
                } elseif (is_category()) {
                    $title = single_cat_title(null, false);
                } elseif (is_singular()) {
                    $breadcrumb = $this::get_taxonomy_breadcrumb($breadcrumb, 'category');
                }
            } elseif (class_exists('WooCommerce') && ($type === 'shop_archieve' || $type === 'product')) {
                $breadcrumb[] = [
                    // Add Shop Title if is shop
                    'title' => get_the_title($this->shop_id),
                    'url' => get_the_permalink($this->shop_id),
                ];

                if (is_product_taxonomy()) {
                    $title = single_cat_title(null, false);
                } elseif (is_singular()) {
                    $breadcrumb = $this::get_taxonomy_breadcrumb($breadcrumb, 'product_cat');
                }
            } elseif ($type === 'portfolio_archieve' || $type === 'portfolio') {
                if ($this->portfolio_id != 0) {
                    $breadcrumb[] = [
                        // Add Portfolio Title if is portfolio
                        'title' => get_the_title($this->portfolio_id),
                        'url' => get_the_permalink($this->portfolio_id),
                    ];
                } else {
                    $breadcrumb[] = [
                        // Add Portfolio Title if is portfolio
                        'title' => esc_html_x('Portfolio', 'Frontend - Page title', 'uicore-framework'),
                        'url' => get_post_type_archive_link('portfolio'),
                    ];
                }

                if (is_tax('portfolio_category')) {
                    $title = single_term_title(null, false);
                } elseif (is_singular()) {
                    $breadcrumb = $this::get_taxonomy_breadcrumb($breadcrumb, 'portfolio_category');
                }
            } elseif ($type === 'generic_archive') {
                $breadcrumb[] = [
                    // Add Portfolio Title if is portfolio
                    'title' => post_type_archive_title(null, false),
                    'url' => '',
                ];
            }

            if ((!is_singular(['post', 'product', 'portfolio', 'page']) && is_singular()) || $type === 'generic_tax') {
                $post_type = get_post_type();
                $archive_title = get_post_type_object($post_type);
                $breadcrumb[] = [
                    // Add Portfolio Title if is portfolio
                    'title' => $archive_title->labels->singular_name,
                    'url' => get_post_type_archive_link($post_type),
                ];
            }

            if ($type === 'generic_tax') {
                $breadcrumb[] = [
                    // Add Portfolio Title if is portfolio
                    'title' => get_the_archive_title(),
                    'url' => '',
                ];
            }

            if (isset($title)) {
                $breadcrumb[] = [
                    // anny taxonomy if single else will get data from get_taxonomy_breadcrumb
                    'title' => $title,
                    'url' => '',
                ];
            }
        }

        if (is_singular()) {
            $parents = array_reverse(get_post_ancestors(\get_the_ID()));
            foreach ($parents as $key => $id) {
                $page_parent = get_post($id);
                $breadcrumb[] = [
                    'title' => $page_parent->post_title,
                    'url' => get_the_permalink($id),
                ];
            }

            $breadcrumb[] = [
                'title' => $post_title,
                'url' => null,
            ];
        }

        return $breadcrumb;
    }

    /**
     * Get the multilevel or single category/meta
     *
     * @param array $breadcrumb Current breadcrumb array
     * @param string $taxonomy Taxonomy for witch we need the data
     * @return array $breadcrumb current + Taxonomy
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public static function get_taxonomy_breadcrumb($breadcrumb, $taxonomy)
    {
        $post = get_the_ID();
        $terms = get_the_terms($post, $taxonomy);

        $is_enabled = \apply_filters('uicore_enable_breadcrumb_taxonomy', true, $taxonomy,$post);
    
        if ((!is_array($terms) && !is_object($terms)) || !$is_enabled) {
            return $breadcrumb;
        }
    
        $processed_terms = [];
        $final_breadcrumb_data = [];
        foreach ($terms as $term) {
            // Check if the term has already been processed
            if (in_array($term->term_id, $processed_terms)) {
                continue;
            }
    
            $parent_id = $term->parent;
    
            $current_term_breadcrumb = [];
    
            while ($parent_id != 0 && !in_array($parent_id, $processed_terms)) {
                $ancestor = get_term_by('id', $parent_id, $taxonomy);
    
                $current_term_breadcrumb[] = $ancestor;

                // Mark the ancestor as processed
                $processed_terms[] = $ancestor->term_id;
    
                $parent_id = $ancestor->parent;
            }
    
            // Reverse the breadcrumb array to get the correct order
            $current_term_breadcrumb = array_reverse($current_term_breadcrumb);
    
            // Add the current term to the breadcrumb
            $current_term_breadcrumb[] = $term;
    
            // Merge the current term breadcrumb with the overall breadcrumb
            $final_breadcrumb_data = array_merge($final_breadcrumb_data, $current_term_breadcrumb);
    
            // Mark the current term as processed
            $processed_terms[] = $term->term_id;

        }
    
        foreach ($final_breadcrumb_data as $term) {
            $breadcrumb[] = [
                'title' => $term->name,
                'url' => get_term_link($term->term_id, $taxonomy),
            ];
        }
        return $breadcrumb;
    }
    
    

    /**
     * Display Page title
     *
     * @param object $post
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function display_page_title($post)
    {
        //check if post id is set if not return 0
        $post_id = $post->ID ?? 0;

        $blog_title = apply_filters('uicore_blogs_title', Helper::get_option('blogs_title'), $post);
        if (Helper::po('pagetitle', 'pagetitle', 'true', $post_id) == 'true') {
            if (is_singular('post') &&  $blog_title === 'simple page title') {
                return null;
            } else {
?>
                <header class="uicore uicore-page-title uicore-section uicore-box" <?php
                                                                                                    if (Helper::get_option('pagetitle_i') === 'true' && $this->with_pic || (\is_singular('post') &&  $blog_title === 'simple creative')) {
                                                                                                        $this->feature_image_as_background();
                                                                                                    }
                                                                                                    ?>>

                    <?php
                    if (!is_singular('post') || (is_singular('post') &&  $blog_title != 'simple creative')) {
                    ?>
                        <div class="uicore-overlay"></div>
                    <?php } ?>
                    <div class="uicore uicore-container">

                        <?php

                            \do_action('uicore_before_page_title_breadcrumb');

                        if (!is_singular('post') || (is_singular('post') &&  $blog_title != 'simple creative')) {
                            if (isset($this->data['breadcrumb']) && $this->data['breadcrumb']) {
                                $this::display_breadcrumb($this->data['breadcrumb']);
                            }

                            \do_action('uicore_before_page_title_title');
                            ?>
                            <h1 class="uicore-title uicore-animate <?php echo Helper::get_option('pagetitle_tag'); ?> uicore-typo-<?php echo Helper::get_option('pagetitle_tag'); ?>">
                                <?php echo $this->data['title']; ?>
                            </h1>
                            <?php 
                            
                            \do_action('uicore_after_page_title_title');
                        
                            if(\is_singular('post') && in_array($blog_title, ['full screen', 'default page title']) && Helper::get_option('blogs_excerpt_in_pt') === 'true'){
                                echo '<div class="uicore-animate uicore-entry-meta">';
                                echo    '<p class="uicore-description">';
                                echo        get_the_excerpt();
                                echo    '</p>';
                                echo '</div>';
                            }
                            
                            echo $this->data['description'];

                            \do_action('uicore_after_page_title_description');

                        
                        }
                        ?>
                    </div>

                </header>
<?php
            }
        }
    }

    /**
     * Display Breadcrumbs
     *
     * @param object $post
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public static function display_breadcrumb($data = [], $type = 'p')
    {
        if ($data) {
            $sep = '<i class="uicore-separator uicore-i-arrow"></i>';
            $breadcrumb = '<' . $type . ' class="uicore-animate ui-breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">';

            foreach ($data as $index => $term) {
                if ($index != 0) {
                    $breadcrumb .= $sep;
                }
                if (isset($term['url'])) {
                    $breadcrumb .= '<span itemprop="itemListElement" itemscope
          itemtype="https://schema.org/ListItem"><a itemprop="item" href="' . $term['url'] . '"><span itemprop="name">' . $term['title'] . '</span></a><meta itemprop="position" content=" ' . ($index + 1) . '" /></span>';
                } else {
                    $breadcrumb .= '<span itemprop="itemListElement" itemscope
          itemtype="https://schema.org/ListItem" ><span><span itemprop="name">' . $term['title'] . '</span><meta itemprop="position" content=" ' . ($index + 1) . '" /></span></span>';
                }
            }
            $breadcrumb .= '</' . $type . '>';

            echo $breadcrumb;
        }
    }

    /**
     * Feature image as bg
     *
     * @param object $post
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function feature_image_as_background()
    {
        if ($this->pic_url) {
            echo ' style="background-image: url(';
            echo $this->pic_url;
            echo ')" ';
        }
    }

    function get_feature_from_archive($post_id)
    {
        $meta = Settings::po_get_page_settings($post_id);
        $this->with_pic = $meta['pagetitle_bg'];
        if (!$this->with_pic['type'] === 'theme default') {
            $this->with_pic = true;
        }

        if ($post_id != 0) {
            $img = wp_get_attachment_image_src(get_post_thumbnail_id($post_id), 'full');
            if (isset($img[0]) && $img[0] != null) {
                $this->pic_url = esc_url($img[0]);
            }
        }
    }

    function preload_feature_img()
    {
        $url = '';
        if (Helper::get_option('performance_preload_img') === 'true') {
            return;
        }
        if (is_singular('post') && Helper::get_option('blogs_title') === 'simple page title' && Helper::get_option('blogs_img') === 'false') {
            return;
        }

        if ($this->pic_url) {
            $url = $this->pic_url;
        } else {
            if ($this->type === 'blog_archieve' || $this->type === 'search') {
                $id = $this->blog_id;
            } elseif ($this->type === 'portfolio_archieve') {
                $id = $this->portfolio_id;
            } elseif ($this->type === 'shop_archieve') {
                $id = $this->shop_id;
            } else {
                $id = get_post()->ID ?? 0;
            }
            $meta = get_post_meta($id, 'page_options', true);
            if (Helper::isJson($meta)) {
                $meta = json_decode($meta, true);
                if (isset($meta['pagetitle_bg']) && $meta['pagetitle_bg']['image']['url'] != '') {
                    $url = $meta['pagetitle_bg']['image']['url'];
                }
            }
            $to = Helper::get_option('pagetitle_bg');
            if ($url === '' && $to['image']['url'] != '') {
                $url = $to['image']['url'];
            }
        }

        if ($url) {
            echo '<link rel="preload" href="' . $url . '" as="image">';
        }
    }
}

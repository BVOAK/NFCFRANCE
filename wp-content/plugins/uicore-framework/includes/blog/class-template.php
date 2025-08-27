<?php
namespace UiCore\Blog;
use UiCore\Pagination as Pagination;
use UiCore\Helper as Helper;
use UiCore\PageTitle;

defined('ABSPATH') || exit();

/**
 * Frontend Blog Archive and Single
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.2
 */
class Template
{
    private $post_type;

    /**
     * __construct
     *
     * @return void
     */
    function __construct($type = 'full', $post_type='post')
    {
		$this->post_type = $post_type;
        if ($type == 'full') {
            if (is_single()) {
                if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
                    $this->render_blog_single();
                }
            } else {
                if ((! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' )  ) ) {
                    $this->render_blog_archive();
                }
            }
        }

    }

    /**
     * render_blog_single
     *
     * @return void
     */
    public function render_blog_single()
    {
        $this->single_layout();
    }

    /**
     * render_blog_archive
     *
     * @return void
     */
    public function render_blog_archive()
    {
        ?>
        <main id="main" class="site-main uicore-section uicore-box uicore">
        <div class="uicore uicore-container uicore-content-wrapper uicore-blog-animation">

            <div class="uicore-archive uicore-post-content">
                <?php

                /**
                 * Fires inside mobile menu content.
                 *
                 * @since 5.0.6
                 *
                 */
                do_action('uicore__before_blog_archive');


                if (!$this->is_search_has_results() && is_search()) {
                    $this->not_found_layout();
                } else {


                    if (Helper::get_option('blog_filters', 'true') == 'true') {
                        $this->get_filters();
                    }



                    $this->blog_layout();
                    new Pagination();
                }

                /**
                 * Fires inside mobile menu content.
                 *
                 * @since 5.0.6
                 *
                 */
                do_action('uicore__after_blog_archive');

                ?>
            </div>
            <?php do_action('uicore_sidebar'); ?>
        </div>
    </main>
    <?php
    }

    /**
     * blog_layout
     *
     * @return void
     */
    public function blog_layout($wp_query = false, $grid_type = null, $col_no = null, $hover_effect = null, $ratio = null, $extra = ['author'=>null,'date'=>null,'category'=>null,'excerpt'=>null], $item_style=null)
    {
        $is_widget = ' uicore-blog-widget';
        if (!$wp_query && $grid_type === null) {
            $is_widget = ' ';
            global $wp_query;
        }



        //global variables
        $grid_type = $grid_type ?? Helper::get_option('blog_layout', 'grid');
        $hover_effect = $hover_effect ?? Helper::get_option('blog_hover_effect', 'zoom');
        $item_style = $item_style ?? Helper::get_option('blog_item_style');
        $ratio = $ratio ?? Helper::get_option('blog_ratio', 'portrait');
        $isexcerpt = $extra['excerpt'] ?? (Helper::get_option('blog_excerpt', 'true') === 'true');
        $isautor = $extra['author'] ?? (Helper::get_option('blog_author', 'true') === 'true');
        $isdate = $extra['date'] ?? (Helper::get_option('blog_date', 'true') === 'true');
        $isreadtime = isset($extra['readtime']) ? $extra['readtime'] : (Helper::get_option('blog_readtime', 'true') === 'true');
        $iscategory = $extra['category'] ?? (Helper::get_option('blog_category', 'true') === 'true');
        // $title_tag = \apply_filters('uicore_blog_grid_tag', 'h4');

        $hover_effect = str_replace(' ', '-', $hover_effect);
        $item_style = str_replace(' ', '-', $item_style);



        $col_no = $col_no ?? Helper::get_option('blog_col', '3');
        if ($col_no == '1') {
            $col = '12';
        }
        if ($col_no == '2') {
            $col = '6';
        }
        if ($col_no == '3') {
            $col = '4';
        }
        if ($col_no == '4') {
            $col = '3';
        }

        $grid_space = Helper::get_option('blog_col_space', 'extra large');
        $grid_space = str_replace(' ', '-', $grid_space);

        if ($grid_space == 'extra-large') {
            $grid_space_no = 80;
        }
        if ($grid_space == 'large') {
            $grid_space_no = 50;
        }
        if ($grid_space == 'medium') {
            $grid_space_no = 30;
        }
        if ($grid_space == 'small') {
            $grid_space_no = 15;
        }
        if ($grid_space == 'extra-small') {
            $grid_space_no = 5;
        }
        if ($grid_space == 'none') {
            $grid_space_no = 0;
        }

        echo '<div class="uicore-grid-container uicore-blog-grid uicore-grid-row';
        echo ' uicore-' . $grid_type;
        echo ' uicore-' . $ratio . '-ratio';
        echo ' uicore-' . $grid_space . '-space';
        echo ' animate-'.$col_no;
		echo ' ui-st-' . $item_style;
        echo $is_widget;

        if ($grid_type == 'masonry') {
            wp_enqueue_script('uicore-grid');
            echo '" data-grid="masonryb"';
            echo ' data-col="' . $col_no . '"';
            echo ' data-space="' . $grid_space_no . '">';
        } else {
            echo '">';
        }
        $size = \apply_filters('uicore-default-blog-img-size','uicore-medium');

        //Start the loop
        while ($wp_query->have_posts()) {

            $wp_query->the_post();
            global $post;

            //post specific varables
            $post_link = get_permalink();
            $category = Helper::get_taxonomy('category');
            $post_thumbnail = false;

            //get the post thumbnail
            if (!empty(get_the_post_thumbnail())) {
                //pic url
                $post_thumbnail =
                    '<a href="' . esc_url($post_link) . '" title=" '.esc_html_x('View Post:', 'Frontend - Blog', 'uicore-framework').' ' . esc_attr(get_the_title()) . '" >';

                if ($grid_type != 'masonry') {
                    $post_thumbnail .=
                        '  <div class="uicore-blog-img-container uicore-zoom-wrapper">
                                            <div class="uicore-cover-img" style="background-image: url(' .
                        get_the_post_thumbnail_url($post->ID, $size) .
                        ')"></div>
                                        </div>';
                } else {
                    $pic_id = get_post_thumbnail_id($post->ID);
                    $post_thumbnail .=
                        '  <div class="uicore-blog-img-container uicore-zoom-wrapper">
                                            <img class="uicore-cover-img" src="'. wp_get_attachment_url($pic_id, $size) .'" srcset="' .
                        wp_get_attachment_image_srcset($pic_id, $size) .
                        '"
                        sizes="' .
                        wp_get_attachment_image_sizes($pic_id, $size) .
                        '" alt="' .
                        esc_attr(get_the_title()) .
                        '"/>
                                        </div>';
                }

                $post_thumbnail .= '</a>';
            }

            $extra_post_classes = ['uicore-grid-item'];
            if ($col != '12') {
                array_push($extra_post_classes, 'uicore-col-md-6');
            }
            array_push($extra_post_classes, 'uicore-col-lg-' . $col);
            array_push($extra_post_classes, ' uicore-' . $hover_effect);
            array_push($extra_post_classes, 'uicore-animate');
            ?>

            <div <?php post_class($extra_post_classes); ?> >
                <article class="uicore-post">
                    <div class="uicore-post-wrapper">

                        <?php echo $post_thumbnail; ?>

                        <div class="uicore-post-info">
                            <div class="uicore-post-info-wrapper">
                                <?php
                                if ($category && $iscategory) { ?>
                                <div class="uicore-post-category uicore-body">
                                    <?php echo $category; ?>
                                </div>
                              <?php }
                                echo '<a href="';
                                echo esc_url($post_link);
                                echo '" title="View Post: ' . esc_html(get_the_title()) . ' ">';
                                ?>
                              <h4 class="uicore-post-title"><span><?php echo esc_html(get_the_title()); ?></span></h4>
                              <?php
                              echo '</a>';

                              if ( $isexcerpt) {
                                  echo '<p>';
                                  echo wp_trim_excerpt(get_the_excerpt());
                                  echo '</p>';
                              }


 						  	  if ( ($isautor || $isdate || $isreadtime) && $this->post_type === 'post' ) {
	                              echo '<div class="uicore-post-footer uicore-body">';

	                              if ($isautor) {
	                                  echo '<span>';
	                                  echo get_the_author_posts_link();
	                                  echo '</span>';
	                              }

	                              if ($isautor && $isdate) {
	                                  echo Helper::get_separator();
	                              }

	                              if ($isdate) {
	                                  echo '<span>';
	                                  echo get_the_date();
	                                  echo '</span>';
	                              }

                                  if($isreadtime){
                                      echo ($isautor || $isdate) ? Helper::get_separator() : '';
                                      echo '<span>';
                                      echo Helper::get_reading_time();
                                      echo '</span>';
                                  }


	                              echo '</div>';
							  }

            //.uicore-post-footer END
            ?>
                            </div>
                        </div>

                    </div>
                </article>
            </div>
        <?php
        }
        wp_reset_query();
        echo '</div>'; //.uicore-post-list END
    }

    /**
     * single_layout
     *
     * @return void
     */
    public function single_layout()
    {
        global $post;
        $page_title_type = apply_filters('uicore_blogs_title', Helper::get_option('blogs_title'), $post);
        $author = (Helper::get_option('blogs_author_box', 'false') === 'false' ? false : true);
        $navigation = (Helper::get_option('blogs_navigation', 'false') === 'false' ? false : true);
        $related = (Helper::get_option('blogs_related', 'false') === 'false' ? false : true);
        $related_style = Helper::get_option('blogs_related_style');

        while (have_posts()):
            the_post();
        if (
            $page_title_type === 'simple creative' && is_singular('post') &&
			(Helper::po('pagetitle', 'pagetitle', 'true', \get_the_ID()) == 'true')
        ) {
            echo '<div class="uicore-section uicore-box">';
                echo '<div class="uicore-container">';
                    $this->single_title('creative');
                echo '</div>';
            echo '</div>';
        }


        ?>
        <main id="main" class="site-main uicore-section uicore-box uicore">
			<div class="uicore uicore-container uicore-content-wrapper uicore-blog-animation">


                <?php


                ?>
				<div class="uicore-type-post uicore-post-content uicore-animate">

                    <article id="post-<?php the_ID(); ?>" <?php post_class('blog-fonts'); ?>>
                    <?php if (
                        ( $page_title_type === 'simple page title' ||
                            (Helper::po('pagetitle', 'pagetitle', 'true', get_the_ID()) == 'false' && $page_title_type != 'simple page title' )
                        ) &&
                         is_singular('post')  ) {
                        $this->single_title();
                    }


                    /**
                     * Fires inside mobile menu content.
                     *
                     * @since 5.0.6
                     *
                     */
                    do_action('uicore__before_blog_single');


                    ?>
                        <div class="entry-content">
                            <?php
                            the_content(
                                sprintf(
                                    wp_kses(
                                        /* translators: %s: Name of current post. Only visible to screen readers */
                                        _x('Continue reading<span class="screen-reader-text"> "%s"</span>', 'Frontend - Blog', 'uicore-framework'),
                                        [
                                            'span' => [
                                                'class' => [],
                                            ],
                                        ]
                                    ),
                                    get_the_title()
                                )
                            );

                            wp_link_pages([
                                'before' => '<div class="page-links">' . esc_html_x('Pages:', 'Frontend - Blog', 'uicore-framework'),
                                'after' => '</div>',
                            ]);
                            ?>
                        </div><!-- .entry-content -->

                        <?php



                        /**
                         * Fires inside mobile menu content.
                         *
                         * @since 5.0.6
                         *
                         */
                        do_action('uicore__after_blog_single_content');


                        if (Helper::get_option('blogs_tags', 'true') == 'true') { ?>
                        <footer class="entry-footer">
                            <?php
                            $tags_list = get_the_tag_list('', ' ');
                            if ($tags_list) {
                                echo '<div class="tags-links">' . $tags_list . '</div>';
                            }
                            ?>
                        </footer><!-- .entry-footer -->
                        <?php } ?>

                    </article><!-- #post-<?php the_ID(); ?> -->
                    <?php

                    if($author){
                        $this->get_the_author_box();
                    }

                    if($navigation){
                        echo '<hr/>';
                        Helper::get_post_navigation(esc_attr_x('Previous Article', 'Frontend - Blog', 'uicore-framework'), esc_attr_x('Next Article', 'Frontend - Blog', 'uicore-framework'));
                    }

                    if($related && ($related_style != 'grid') ) {
                        $this->get_related_posts($related_style);
                    }


                    /**
                     * Fires inside mobile menu content.
                     *
                     * @since 5.0.6
                     *
                     */
                    do_action('uicore__before_blog_comments');


                    //prettier-ignore
                    // If comments are open or we have at least one comment, load up the comment template.
                    if (comments_open() || get_comments_number()):
                        comments_template();
                    endif;


                    /**
                     * Fires inside mobile menu content.
                     *
                     * @since 5.0.6
                     *
                     */
                    do_action('uicore__after_blog_single');


                    ?>
                </div>
            <?php do_action('uicore_sidebar', $post); ?>
        </div>
        <?php
        if($related && ($related_style === 'grid') ) {
            $this->get_related_posts($related_style);
        }
        ?>
    </main>
    <?php
    endwhile;// End of the loop.
    }

    /**
     * single_title
     *
     * @return void
     */
    public function single_title($type = 'simple page title')
    {
        ?>
        <header class="uicore-single-header <?php
        echo ($type === 'creative') ? ' ui-simple-creative' : '';
        ?>">

        <?php
        if(Helper::get_option('blogs_breadcrumb') === 'true' && $type === 'simple page title'){
            $this->get_the_breadcrumb();
        }

        the_title('<h1 class="entry-title uicore-animate">', '</h1>');

        $description = '<div class="uicore-entry-meta uicore-animate">';

        if (Helper::get_option('blogs_author') == 'true') {
            $description .= '<div>';
            $description .= get_the_author_posts_link();
            $description .= Helper::get_separator();
            $description .= '</div>';
        }

        if (Helper::get_option('blogs_date') == 'true') {
            $date = get_the_date() ?? '';
            $update_date = get_the_modified_date() ?? '';
            if(Helper::get_option('blogs_date_type') === 'published' || Helper::get_option('blogs_date_type') === 'both'){
                $date = get_the_date() ?? '';
                $description .= '<span class="ui-blog-date ui-published">';
                if(Helper::get_option('blogs_date_type') === 'both' && $date != $update_date){
                    $description .= esc_attr_x('Posted On:','Frontend - Blog Meta','uicore-framework');
                }
                $description .= $date;
                $description .= ' </span>';
                $description .= Helper::get_separator();
            }
            if(Helper::get_option('blogs_date_type') === 'updated' || Helper::get_option('blogs_date_type') === 'both'){
                if(Helper::get_option('blogs_date_type') === 'updated' || $date != $update_date){
                    $date = get_the_modified_date() ?? '';
                    $description .= '<span class="ui-blog-date ui-updated">';
                    if(Helper::get_option('blogs_date_type') === 'both'){
                        $description .= esc_attr_x('Updated On:','Frontend - Blog Meta','uicore-framework');
                    }
                    $description .= $date;
                    $description .= ' </span>';
                    $description .= Helper::get_separator();
                }
            }
        }

        if (Helper::get_option('blogs_category') == 'true') {
            $description .= '<div class="uicore-post-category uicore-body">';
            ob_start();
            the_category(', ');
            $description .= ob_get_clean();
            $description .= '</div>';
        }

        if(Helper::get_option('blogs_readtime') == 'true') {
            $description .= Helper::get_separator();
            $description .= '<span class="uicore-post-readtime">';
            $description .= Helper::get_reading_time();
            $description .= '</span>';
        }
        $description .= '</div>';

        echo $description;

        if (Helper::get_option('blogs_img') == 'true' && $type === 'simple page title') {
            echo '<div class="uicore-feature-img-wrapper uicore-animate">';
            the_post_thumbnail('large');
            echo '</div>';
        } ?>
    </header>
    <?php
    }

    /**
     * is_search_has_results
     *
     * @return void
     */
    function is_search_has_results()
    {
        global $wp_query;
        $result = 0 != $wp_query->found_posts ? true : false;
        return $result;
    }

    function not_found_layout()
    {
        ?>
		<div class="uicore-animate ui-no-results">
            <h2 class="ui-search-title"><?php echo esc_attr_x('No results', 'Frontend - Search', 'uicore-framework'); ?></h2>
            <p><?php echo esc_attr_x('We did not find any article that matches this search. Try using other search criteria:', 'Frontend - Search', 'uicore-framework'); ?></p>
            <?php get_search_form(); ?>
            </div>
		<?php
    }


    function get_the_breadcrumb()
    {
        $breadcrumb = array();

        $breadcrumb[] = [
            // Add Blog Title if is blog
            'title' => get_the_title(get_option('page_for_posts')),
            'url' => get_the_permalink(get_option('page_for_posts')),
        ];
        $breadcrumb = PageTitle::get_taxonomy_breadcrumb($breadcrumb, 'category');

        $breadcrumb[] = [
            'title' => single_post_title(null, false),
            'url' => null,
        ];
        //display
        PageTitle::display_breadcrumb($breadcrumb, 'div');
    }

    function get_the_author_box()
    {
        if(Helper::get_option('blogs_author_style') === 'simple page title'){
            echo '<hr/>';
        }
        ?>

        <div class="ui-author-box">
            <?php echo sprintf(
                '<a href="%1$s" title="%2$s" rel="author">%3$s</a>',
                esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
                /* translators: %s: Author's display name. */
                esc_attr( sprintf( __( 'View %s&#8217;s posts', 'uicore-framework' ), get_the_author() ) ),
                '<img src="' . esc_url( get_avatar_url(get_the_author_meta( 'ID' ), array('size' => 450)) ) . '" />'
            );?>
            <span>
                <h4> <?php echo get_the_author_posts_link(); ?></h4>
                <?php
                if(get_the_author_meta( 'url' )){
                    echo sprintf(
                        '<a href="%1$s" title="%2$s" class="author-url" rel="author external">%3$s</a>',
                        esc_url( get_the_author_meta( 'url' ) ),
                        /* translators: %s: Author's display name. */
                        esc_attr( sprintf( __( 'Visit %s&#8217;s website','uicore-framework' ), get_the_author() ) ),
                        get_the_author_meta( 'url' )
                    );
                }
                ?>
                <p> <?php the_author_meta('description'); ?></p>
            </span>
        </div>
        <?php
    }

    function get_related_posts($style)
    {
        require_once UICORE_INCLUDES . '/blog/class-related-posts.php';

        if($style != 'grid'){
            echo '<hr/>';
        }


        \ob_start();
        new RelatedPost();
        $related_posts = \ob_get_clean();

        // if no related posts found don't display the title;
        if(!$related_posts){
            return;
        }


        ?>
        <section class="ui-related-posts uicore-section<?php echo ($style === 'grid') ? ' uicore-box' : ''?>">
            <div class="uicore <?php echo ($style === 'grid') ? ' uicore-container' : ''?>">
                <div class="uicore-row">
                    <h3><?php esc_html_e('You may also like','uicore-framework') ?></h3>
                   <?php echo $related_posts; ?>
                </div>
            </div>
        </section>
        <?php
    }

    // Function to display the filter component for blog post categories
    function get_filters() {
        $all_categories_label = Helper::get_option('blog_filters_all_text');
        // Get all categories for blog posts
        $categories = get_categories(array(
            'taxonomy' => 'category', // Taxonomy name
            'orderby' => 'name',      // Order by category name
            'order' => 'ASC',         // Sort categories in ascending order
        ));

        // Check if there are categories
        if ($categories) {
            ?>
            <div class="ui-post-filter">
                <!-- Display select dropdown for mobile -->
                <select id="ui-category-filter">
                    <option value=""><?php echo \esc_html($all_categories_label); ?></option>
                    <?php foreach ($categories as $category) : ?>
                        <option value="<?php echo esc_url(get_category_link($category->term_id)); ?>"><?php echo esc_html($category->name); ?></option>
                    <?php endforeach; ?>
                </select>
                <!-- Display list of category links for desktop -->
                <ul class="ui-category-list">
                    <li><a href="<?php echo esc_url(get_post_type_archive_link( 'post' )); ?>" class="ui-category-link <?php echo (is_home() || is_front_page()) ? 'ui-active' : ''; ?>"><?php echo \esc_html($all_categories_label); ?></a></li>
                    <?php foreach ($categories as $category) : ?>
                        <li><a href="<?php echo esc_url(get_category_link($category->term_id)); ?>" class="ui-category-link <?php echo (is_category($category->term_id)) ? 'ui-active' : ''; ?>"><?php echo esc_html($category->name); ?></a></li>
                    <?php endforeach; ?>
                </ul>
            </div>

            <script>
                // Handles filter selection
                document.addEventListener('DOMContentLoaded', function() {
                    var filter = document.getElementById('ui-category-filter');
                    if (filter) {
                        filter.addEventListener('change', function() {
                            var selectedCategory = filter.value;
                            if (selectedCategory) {
                                window.location.href = selectedCategory;
                            } else {
                                window.location.href = '<?php echo esc_url(get_post_type_archive_link( 'post' )); ?>';
                            }
                        });
                    }
                });
            </script>

            <?php
        }
    }

}

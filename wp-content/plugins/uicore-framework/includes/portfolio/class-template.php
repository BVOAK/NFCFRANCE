<?php
namespace UiCore\Portfolio;

use UiCore\Helper;
use UiCore\Pagination as Pagination;
defined('ABSPATH') || exit();

/**
 * Frontend Portfolio Archive and Single
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 2.0.2
 */
class Template{

    function __construct($type = 'full')
    {
        if ($type == 'full') {
            if (is_single()) {
                if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'single' ) ) {
                    $this->render_portfolio_single();
                }
            } elseif (is_archive()) {
                if ( ! function_exists( 'elementor_theme_do_location' ) || ! elementor_theme_do_location( 'archive' ) ) {
                    $this->render_portfolio_archive();
                }
            }
        }
    }

    /**
     * render_portfolio_single
     *
     * @return void
     */
    public function render_portfolio_single()
    {
        $this->single_layout();
    }

    public function render_portfolio_archive()
    {
        ?>
        <main id="main" class="site-main uicore-section uicore-box uicore">
        <div class="uicore uicore-container uicore-content-wrapper uicore-portfolio-animation">
            <div class="uicore-archive uicore-post-content">
                <?php


                do_action('uicore__before_portfolio_archive');


                $this->portfolio_layout();
                new Pagination();

                do_action('uicore__after_portfolio_archive');

                
                ?>
            </div>
            <?php do_action('uicore_sidebar'); ?>
        </div>
    </main>
    <?php
    }

    /**
     * portfolio_layout
     *
     * @return void
     */
    public function portfolio_layout($wp_query = false, $grid_typee = null, $col_no = null, $effect = null)
    {

        $is_widget = ' uicore-portfolio-widget';
        if (!$wp_query && $grid_typee === null) {
            $is_widget = ' ';
            global $wp_query;
        }
        //global variables
        $grid_type = $grid_typee ?? Helper::get_option('portfolio_layout', 'masonry');
        $hover_effect = Helper::get_option('portfolio_hover_effect', 'zoom');
        $ratio = Helper::get_option('portfolio_ratio', 'square');
        $hover_effect = $effect ?? str_replace(' ', '-', $hover_effect);

        $col_no = $col_no ?? Helper::get_option('portfolio_col', '3');
        if ($col_no == '2') {
            $col = '6';
        }
        if ($col_no == '3') {
            $col = '4';
        }
        if ($col_no == '4') {
            $col = '3';
        }

        $grid_space = Helper::get_option('portfolio_col_space');
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

        $justified_size = Helper::get_option('portfolio_justified_size', 'medium');
        if ($justified_size == 'large') {
            $justified_size = 600;
        }
        if ($justified_size == 'medium') {
            $justified_size = 300;
        }
        if ($justified_size == 'small') {
            $justified_size = 150;
        }

        echo '<div class="uicore-grid-container uicore-portfolio-grid uicore-grid-row';
        echo ' uicore-' . $grid_type;
        echo ' uicore-' . $ratio . '-ratio';
        echo $is_widget;
        if ($grid_type !== 'justified') {
            echo ' animate-'.$col_no;
            echo ' uicore-' . $grid_space . '-space';
        }

        if ($grid_type == 'masonry' || $grid_type == 'masonry-tiles' || $grid_type == 'justified') {
            wp_enqueue_script('uicore-grid');
            $type = str_replace('-tiles', '', $grid_type);
            echo '" data-grid="' . $type . '"';
            if ($grid_type == 'justified') {
                echo ' data-size="' . $justified_size . '"';
            }
            echo ' data-col="' . $col_no . '"';
            echo ' data-space="' . $grid_space_no . '">';
        } else {
            echo '">';
        }

        //img size
        $img_size = \apply_filters('uicore-default-portfolio-img-size', 'uicore-medium');

        //Start the loop
        while ($wp_query->have_posts()) {

            $wp_query->the_post();
            global $post;

            //post specific varables
            $post_link = get_permalink();
            $terms = get_the_terms($post->ID, 'portfolio_category');
            $category = false;
            $post_thumbnail = false;
            if ($terms) {
                foreach ($terms as $t) {
                    $term_name[] = $t->name;
                }
                $category = implode('  •  ', $term_name);
                $term_name = null;
            }

            //get the post thumbnail
            if (!empty(get_the_post_thumbnail())) {
                if ($grid_type == 'grid' || $grid_type == 'tiles') {
                    $post_thumbnail =
                        '  <div class="uicore-portfolio-img-container uicore-zoom-wrapper">
                                            <div class="uicore-cover-img" style="background-image: url(' .
                        get_the_post_thumbnail_url($post->ID, $img_size) .
                        ')"></div>
                                        </div>';
                } elseif ($grid_type == 'justified') {
                    $post_thumbnail =
                        '  <div class="uicore-portfolio-img-container uicore-zoom-wrapper">
                                            <div class="uicore-cover-img" data-thumbnail="' .
                        get_the_post_thumbnail_url($post->ID, $img_size) .
                        '"></div>
                                        </div>';
                } elseif ($type == 'masonry') {
                    $post_thumbnail =
                        '  <div class="uicore-portfolio-img-container uicore-zoom-wrapper">
                                            <img class="uicore-cover-img" src="' .
                        get_the_post_thumbnail_url($post->ID, $img_size) .
                        '"  alt="' .
                        esc_attr(get_the_title()) .
                        '"/>
                                        </div>';
                }
            }

            echo '<div class="uicore-grid-item uicore-animate ';
            if ($grid_type != 'justified') {
                echo 'uicore-col-lg-' . $col;
                if ($col != '12') {
                    echo ' uicore-col-md-6';
                }
            }
            echo ' uicore-' . $hover_effect;
            echo '" >';
            ?>
                <article <?php post_class( 'uicore-post' ); ?>>
                    <a class="uicore-post-wrapper" href="<?php echo esc_url($post_link); ?>" title="<?php echo esc_html(
    get_the_title()
); ?>">

                        <?php echo $post_thumbnail; ?>

                        <div class="uicore-post-info">
                            <div class="uicore-post-info-wrapper">
                                <h4 class="uicore-post-title"><?php echo esc_html(get_the_title()); ?></h4>
                                <?php if ($category) { ?>
                                <div class="uicore-post-category uicore-body">
                                    <?php echo $category; ?>
                                </div>
                                <?php } ?>
                            </div>
                        </div>

                    </a>
                </article>
            </div>
        <?php
        }
        wp_reset_query();
        echo '</div>'; //.uicore-post-list END
    }

    public function single_layout()
    {
        $navigation = (Helper::get_option('portfolios_navigation', 'false') === 'false' ? false : true);
        ?>
        <main id="main" class="site-main uicore-section uicore-box uicore">
            <?php
            global $post;
            while (have_posts()):
                the_post(); 
                //check if the page is built with elementor
                if(\class_exists('\Elementor\Plugin') && isset($post->ID) && $post->ID){
                    $is_builder = \Elementor\Plugin::$instance->documents->get( $post->ID )->is_built_with_elementor();
                }

                if(!$is_builder && \class_exists('\UiCoreBlocks\Frontend') && isset($post->ID) && $post->ID){
                    $is_builder = \UiCoreBlocks\Frontend::is_built_with_blocks();
                }
                $portfolio_single_sidebar = Helper::get_option('portfolios_sidebar_id') !== 'none' ? true : false;
                if ( $is_builder && !$portfolio_single_sidebar) { 
                    $css_class = '';
                }else{
                    $css_class = ' uicore-container';
                }
                
            ?>
            <div class="uicore uicore-content-wrapper<?php echo $css_class; ?>">
                <div class="uicore-type-post uicore-type-portfolio uicore-post-content">
                    <?php


                    /**
                     * Fires inside mobile menu content.
                     *
                     * @since 5.0.6
                     *
                     */
                    do_action('uicore__before_portfolio_single');


                    ?>
                    <div class="entry-content">
                        <?php the_content(
                            sprintf(
                                wp_kses(
                                    /* translators: %s: Name of current post. Only visible to screen readers */
                                    \_x('Continue reading<span class="screen-reader-text"> "%s"</span>', 'Frontend - Portfolio', 'uicore-framework'),
                                    [
                                        'span' => [
                                            'class' => [],
                                        ],
                                    ]
                                ),
                                get_the_title()
                            )
                        ); ?>
                    </div>

                    <?php


                     /**
                     * Fires inside mobile menu content.
                     *
                     * @since 5.0.6
                     *
                     */
                    do_action('uicore__after_portfolio_single');


                    if($navigation){
                        echo '<div class="uicore-section uicore-box">';
                        echo '<div class="uicore-container">';
                            Helper::get_post_navigation(esc_attr_x('Previous Item', 'Frontend - Portfolio', 'uicore-framework'), esc_attr_x('Next Item', 'Frontend - Portfolio', 'uicore-framework'));
                        echo '</div>';
                        echo '</div>';
                    }
                    do_action('uicore_after_portfolio_single');
                    ?>
                </div>
                <?php do_action('uicore_sidebar', $post); ?>
            </div>
            <?php                     
            endwhile;
            // End of the loop.
            ?>
        </main>
    <?php
    }
}

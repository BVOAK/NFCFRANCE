<?php
namespace UiCore\Blog;

use UiCore\Helper;

defined('ABSPATH') || exit();

/**
 * Related Post Component
 */
class RelatedPost
{
	private $col;
	private $type;

    function __construct($filter = false, $style = false)
    {

        // 'blogs_related'				=> 'true',
        // 'blogs_related_filter'		=> 'random', (category, tag, random)
        // 'blogs_related_style'		=> 'grid', (grid, list)

        $filter = $filter ? $filter : Helper::get_option('blogs_related_filter');
        $style = $style ? $style : Helper::get_option('blogs_related_style');



        if ($style === 'grid') {
			$this->type = Helper::get_option('blog_layout');
			$this->type = ($this->type != 'horizontal') ? 'grid' : $this->type;
            $post_to_get = (int) Helper::get_option('blog_col');
			if ($this->type == 'horizontal') {
				$post_to_get = 2;
			} else if ($post_to_get <= 2) {
				$post_to_get = 3;
			}


        } else {
            $post_to_get = 4;
        }
		$this->col = $post_to_get;

        $related = Helper::get_related($filter, $post_to_get);

        //display related only if there is any
        if($related){
            if ($filter == 'category' && $related) {
                $this->display_related($style, $related);
            } elseif ($filter == 'tag' && $related) {
                $this->display_related($style, $related);
            } else {
                $this->display_related($style, Helper::get_related('random', $post_to_get));
            }
        }
    }


    function display_related($style, $wp_query)
    {

        if ($style === 'grid'){
            Frontend::frontend_css(true);
            $blog = new Template('display',get_post_type());
            $blog->blog_layout($wp_query, $this->type, $this->col);
        }else{
            self::display_blog_list($wp_query);
        }

    }

    public static function display_blog_list($related)
    {
        if($related){
            while ($related->have_posts()) {
                $related->the_post();
                ?>
                <h4> <a href="<?php the_permalink(); ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute(); ?>"><span><?php the_title(); ?></span></a> </h4>
                <?php
            }
        }

        wp_reset_query();
    }

}

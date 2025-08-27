<?php
namespace UiCore;

defined('ABSPATH') || exit();


/**
 *  Tutor LMS Support
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 3.0.0
 */
class Tutor
{

    function __construct()
    {
        //TUTOR LMS css variables hack
        add_action('wp_enqueue_scripts', function(){
            //Add tutor lms globals
            $after = wp_styles()->get_data( 'tutor-frontend', 'after' );
            if ( ! $after ) {
                $after = array();
            }
            // array_unshift($after , self::load_color_palette());
            \array_push($after, self::load_color_palette());
            wp_styles()->add_data( 'tutor-frontend', 'after', $after );
        });

        //TUTOR LMS Tag Archive template fix
        add_filter( 'template_include', [$this, 'load_course_archive_template'], 99 );
        add_filter( 'tutor_course_archive_pagination', [$this, 'load_course_custom_pagination']);

    }

    /**
     * Default color from Global Colors
     *
     * @return string (css)
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    static function load_color_palette()
    {
        $tutor_css = ":root{";
        $tutor_css .= " --tutor-color-primary: ".Helper::get_option("pColor").";";
        $tutor_css .= " --tutor-color-primary-rgb: ".self::get_rgb_from_color( Helper::get_option("pColor") ).";";
        $tutor_css .= " --tutor-color-primary-hover:  ".Helper::get_option("sColor").";";
        $tutor_css .= " --tutor-body-color:  ".Helper::get_option("bColor").";";
        $tutor_css .= " --tutor-color-gray:  ".Helper::get_option("lColor").";";
        $tutor_css .= " --tutor-border-color:  ".Helper::get_option("lColor").";";
        $tutor_css .= "}";

		return $tutor_css;
    }

    /**
     * Fix for course archive template on tags
     *
     * @param [type] $template
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 6.0.0
     */
    public function load_course_archive_template($template){
		global $wp_query;

		$post_type = get_query_var('post_type');
		$course_category = get_query_var('course-tag');

		if ( ($post_type === 'course' || ! empty($course_category) )  && $wp_query->is_archive){
			$template = tutor_get_template('archive-course');
			return $template;
		}

		return $template;
	}

    /**
     * Replace Pagination with our function
     *
     * @param string $content
     * @return string (pagination markup)
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function load_course_custom_pagination($content)
    {
        if(!class_exists('UiCore\Pagination')){
            require UICORE_INCLUDES . '/templates/pagination.php';
        }
        ob_start();
        new Pagination();
        $content = ob_get_clean();
        return $content;
    }

    static function get_rgb_from_color($color)
    {
        $color = str_replace('#', '', $color);
        if (strlen($color) > 3) {
            $rgb = array(
                'r' => hexdec(substr($color, 0, 2)),
                'g' => hexdec(substr($color, 2, 2)),
                'b' => hexdec(substr($color, 4, 2))
            );
        } else {
            $color = str_replace('#', '', $color);
            $r = substr($color, 0, 1) . substr($color, 0, 1);
            $g = substr($color, 1, 1) . substr($color, 1, 1);
            $b = substr($color, 2, 1) . substr($color, 2, 1);
            $rgb = array(
                'r' => hexdec($r),
                'g' => hexdec($g),
                'b' => hexdec($b)
            );
        }
        //retun rgb as string "255,255,255"
        return  implode(',', $rgb);
    }
}
new Tutor;
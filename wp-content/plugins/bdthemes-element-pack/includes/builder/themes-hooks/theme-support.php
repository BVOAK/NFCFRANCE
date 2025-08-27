<?php

namespace ElementPack\Builder\Themes_Hooks;

defined('ABSPATH') || exit;

/**
 * Force fully replace the header footer.
 */
class Themes_Support {


    /**
     * Run all the Actions / Filters.
     */
    function __construct($template_ids) {
        if ($template_ids[0] != null) {
            add_action('get_header', array($this, 'get_header'));
        }
        if ($template_ids[1] != null) {
            add_action('get_footer', array($this, 'get_footer'));
        }
        // if($template_ids[2] != null) {
		// 	// require_once BDTEP_INC_PATH . 'builder/themes-hooks/views/archive-post.php';
		// 	require_once BDTEP_INC_PATH . 'builder/themes-hooks/views/single-post.php';
        // }
       
    }

    public function get_header($name) {
        require_once BDTEP_INC_PATH . 'builder/themes-hooks/views/theme-support-header.php';

        $templates = array();
        $name      = (string) $name;
        if ('' !== $name) {
            $templates[] = "header-{$name}.php";
        }

        $templates[] = 'header.php';

        // Avoid running wp_head hooks again
        remove_all_actions('wp_head');
        ob_start();
        // It cause a `require_once` so, in the get_header it self it will not be required again.
        locate_template($templates, true);
        ob_get_clean();
    }

    public function get_footer($name) {
        require BDTEP_INC_PATH . 'builder/themes-hooks/views/theme-support-footer.php';

        $templates = array();
        $name      = (string) $name;
        if ('' !== $name) {
            $templates[] = "footer-{$name}.php";
        }

        $templates[] = 'footer.php';

        ob_start();
        // It cause a `require_once` so, in the get_header it self it will not be required again.
        locate_template($templates, true);
        ob_get_clean();
    }
}

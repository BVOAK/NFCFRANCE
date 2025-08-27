<?php
namespace UiCore;

defined('ABSPATH') || exit();


/**
 *  Performance styles manager
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 3.0.0
 */
class Performance
{

    function __construct()
    {

        //Preload
        add_action('wp_head', [$this, 'add_preload'], 2);


        if(Helper::get_option('performance_emojy') === 'false'){
            remove_action('wp_head', 'print_emoji_detection_script', 7);
            remove_action('wp_print_styles', 'print_emoji_styles');

            remove_action( 'admin_print_scripts', 'print_emoji_detection_script' );
            remove_action( 'admin_print_styles', 'print_emoji_styles' );
        }
        if(Helper::get_option('performance_embed') === 'false'){
            wp_deregister_script('wp-embed');
        }

        if(Helper::get_option('performance_fa') === 'false'){
            add_action('elementor/frontend/after_register_styles',function() {
                foreach( [ 'solid', 'regular', 'brands' ] as $style ) {
                    wp_deregister_style( 'elementor-icons-fa-' . $style  );
                    wp_dequeue_style( 'elementor-icons-fa-' . $style );
                }
            }, 20 );
        }
        if(Helper::get_option('performance_eicon') === 'false'){
            add_action( 'elementor/frontend/after_enqueue_styles', function() {
                // Don't remove it in the backend
                if ( is_admin() || current_user_can( 'manage_options' ) ) {
                        return;
                }
                wp_dequeue_style( 'elementor-icons' );
            });
        }

        //Disable Default elementor animations ( new elementor version )
        add_action('elementor/frontend/after_enqueue_scripts',function() {
            wp_deregister_style('e-animations' );
            wp_dequeue_style( 'e-animations' );
        }, 20 );


        //via generic action
        add_action( 'wp_enqueue_scripts', [$this, 'remove_css'], 101 );

        //Elementor triks
        add_action('wp_enqueue_scripts', [$this, 'frontend_elementor_css'], 50);

        //disable wp global styles
        remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
        remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );

    }


    /**
     * Remove resources based on performance settings
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function remove_css() {
        if(!class_exists('\Elementor\Plugin')){
            $url = 'https://fonts.googleapis.com/css?family=';
            if(is_array($fonts = get_option('uicore_fonts_array'))){
                foreach($fonts as $font){
                    if($font['fam']){
                        $url .= str_replace(' ', '+', $font['fam'] ) . ':' . $font['style'] . '|';
                    }
                }
                // wp_enqueue_style('uicore_global_fonts', $url);
            }

        }else{
            if(is_array($fonts = get_option('uicore_fonts_array'))){
                foreach($fonts as $font){
                    if($font['fam'] && !in_array($font['fam'], \Elementor\Plugin::instance()->frontend->fonts_to_enqueue))
                    \Elementor\Plugin::instance()->frontend->fonts_to_enqueue[] = $font['fam'];
                }
            }
        }

        if(Helper::get_option('performance_block_style') === 'false'){
            wp_dequeue_style( 'wp-block-library' );
            wp_dequeue_style( 'wp-block-library-theme' );
            wp_dequeue_style( 'wc-block-style' );
        }


        // // Get all scripts.
        // $scripts = wp_scripts();


        // // Array of handles to remove.
        // $handles_to_remove = [];

        // // Flag indicating if we have removed the handles.
        // $handles_updated = false;

        // // Remove desired handles from the elementor-frontend script.
        // foreach ( $scripts->registered as $dependency_object_id => $dependency_object ) {

        //     if ( 'elementor-frontend' === $dependency_object_id ) {

        //         // Bail if something went wrong.
        //         if ( ! ( $dependency_object instanceof \_WP_Dependency ) ) {
        //             return;
        //         }

        //         // Bail if there are no dependencies for some reason.
        //         if ( empty( $dependency_object->deps ) ) {
        //             return;
        //         }

        //         // Do the handle removal.
        //         foreach ( $dependency_object->deps as $dep_key => $handle ) {
        //             if ( in_array( $handle, $handles_to_remove ) ) {
        //                 unset( $dependency_object->deps[ $dep_key ] );
        //                 $dependency_object->deps = array_values( $dependency_object->deps );  // "reindex" array
        //                 $handles_updated = true;
        //             }
        //         }
        //     }
        // }

        // // If we have updated the handles, dequeue the relevant dependencies which
        // // were enqueued separately Elementor\Frontend.
        // if ( $handles_updated ) {
        //     //wp_dequeue_script();
        //     //wp_deregister_script();
        // }
    }

    /**
     * Force elementor to add kit fonts and remove legacy styles already added to global styles
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co>
     * @since 3.0.0
     */
    function frontend_elementor_css()
    {

        $kit_id = get_option('elementor_active_kit');
        if (class_exists('\Elementor\Plugin')) {
            //Add kit just to be sure it loads the required fonts but dequeue it later
            $post_css_file = new \Elementor\Core\Files\CSS\Post($kit_id);
            $fonts = $post_css_file->enqueue();
        }
        //Now dequeue
        if($kit_id && 'internal' != get_option( 'elementor_css_print_method' )){
            wp_dequeue_style( 'elementor-post-'.$kit_id );
        }

        //use our css for animations (old elementor version / to be removed in the future)
        wp_dequeue_style( 'elementor-animations' );
    }

    /**
     * Add Preload
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 3.0.0
     */
    function add_preload()
    {
        global $post;
        $preload = Helper::get_option('performance_preload');
        $page_perload = Helper::po('performance_preload',false,[],$post->ID ?? 0);
        $preload = \wp_parse_args($page_perload,$preload);
        if( is_array($preload) && count($preload) ) {
            foreach($preload as $item){
                if(strlen($item['url'])){
                    echo '<link rel="preload" href="'.esc_url($item['url']).'" as="'.$item['as'].'"> ';
                }
            }
        }

        $fonts = Helper::get_option('customFonts');
        if( count($fonts) ) {
            foreach($fonts as $font){
                foreach($font['variants'] as $variant){
                    if($variant['src']['woff']){
                        echo '<link rel="preload" href="'.$variant['src']['woff'].'" as="font" type="font/woff" crossorigin="anonymous"> ';
                    }else if($variant['src']['ttf']){
                        echo '<link rel="preload" href="'.$variant['src']['ttf'].'" as="font" type="font/ttf" crossorigin="anonymous"> ';
                    }else if($variant['src']['eot']){
                        echo '<link rel="preload" href="'.$variant['src']['ttf'].'" as="font" type="font/eot" crossorigin="anonymous"> ';
                    }
                }
            }
        }
    }
}


<?php
namespace UiCore;

defined('ABSPATH') || exit();


/**
 * Studio ui and functions
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 1.0.0
 */
class Studio
{
    /**
     * Construct Frontend
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function __construct() {
        //studio editor
        if (isset($_GET['uistudio']) && $_GET['uistudio'] === 'true') {
            add_action('template_redirect', [$this, 'render_page']);
        }

        //studio preview
        if (isset($_GET['uistudio_preview']) && $_GET['uistudio_preview'] === 'true') {
            //remove admin bar
            add_filter('show_admin_bar', '__return_false');
            add_action('wp_footer', [$this, 'enqueue_preview_scripts']);
        }
        // add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
        // add_action( 'admin_menu', array( $this, 'add_menu_item' ) );
        // add_action( 'wp_ajax_uicore_studio_save_data', array( $this, 'ajax_save_data' ) );
        // add_action( 'wp_ajax_nopriv_uicore_studio_save_data', array( $this, 'ajax_save_data' ) );
    }


    public function add_menu_item() {
        add_submenu_page( 'themes.php', 'UI Studio', 'UI Studio', 'manage_options', 'uicore-studio', array( $this, 'render_page' ) );
    }

    public function render_page() {

        if ( current_user_can( 'manage_options' )) {

            $iframe_url = add_query_arg( 'uistudio_preview', 'true', home_url() ); // set the URL for the iframe

            ?>

            <!DOCTYPE html>
            <html>
            <body class="uicore-studio">

            <div class="uicore-studio-wrap">
                <iframe id="uicore-studio-iframe" src="<?php echo  esc_url( $iframe_url ); ?>"></iframe>
            </div>

            <?php
                // Add the stylesheet
                self::enqueue_scripts();
                \do_action('uicore/studio/editor');
            ?>
            </body>
            </html>



            <?php
        }
        exit;
    }

    public function enqueue_scripts() {
        // Add the stylesheet
        echo '<link rel="preconnect" href="https://fonts.googleapis.com">';
        echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>';
        echo '<link href="https://fonts.googleapis.com/css2?family=Inter:wght@500;600&display=swap" rel="stylesheet">';
        echo '<link rel="stylesheet" id="uicore-studio" href="' .  UICORE_ASSETS . '/css/studio.css">';
    
        wp_print_scripts('jquery');
        // Add the script
        // echo '<script id="uicore-studio-manifest" src="' . UICORE_ASSETS . '/js/manifest.js"></script>';
        echo '<script id="uicore-studio-vendor" src="' . UICORE_ASSETS . '/js/vendor.js"></script>';
        echo '<script id="uicore-studio" src="' . UICORE_ASSETS . '/js/studio.js"></script>';
       
    
        // Add the inline script
        $uicore_studio_data = [
            'nonce' => wp_create_nonce('wp_rest'),
            'rest_url' => rest_url(),
            'data' => Settings::get_studio_data(),
            'settings' => Settings::current_settings()
        ];
        echo '<script>var uicore_studio = ' . json_encode($uicore_studio_data) . ';</script>';
    
        // Add the inline script
        $uicore_studio_data = [
            'assets_path' => UICORE_ASSETS,
            'pages' => Data::get_pages(),
        ];
        echo '<script>var uicore_data = ' . json_encode($uicore_studio_data) . ';</script>';
    }

    public function enqueue_preview_scripts() {
        echo "<script>
        console.log('dada');
        // Send a message to the parent page when the button is clicked

    jQuery( document ).ready( function( $ ) {
    
        $('[data-uils]').click(function(event) {
    
            // stop event from bubbling up the DOM tree
            event.stopPropagation();
            event.preventDefault();
            
            //remove previos
            $('.ui-ls-active').removeClass('ui-ls-active');
            $('.ui-ls-tools').remove();
        
            //add on our element
            $(this).addClass('ui-ls-active');
    
            // Get the data-uils attribute value
            window.uiStudioModel = $(this).attr('data-uils');
            
            // Create the tools container
            const toolsContainer = $('<div class=\"ui-ls-tools\"></div>');
    
            // Create the tool elements and add them to the container
            const settingTrigger = $('<div class=\"ui-ls-tool ui-ls-settings\"></div>');
            const styleTrigger = $('<div class=\"ui-ls-tool\"></div>');
            const animationsTrigger = $('<div class=\"ui-ls-tool\"></div>');
            toolsContainer.append(settingTrigger, styleTrigger, animationsTrigger);
    
            
            // Add the tools container to element
            $(this).append(toolsContainer);
    
            
            // Add click event listeners to each tool element
            settingTrigger.on('click', function() {
                parent.postMessage({ 'action': 'settings', 'model':uiStudioModel }, '*');
            });
    
            styleTrigger.on('click', function() {
                parent.postMessage({ 'action': 'style', 'model':uiStudioModel }, '*');
            });
    
            animationsTrigger.on('click', function() {
                parent.postMessage({ 'action': 'animation', 'model':uiStudioModel }, '*');
            });
    
        });
    
    });

        </script>";
    }
    

}
new Studio();

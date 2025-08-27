<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Search Render
 *
 * @author Andrei Voica <andrei@uicore.co
 * @since 1.0.0
 */
class Search
{
    /**
     * Add Search Hook
     *
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    function __construct()
    {
        if(Helper::get_option('header') === 'true' && Helper::get_option('header_search') === 'true'){
            add_action('uicore_body_end', [$this, 'search_display']);
        }
    }

    /**
     * Display Search
     *
     * @return void
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
    public function search_display()
    {
        // prettier-ignore
        ?>
        <div class="uicore uicore-wrapper uicore-search uicore-section uicore-box ">
            <span class="uicore-close uicore-i-close"></span>
            <div class="uicore uicore-container">
                <form role="search" method="get" autocomplete="on" class="search-form" action="<?php echo home_url(
                    '/'
                ); ?>">
                    <label>
                        <input class="search-field" placeholder="<?php _ex( 'Type and hit enter', 'Frontend - FullScreen Search', 'uicore-framework'); ?>" value="" name="s" title="Start Typing" />
                    </label>
                </form>
            </div>
        </div>
    <?php
    }
}

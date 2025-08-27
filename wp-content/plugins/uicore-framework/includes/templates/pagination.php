<?php
namespace UiCore;
defined('ABSPATH') || exit();

/**
 * Here we generate the header
 */
class Pagination
{
    /**
     * sajdnek
     *
     * @param array $args
     * @param string $class
     * @author Andrei Voica <andrei@uicore.co
     * @since 1.0.0
     */
     function __construct($args = [])
    {
       global $query;
       
        $args = wp_parse_args($args, [
            'mid_size' => 2,
            'prev_next' => true,
            'prev_text' => null,
            'next_text' => null,
            'screen_reader_text' => _x('Posts navigation', 'Frontend - Pagination', 'uicore-framework'),
            'type' => 'array',
            'current' => max(1, get_query_var('paged')),
        ]);
        if (class_exists('WooCommerce') && isset($query->query['post_type']) && $query->query['post_type'] == 'product' ) {
            if ( ! wc_get_loop_prop( 'is_paginated' ) || ! woocommerce_products_will_display() ) {
                return;
            }
            
            $total = wc_get_loop_prop('total_pages',false);
            if ($total && $total <= 1) {
                return;
            } elseif ($total) {
                $args = apply_filters('woocommerce_pagination_args', [
                    // WPCS: XSS ok.
                    'current' => max(1, wc_get_loop_prop('current_page')),
                    'total' => $total,
                    'prev_text' => '',
                    'next_text' => '',
                    'type' => 'array',
                    'base'    => esc_url_raw( add_query_arg( 'product-page', '%#%', false ) ),
                    'screen_reader_text' => _x('Products navigation', 'Frontend - Pagination', 'uicore-framework'),
                ]);
                if ( ! wc_get_loop_prop( 'is_shortcode' ) ) {
                    $args['format'] = '';
                    $args['base']   = esc_url_raw( str_replace( 999999999, '%#%', remove_query_arg( 'add-to-cart', get_pagenum_link( 999999999, false ) ) ) );
                }
            }
        }
        $links = paginate_links($args);
        if (is_array($links) || is_object($links)) { ?>
		<nav aria-label="<?php echo esc_html($args['screen_reader_text']); ?>" class="uicore-pagination">
			<ul>
			<?php foreach ($links as $link) { ?>
                <li class="uicore-page-item <?php echo strpos($link, 'current') ? 'uicore-active' : ''; ?>">
                    <?php echo str_replace('page-numbers', 'uicore-page-link', $link); ?>
					</li>
                    <?php } ?>
			</ul>
		</nav>
		<?php }
    }
}

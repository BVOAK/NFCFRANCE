<?php
defined('ABSPATH') || exit();
//INCLUDED IN CLASS CSS

$css .= '
.uicore-widget ul li a {
	color:' . $this->color( $json_settings['h4']['c']) . '
}

.uicore-widget ul li .post-date {
	color:' . $this->color( $json_settings['p']['c']) .
	'
}

';

$css .=
' .tags-links a, tags-links a:hover,
.uicore-widget ul li a, [class*="elementor-widget-wp-widget-"] ul li a,
.uicore-widget .tagcloud a, [class*="elementor-widget-wp-widget-"] .tagcloud a,
.uicore-widget .wp-block-tag-cloud a,
.uicore-sidebar .uicore-widget .wp-block-tag-cloud a:hover,
.uicore-widget #wp-calendar, [class*="elementor-widget-wp-widget-"] #wp-calendar,
.uicore-widget #wp-calendar a, [class*="elementor-widget-wp-widget-"] #wp-calendar a,
.uicore-widget select, [class*="elementor-widget-wp-widget-"] select,
.uicore-widget .price_slider_wrapper .price_slider_amount .price_label,
#woocommerce-product-search-field-0,
input[type=text] {
color: ' . $this->color( $json_settings['h4']['c']) . ';
}
.uicore-widget .tagcloud a .tag-link-count, [class*="elementor-widget-wp-widget-"] .tagcloud a .tag-link-count {
color: ' . $this->color($json_settings['p']['c']) .
';
}
';

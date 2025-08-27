<?php

namespace ElementPack\Modules\PostFeaturedImage;

use ElementPack\Base\Element_Pack_Module_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'post-featured-image';
	}

	public function get_widgets() {

		$widgets = [
			'Post_Featured_Image',
		];

		return $widgets;
	}
}

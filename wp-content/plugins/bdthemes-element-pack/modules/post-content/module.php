<?php

namespace ElementPack\Modules\PostContent;

use ElementPack\Base\Element_Pack_Module_Base;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'post-content';
	}

	public function get_widgets() {

		$widgets = [
			'Post_Content',
		];

		return $widgets;
	}
}

<?php

namespace ElementPack\Base;

use ElementPack\Includes\Builder\Builder_Widget_Base;
use ElementPack\Element_Pack_Loader;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

abstract class Module_Base extends Builder_Widget_Base {

	public function get_style_depends() {

		if ( method_exists( $this, '_get_style_depends' ) ) {
			if ( $this->ep_is_edit_mode() ) {
				return [ 'ep-all-styles' ];
			}
			return $this->_get_style_depends();
		}
		return array();
	}

	protected function ep_is_edit_mode() {

		if ( Element_Pack_Loader::elementor()->preview->is_preview_mode() || Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
			return true;
		}

		return false;
	}
}


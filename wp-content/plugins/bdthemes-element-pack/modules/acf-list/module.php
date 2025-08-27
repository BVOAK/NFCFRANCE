<?php
namespace ElementPack\Modules\AcfList;

use ElementPack\Base\Element_Pack_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'acf-list';
	}

	public function get_widgets() {
		$widgets = [
			'Acf_List',
		];
		return $widgets;
	}
}

<?php
namespace ElementPack\Modules\AcfTabs;

use ElementPack\Base\Element_Pack_Module_Base;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function get_name() {
		return 'acf-tabs';
	}

	public function get_widgets() {
		$widgets = [
			'Acf_Tabs',
		];
		return $widgets;
	}
}

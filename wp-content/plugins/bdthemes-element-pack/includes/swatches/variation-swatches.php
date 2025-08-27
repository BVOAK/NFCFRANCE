<?php
namespace ElementPack\VariationSwatches;

use ElementPack\Base\Singleton;

defined( 'ABSPATH' ) || exit;

class Variation_Swatches {

	use Singleton;

	private $props = [];

	public $mapping = null;

	public function __construct() {
		$this->require_once();
		$this->init();
		$this->set_mapping();
	}

	public function require_once() {
		require_once dirname( __FILE__ ) . '/mapping.php';
		require_once dirname( __FILE__ ) . '/helper.php';
		require_once dirname( __FILE__ ) . '/swatches.php';
		require_once dirname( __FILE__ ) . '/admin/settings.php';
		require_once dirname( __FILE__ ) . '/admin/term-meta.php';
		require_once dirname( __FILE__ ) . '/admin/product-data.php';
	}

	protected function init() {
		Swatches::instance();
	}

	public function set_mapping() {
		$this->mapping = new Mapping();
	}

	public function get_mapping() {
		return $this->mapping;
	}
}

Variation_Swatches::instance();

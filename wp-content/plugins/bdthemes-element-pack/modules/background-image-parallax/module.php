<?php

namespace ElementPack\Modules\BackgroundImageParallax;

use Elementor\Controls_Manager;
use ElementPack\Base\Element_Pack_Module_Base;

if (!defined('ABSPATH')) {
	exit;
} // Exit if accessed directly

class Module extends Element_Pack_Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'bdt-background-image-parallax';
	}

	public function register_section($element) {
		$element->start_controls_section(
			'ep_background_image_parallax_controls',
			[
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => BDTEP_CP . esc_html__('Background Image Parallax', 'bdthemes-element-pack') . BDTEP_NC,
			]
		);
		$element->end_controls_section();
	}

	public function register_controls($widget, $args) {

		$widget->add_control(
			'ep_background_image_parallax_on',
			[
				'label' => esc_html__('Parallax Effects?', 'bdthemes-element-pack'),
				'type'  => Controls_Manager::SWITCHER,
                'prefix_class' => 'bdt-background-image-parallax-',
				'render_type' => 'template'
			]
		);

		$widget->add_control(
			'ep_background_image_parallax_orientation',
			[
				'label'   => esc_html__('Orientation', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::SELECT,
				'options' => [
					'up'      => esc_html__('Up', 'bdthemes-element-pack'),
					'down'      => esc_html__('Down', 'bdthemes-element-pack'),
					'left'      => esc_html__('Left', 'bdthemes-element-pack'),
					'right'      => esc_html__('Right', 'bdthemes-element-pack'),
					'up-left'      => esc_html__('Up Left', 'bdthemes-element-pack'),
					'up-right'      => esc_html__('Up Right', 'bdthemes-element-pack'),
					'down-left'      => esc_html__('Down Left', 'bdthemes-element-pack'),
					'down-right'      => esc_html__('Down Right', 'bdthemes-element-pack'),
				],
				'default' => 'left',
				'frontend_available' => true,
				'condition' => [
					'ep_background_image_parallax_on' => 'yes'
				],
			]
		);
		$widget->add_control(
			'ep_background_image_parallax_scale',
			[
				'label'   => esc_html__('Scale', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 1.1,
						'max'  => 3,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 1.3,
				],
				'frontend_available' => true,
				'condition' => [
					'ep_background_image_parallax_on' => 'yes'
				],
			]
		);
		
		$widget->add_control(
			'ep_background_image_parallax_delay',
			[
				'label'   => esc_html__('Delay', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::SLIDER,
				'range'   => [
					'px' => [
						'min'  => 0,
						'max'  => 5,
						'step' => 0.1,
					],
				],
				'default' => [
					'size' => 0,
				],
				'frontend_available' => true,
				'condition' => [
					'ep_background_image_parallax_on' => 'yes'
				],
			]
		);
		// $widget->add_control(
		// 	'ep_background_image_parallax_transition',
		// 	[
		// 		'label'   => esc_html__('Transition', 'bdthemes-element-pack'),
		// 		'type'    => Controls_Manager::TEXT,
		// 		'placeholder' => 'cubic-bezier(0,0,0,1)',
		// 		'frontend_available' => true,
		// 		'condition' => [
		// 			'ep_background_image_parallax_on' => 'yes'
		// 		],
		// 	]
		// );
		$widget->add_control(
			'ep_background_image_parallax_overflow',
			[
				'label'   => esc_html__('Overflow', 'bdthemes-element-pack'),
				'description' => esc_html__('This option will help you to show the image overflow.', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::SWITCHER,
				'frontend_available' => true,
				'condition' => [
					'ep_background_image_parallax_on' => 'yes'
				],
			]
		);
		// $widget->add_control(
		// 	'ep_background_image_parallax_max_transition',
		// 	[
		// 		'label'   => esc_html__('Max Transition', 'bdthemes-element-pack'),
		// 		'type'    => Controls_Manager::SLIDER,
		// 		'range'   => [
		// 			'px' => [
		// 				'min'  => 0,
		// 				'max'  => 99,
		// 				'step' => 1,
		// 			],
		// 		],
		// 		'frontend_available' => true,
		// 		'condition' => [
		// 			'ep_background_image_parallax_on' => 'yes'
		// 		],
		// 	]
		// );
	}

	public function enqueue_scripts() {
		wp_register_script( 'simple-parallax-js', BDTEP_ASSETS_URL . 'vendor/js/simpleParallax.min.js', [], null );

		if ( \ElementPack\Element_Pack_Loader::elementor()->preview->is_preview_mode() || \ElementPack\Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
			wp_enqueue_script( 'simple-parallax-js' );
		}
	}

	public function should_script_enqueue($widget) {
        if ('yes' === $widget->get_settings_for_display('ep_background_image_parallax_on')) {
			$this->enqueue_scripts();
			wp_enqueue_script( 'simple-parallax-js' );
            wp_enqueue_script('ep-background-image-parallax');
        }
    }

	protected function add_actions() {
		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ], 9999 );
		add_action('elementor/element/image/section_style_image/after_section_end', [$this, 'register_section']);
		add_action('elementor/element/image/ep_background_image_parallax_controls/before_section_end', [$this, 'register_controls'], 10, 2);

		// render scripts
		add_action('elementor/frontend/widget/before_render', [$this, 'should_script_enqueue'], 10, 1);
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}
}

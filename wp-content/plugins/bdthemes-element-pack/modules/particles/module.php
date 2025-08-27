<?php

namespace ElementPack\Modules\Particles;

use ElementPack\Base\Element_Pack_Module_Base;
use Elementor\Controls_Manager;
use ElementPack;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Module extends Element_Pack_Module_Base {

	public function __construct() {
		parent::__construct();
		$this->add_actions();
	}

	public function get_name() {
		return 'bdt-particles';
	}

	public function register_section( $element ) {
		$element->start_controls_section(
			'section_background_particles_controls',
			[ 
				'tab'   => Controls_Manager::TAB_STYLE,
				'label' => BDTEP_CP . esc_html__( 'Background Particles Effects', 'bdthemes-element-pack' ),
			]
		);

		$element->end_controls_section();
	}

	public function register_controls( $widget, $args ) {

		$widget->add_control(
			'section_particles_on',
			[ 
				'label'              => BDTEP_CP . esc_html__( 'Particles Effects', 'bdthemes-element-pack' ),
				'type'               => Controls_Manager::SWITCHER,
				'default'            => '',
				'return_value'       => 'yes',
				'prefix_class'       => 'bdt-particles-',
				'separator'          => [ 'before' ],
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);

		$widget->add_control(
			'section_particles_js',
			[ 
				'label'              => esc_html__( 'Particles JSON', 'bdthemes-element-pack' ),
				'type'               => Controls_Manager::TEXTAREA,
				'condition'          => [ 
					'section_particles_on' => 'yes',
				],
				'description'        => __( 'Paste your particles JSON code here - Generate it from <a href="http://vincentgarreau.com/particles.js/#default" target="_blank">Here</a>.', 'bdthemes-element-pack' ),
				'default'            => '',
				'dynamic'            => [ 'active' => true ],
				'frontend_available' => true,
				'render_type'        => 'template',
			]
		);

		$widget->add_control(
			'section_particles_z_index',
			[ 
				'label'       => esc_html__( 'Z-Index', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'condition'   => [ 
					'section_particles_on' => 'yes',
				],
				'description' => __( 'If you need mouse activity, you can fix z-index.', 'bdthemes-element-pack' ),
				'default'     => '',
				'dynamic'     => [ 'active' => true ],
				'selectors'   => [ 
					'{{WRAPPER}} .bdt-particle-container' => 'z-index: {{VALUE}};',
				],
				'render_type' => 'template',
			]
		);
	}

	public function enqueue_scripts() {
		wp_register_script( 'particles-js', BDTEP_ASSETS_URL . 'vendor/js/particles.min.js', [], '2.0.0' );


		if ( \ElementPack\Element_Pack_Loader::elementor()->preview->is_preview_mode() || \ElementPack\Element_Pack_Loader::elementor()->editor->is_edit_mode() ) {
			wp_enqueue_script( 'particles-js' );
		}
	}

	public function should_script_enqueue( $widget ) {
		// var_dump($widget);
		if ( 'yes' === $widget->get_settings_for_display( 'section_particles_on' ) ) {
			wp_enqueue_script( 'particles-js' );
			wp_enqueue_script( 'ep-particles' );
		}
	}

	protected function add_actions() {

		add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		add_action( 'elementor/element/container/section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/container/section_background_particles_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );
		add_action( 'elementor/frontend/container/after_render', [ $this, 'should_script_enqueue' ] );

		add_action( 'elementor/element/section/section_background/after_section_end', [ $this, 'register_section' ] );
		add_action( 'elementor/element/section/section_background_particles_controls/before_section_end', [ $this, 'register_controls' ], 10, 2 );

		/**
		 * Render scripts
		 */
		add_action( 'elementor/frontend/section/after_render', [ $this, 'should_script_enqueue' ] );
		add_action( 'elementor/preview/enqueue_scripts', [ $this, 'enqueue_scripts' ] );
	}
}

<?php
namespace ElementPack\Modules\CreativeButton\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Icons_Manager;

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class Creative_Button extends Module_Base {
	public function get_name() {
		return 'bdt-creative-button';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Creative Button', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-creative-button';
	}	

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'button', 'creative', 'link', 'readmore', 'url', 'animated' ];
	}
  
	public function get_style_depends() {
		if ($this->ep_is_edit_mode()) {
			return ['ep-styles'];
		} else {
			return [ 'ep-font', 'ep-creative-button' ];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/6f2t-79MfnU';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_creative_button',
			[
				'label' => esc_html__( 'Creative Button', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'button_style',
			[
				'label'   => esc_html__( 'Style', 'bdthemes-element-pack' ) . BDTEP_UC,
				'type'    => Controls_Manager::SELECT,
				'default' => 'anthe',
				'options' => [
					'anthe'    => esc_html__( 'Anthe', 'bdthemes-element-pack' ),
					'atlas'    => esc_html__( 'Atlas', 'bdthemes-element-pack' ),
					'bestia'   => esc_html__( 'Bestia', 'bdthemes-element-pack' ),
					'calypso'  => esc_html__( 'Calypso', 'bdthemes-element-pack' ),
					'dione'    => esc_html__( 'Dione', 'bdthemes-element-pack' ),
					'fenrir'   => esc_html__( 'Fenrir', 'bdthemes-element-pack' ),
					'greip'    => esc_html__( 'Greip', 'bdthemes-element-pack' ),
					'hati'     => esc_html__( 'Hati', 'bdthemes-element-pack' ),
					'hyperion' => esc_html__( 'Hyperion', 'bdthemes-element-pack' ),
					'helene'   => esc_html__( 'Helene', 'bdthemes-element-pack' ),
					'janus'    => esc_html__( 'Janus', 'bdthemes-element-pack' ),
					'kari'     => esc_html__( 'Kari', 'bdthemes-element-pack' ),
					'mimas'    => esc_html__( 'Mimas', 'bdthemes-element-pack' ),
					'narvi'    => esc_html__( 'Narvi', 'bdthemes-element-pack' ),
					'pan'      => esc_html__( 'Pan', 'bdthemes-element-pack' ),
					'pandora'  => esc_html__( 'Pandora', 'bdthemes-element-pack' ),
					'pallene'  => esc_html__( 'Pallene', 'bdthemes-element-pack' ),
					'rhea'     => esc_html__( 'Rhea', 'bdthemes-element-pack' ),
					'skoll'    => esc_html__( 'Skoll', 'bdthemes-element-pack' ),
					'surtur'   => esc_html__( 'Surtur', 'bdthemes-element-pack' ),
					'telesto'  => esc_html__( 'Telesto', 'bdthemes-element-pack' ),
					'reklo'    => esc_html__( 'Reklo', 'bdthemes-element-pack' ),
					'elon'     => esc_html__( 'Elon', 'bdthemes-element-pack' ),
					'reveal'     => esc_html__( 'Reveal', 'bdthemes-element-pack' ),
					'glitch'   => esc_html__( 'Glitch', 'bdthemes-element-pack' ),
					'gooey'    => esc_html__( 'Gooey', 'bdthemes-element-pack' ),
				],
			]
		);

		$this->add_control(
			'text',
			[
				'label'       => esc_html__( 'Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => [ 'active' => true ],
				'default'     => esc_html__( 'Read More', 'bdthemes-element-pack' ),
				'placeholder' => esc_html__( 'Type Button Text', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'link',
			[
				'label'       => esc_html__( 'Link', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::URL,
				'dynamic'     => [ 'active' => true ],
				'placeholder' => esc_html__( 'https://your-link.com', 'bdthemes-element-pack' ),
				'default'     => [
					'url' => '#',
				],
			]
		);

		$this->add_responsive_control(
			'alignment',
			[
				'label'        => esc_html__( 'Alignment', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::CHOOSE,
				'prefix_class' => 'elementor%s-align-',
				'default'      => '',
				'options' => [
					'left'    => [
						'title' => __( 'Left', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-justify',
					],
				],
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-creative-button' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->add_control(
			'button_css_id',
			[
				'label' => __( 'Button ID', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::TEXT,
				'dynamic' => [
					'active' => true,
				],
				'default' => '',
				'title' => __( 'Add your custom id WITHOUT the Pound key. e.g: my-id', 'bdthemes-element-pack' ),
				'description' => __( 'Please make sure the ID is unique and not used elsewhere on the page this form is displayed. This field allows <code>A-z 0-9</code> & underscore chars without spaces.', 'bdthemes-element-pack' ),
				'separator' => 'before',
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_content_style',
			[
				'label'     => esc_html__( 'Creative Button', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_responsive_control(
			'shape_alignment',
			[
				'label'        => esc_html__( 'Shape Align', 'bdthemes-element-pack' ),
				'type'         => Controls_Manager::CHOOSE,
				'default'      => 'left',
				'options' => [
					'left'    => [
						'title' => __( 'Left', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'bdthemes-element-pack' ),
						'icon' => 'eicon-text-align-right',
					],
				],
				'selectors_dictionary' => [
					'left' => 'left: 0; right: auto; transform: translateX(0px);',
					'right' => 'left: auto; right: 0; transform: translateX(0px);',
					'center' => 'left: 50%; transform: translateX(-50%);',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--elon:before' => '{{VALUE}}',
				],
				'condition' => [
					'button_style' => ['elon']
				]
			]
		);
		$this->add_control(
			'gooey_direction',
			[
				'label' => esc_html__( 'Fill Direction', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'top',
				'options' => [
					'top'    => [
						'title' => __( 'To Top', 'bdthemes-element-pack' ),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => __( 'To Bottom', 'bdthemes-element-pack' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'selectors_dictionary' => [
					'top' => 'transform: scale(1.4) translateY(125%) translateZ(0);',
					'bottom' => 'transform: scale(1.4) translateY(-125%) translateZ(0);',
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--gooey .c-button__blobs div' => '{{VALUE}}',
				],
				'condition' => [
					'button_style' => ['gooey']
				]
			]
		);
		$this->add_control(
			'reveal_direction',
			[
				'label' => esc_html__( 'Reveal Direction', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::CHOOSE,
				'default' => 'left',
				'toggle' => false,
				'options' => [
					'left'    => [
						'title' => __( 'To Left', 'bdthemes-element-pack' ),
						'icon' => 'eicon-h-align-left',
					],
					'right' => [
						'title' => __( 'To Right', 'bdthemes-element-pack' ),
						'icon' => 'eicon-h-align-right',
					],
					'top' => [
						'title' => __( 'To Top', 'bdthemes-element-pack' ),
						'icon' => 'eicon-v-align-top',
					],
					'bottom' => [
						'title' => __( 'To Bottom', 'bdthemes-element-pack' ),
						'icon' => 'eicon-v-align-bottom',
					],
				],
				'prefix_class' => 'bdt-ep-reveal-direction-',
				'condition' => [
					'button_style' => ['reveal']
				]
			]
		);

		$this->start_controls_tabs( 'tabs_creative_button_style' );

		$this->start_controls_tab(
			'tab_creative_button_normal',
			[
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'creative_button_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button, {{WRAPPER}} .bdt-ep-creative-button--dione span' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--glitch::after' => 'color: {{VALUE}}; text-shadow: -2px -3px 0 {{VALUE}}, 2px 3px 0 {{VALUE}};',
				],
				'condition' => [
					'button_style!' => ['surtur']
				]
			]
		);

		$this->add_control(
			'creative_button_line_color',
			[
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--fenrir .progress__circle, {{WRAPPER}} .bdt-ep-creative-button--fenrir .progress__path' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--janus::after' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['fenrir', 'janus']
				]
			]
		);

		$this->add_control(
			'creative_button_stroke_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--surtur svg *' => 'stroke: {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['surtur']
				]
			]
		);

		$this->add_control(
			'creative_button_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button, {{WRAPPER}} .bdt-ep-creative-button--anthe::before, {{WRAPPER}} .bdt-ep-creative-button--bestia .bdt-ep-creative-button__bg, {{WRAPPER}} .bdt-ep-creative-button--dione::before, {{WRAPPER}} .bdt-ep-creative-button--greip::before, {{WRAPPER}} .bdt-ep-creative-button--hyperion::before, {{WRAPPER}} .bdt-ep-creative-button--janus::before, {{WRAPPER}} .bdt-ep-creative-button--mimas::before, {{WRAPPER}} .bdt-ep-creative-button--narvi::before, {{WRAPPER}} .bdt-ep-creative-button--pan::before, {{WRAPPER}} .bdt-ep-creative-button--pandora span, {{WRAPPER}} .bdt-ep-creative-button--rhea::before, {{WRAPPER}} .bdt-ep-creative-button--skoll::before' => 'background: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--dione::after' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--elon::before' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--reveal:hover' => 'background: {{VALUE}} !important;',
					'{{WRAPPER}} .bdt-ep-creative-button--glitch, {{WRAPPER}} .bdt-ep-creative-button--glitch::after' => 'background: linear-gradient(45deg, transparent 5%, {{VALUE}} 5%);',
					'{{WRAPPER}} .bdt-ep-creative-button--gooey:hover' => 'background: {{VALUE}} !important;',
				],
				'condition' => [
					'button_style!' => ['fenrir', 'hati', 'surtur', 'reklo']
				]
			]
		);

		$this->add_control(
			'secondary_creative_button_background_color',
			[
				'label'     => esc_html__( 'Secondary Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button.bdt-ep-creative-button--pandora' => 'background: {{VALUE}};'
				],
				'condition' => [
					'button_style' => ['pandora']
				]
			]
		);

		$this->add_control(
			'creative_button_helene_shadow_color',
			[
				'label'     => esc_html__( 'Shadow Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--helene::before' => 'background: {{VALUE}};',
					'{{WRAPPER}}.elementor-widget-bdt-creative-button .bdt-ep-creative-button--glitch, {{WRAPPER}}.elementor-widget-bdt-creative-button .bdt-ep-creative-button--glitch::after' => 'box-shadow: 6px 0 0 {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['helene', 'glitch']
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'     => 'creative_button_border',
				'selector' => '{{WRAPPER}} .bdt-ep-creative-button, {{WRAPPER}} .bdt-ep-creative-button--bestia .bdt-ep-creative-button__bg, {{WRAPPER}} .bdt-ep-creative-button--elon:before',
				'condition' => [
					'button_style!' => ['fenrir', 'janus', 'surtur', 'pandora', 'narvi', 'reklo', 'glitch']
				]
			]
		);

		$this->add_responsive_control(
			'creative_button_radius',
			[
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-ep-creative-button, {{WRAPPER}} .bdt-ep-creative-button--bestia .bdt-ep-creative-button__bg, {{WRAPPER}} .bdt-ep-creative-button--pandora span, {{WRAPPER}} .bdt-ep-creative-button--dione::before, {{WRAPPER}} .bdt-ep-creative-button--dione::after, {{WRAPPER}} .bdt-ep-creative-button--elon::before' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'button_style!' => ['fenrir', 'janus', 'surtur', 'narvi', 'reklo', 'glitch']
				]
			]
		);

		$this->add_responsive_control(
			'creative_button_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-ep-creative-button, {{WRAPPER}} .bdt-ep-creative-button--bestia .bdt-ep-creative-button__bg span, {{WRAPPER}} .bdt-ep-creative-button-marquee span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'button_style!' => ['fenrir', 'janus', 'surtur', 'pandora', 'rhea', 'reklo']
				]
			]
		);

		$this->add_responsive_control(
			'creative_button_pandora_padding',
			[
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [
					'{{WRAPPER}} .bdt-ep-creative-button--pandora span' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'button_style' => ['pandora']
				]
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'creative_button_shadow',
				'selector' => '{{WRAPPER}} .bdt-ep-creative-button, {{WRAPPER}} .bdt-ep-creative-button--bestia .bdt-ep-creative-button__bg',
				'condition' => [
					'button_style!' => ['fenrir', 'janus', 'surtur', 'reklo', 'elon', 'glitch']
				]
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name'     => 'creative_button_typography',
				'selector' => '{{WRAPPER}} .bdt-ep-creative-button, {{WRAPPER}} .bdt-ep-creative-button--glitch::after',
				'condition' => [
					'button_style!' => ['surtur']
				]
			]
		);

		$this->add_responsive_control(
			'creative_button_size',
			[
				'label' => __( 'Size', 'bdthemes-element-pack' ),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 20,
						'max' => 500,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--surtur .textcircle' => 'width: {{SIZE}}{{UNIT}};',
					'{{WRAPPER}} .bdt-ep-creative-button--elon:before' => 'width: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'button_style' => ['surtur', 'elon']
				]
			]
		);

		$this->end_controls_tab();

		$this->start_controls_tab(
			'tab_creative_button_hover',
			[
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'creative_button_hover_text_color',
			[
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button:hover, {{WRAPPER}} .bdt-ep-creative-button--dione:hover span' => 'color: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--glitch:hover::after' => 'color: {{VALUE}}; text-shadow: -2px -3px 0 {{VALUE}}, 2px 3px 0 {{VALUE}};',
				],
				'condition' => [
					'button_style!' => ['surtur']
				]
			]
		);

		$this->add_control(
			'creative_button_hover_stroke_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--surtur:hover svg *' => 'stroke: {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['surtur']
				]
			]
		);

		$this->add_control(
			'creative_button_hover_line_color',
			[
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--fenrir .progress__path' => 'stroke: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--janus:hover::after' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['fenrir', 'janus', 'pandora', 'narvi']
				]
			]
		);

		$this->add_control(
			'creative_button_hover_background_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button:hover, {{WRAPPER}} .bdt-ep-creative-button--anthe:hover::before, {{WRAPPER}} .bdt-ep-creative-button--bestia .bdt-ep-creative-button__bg::before, {{WRAPPER}} .bdt-ep-creative-button--bestia .bdt-ep-creative-button__bg::after, {{WRAPPER}} .bdt-ep-creative-button--calypso::before, {{WRAPPER}} .bdt-ep-creative-button--calypso::after, {{WRAPPER}} .bdt-ep-creative-button--dione:hover::before, {{WRAPPER}} .bdt-ep-creative-button--greip, {{WRAPPER}} .bdt-ep-creative-button--hyperion, {{WRAPPER}} .bdt-ep-creative-button--janus:hover::before, {{WRAPPER}} .bdt-ep-creative-button--mimas, {{WRAPPER}} .bdt-ep-creative-button--narvi:hover::before, {{WRAPPER}} .bdt-ep-creative-button--pan, {{WRAPPER}} .bdt-ep-creative-button--pandora:hover span, {{WRAPPER}} .bdt-ep-creative-button--rhea:hover::before, {{WRAPPER}} .bdt-ep-creative-button--skoll, {{WRAPPER}} .bdt-ep-creative-button--telesto::before, {{WRAPPER}} .bdt-ep-creative-button--telesto::after' => 'background: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--dione:hover::after' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--elon:hover::before' => 'background-color: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--reveal::after' => 'background: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--glitch:hover, {{WRAPPER}} .bdt-ep-creative-button--glitch:hover::after' => 'background: linear-gradient(45deg, transparent 5%, {{VALUE}} 5%);',
					'{{WRAPPER}} .bdt-ep-creative-button--gooey .c-button__blobs div' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'button_style!' => ['fenrir', 'hati', 'surtur', 'reklo']
				]
			]
		);

		$this->add_control(
			'creative_button_glitch_hover_shadow_color',
			[
				'label'     => esc_html__( 'Shadow Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}}.elementor-widget-bdt-creative-button .bdt-ep-creative-button--glitch:hover, {{WRAPPER}}.elementor-widget-bdt-creative-button .bdt-ep-creative-button--glitch:hover::after' => 'box-shadow: 6px 0 0 {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['glitch']
				]
			]
		);

		$this->add_control(
			'secondary_creative_button_background_hover',
			[
				'label'     => esc_html__( 'Secondary Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button.bdt-ep-creative-button--pandora:hover' => 'background: {{VALUE}};'
				],
				'condition' => [
					'button_style' => ['pandora']
				]
			]
		);

		$this->add_control(
			'button_hover_border_color',
			[
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button:hover, {{WRAPPER}} .bdt-ep-creative-button--bestia:hover .bdt-ep-creative-button__bg' => 'border-color: {{VALUE}};',
					'{{WRAPPER}} .bdt-ep-creative-button--elon:hover:before' => 'border-color: {{VALUE}};',
				],
				'condition' => [
					'creative_button_border_border!' => '',
					'button_style!' => ['fenrir', 'janus', 'surtur', 'narvi', 'reklo', 'glitch']
				]
			]
		);

		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[
				'name'     => 'creative_button_hover_shadow',
				'selector' => '{{WRAPPER}} .bdt-ep-creative-button:hover, {{WRAPPER}} .bdt-ep-creative-button--bestia:hover .bdt-ep-creative-button__bg',
				'condition' => [
					'button_style!' => ['fenrir', 'janus', 'surtur', 'reklo', 'elon', 'glitch']
				]
			]
		);

		$this->add_control(
			'hover_animation',
			[
				'label' => esc_html__( 'Hover Animation', 'bdthemes-element-pack' ),
				'type' => Controls_Manager::HOVER_ANIMATION,
			]
		);

		//icon color
		$this->add_control(
			'creative_button_hover_icon_heading',
			[
				'label'     => esc_html__( 'Icon Style', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::HEADING,
				'condition' => [
					'button_style' => ['reklo']
				],
				'separator' => 'before'
			]
		);
		$this->add_control(
			'creative_button_hover_icon_color',
			[
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--reklo:hover i' => 'color: {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['reklo']
				]
			]
		);
		$this->add_control(
			'creative_button_hover_icon_bg_color',
			[
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--reklo i' => 'background-color: {{VALUE}};',
				],
				'condition' => [
					'button_style' => ['reklo']
				]
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[
				'name'      => 'icon_border',
				'selector'  => '{{WRAPPER}} .bdt-ep-creative-button--reklo i',
				'condition' => [
					'button_style' => ['reklo']
				]
			]
		);

		$this->add_responsive_control(
			'icon_border_radius',
			[
				'label'      => esc_html__('Border Radius', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => ['px', '%'],
				'selectors'  => [
					'{{WRAPPER}} .bdt-ep-creative-button--reklo i' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
				'condition' => [
					'button_style' => ['reklo']
				]
			]
		);
		$this->add_responsive_control(
			'icon_size',
			[
				'label' => esc_html__('Size', 'bdthemes-element-pack'),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--reklo i' => 'height: {{SIZE}}{{UNIT}}; width: {{SIZE}}{{UNIT}}; line-height: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'button_style' => ['reklo']
				]
			]
		);
		$this->add_responsive_control(
			'icon_gap',
			[
				'label' => esc_html__('Space Between', 'bdthemes-element-pack'),
				'type'  => Controls_Manager::SLIDER,
				'range' => [
					'px' => [
						'min' => 0,
						'max' => 50,
					],
				],
				'selectors' => [
					'{{WRAPPER}} .bdt-ep-creative-button--reklo' => 'gap: {{SIZE}}{{UNIT}};',
				],
				'condition' => [
					'button_style' => ['reklo']
				]
			]
		);

		$this->end_controls_tab();

		$this->end_controls_tabs();

		$this->end_controls_section();

	}

	protected function render() {
		$settings = $this->get_settings_for_display();

		if ( ! empty( $settings['link']['url'] ) ) {
			$this->add_link_attributes( 'creative_button', $settings['link']);
		}

		if ( $settings['link']['nofollow'] ) {
			$this->add_render_attribute( 'creative_button', 'rel', 'nofollow' );
		}

		$this->add_render_attribute( 'creative_button', 'class', 'bdt-ep-creative-button' );		
		$this->add_render_attribute( 'creative_button', 'class', 'bdt-ep-creative-button--' . esc_attr($settings['button_style']) );

		if ( $settings['hover_animation'] ) {
			$this->add_render_attribute( 'creative_button', 'class', 'elementor-animation-' . esc_attr($settings['hover_animation']) );
		}

		if ( ! empty( $settings['button_css_id'] ) ) {
			$this->add_render_attribute( 'creative_button', 'id', esc_html($settings['button_css_id']) );
		}

		?>
		<?php if ( $settings['button_style'] == 'hyperion' or $settings['button_style'] == 'telesto' or $settings['button_style'] == 'narvi' or $settings['button_style'] == 'helene' or $settings['button_style'] == 'greip' or $settings['button_style'] == 'skoll' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>><span><span><?php echo esc_html($settings['text']); ?></span></span></a>
		<?php elseif ( $settings['button_style'] == 'atlas' or $settings['button_style'] == 'kari' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>>
				<span><?php echo esc_html($settings['text']); ?></span>
				<div class="bdt-ep-creative-button-marquee" aria-hidden="true">
					<div class="bdt-ep-creative-button-marquee__inner">
						<span><?php echo esc_html($settings['text']); ?></span>
						<span><?php echo esc_html($settings['text']); ?></span>
						<span><?php echo esc_html($settings['text']); ?></span>
						<span><?php echo esc_html($settings['text']); ?></span>
					</div>
				</div>
			</a>
		<?php elseif ( $settings['button_style'] == 'pallene' or $settings['button_style'] == 'glitch' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>><?php echo esc_html($settings['text']); ?></a>
		<?php elseif ( $settings['button_style'] == 'bestia' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>>
				<div class="bdt-ep-creative-button__bg"></div><span><?php echo esc_html($settings['text']); ?></span>
			</a>
		<?php elseif ( $settings['button_style'] == 'surtur' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>>
				<svg class="textcircle" viewBox="0 0 500 500">
					<title><?php echo esc_html($settings['text']); ?></title>
					<defs><path id="textcircle" d="M250,400 a150,150 0 0,1 0,-300a150,150 0 0,1 0,300Z"
					/></defs>
					<text><textPath xlink:href="#textcircle" aria-label="<?php echo esc_html($settings['text']); ?>" textLength="900"><?php echo esc_html($settings['text']); ?></textPath></text>
				</svg>
				<svg aria-hidden="true" class="eye" width="70" height="70" viewBox="0 0 70 70" xmlns="http://www.w3.org/2000/svg">
					<path class="eye__outer" d="M10.5 35.308c5.227-7.98 14.248-13.252 24.5-13.252s19.273 5.271 24.5 13.252c-5.227 7.98-14.248 13.253-24.5 13.253s-19.273-5.272-24.5-13.253z"/>
					<path class="eye__lashes-up" d="M35 8.802v8.836M49.537 11.383l-3.31 8.192M20.522 11.684l3.31 8.192" />
					<path class="eye__lashes-down" d="M35 61.818v-8.836 8.836zM49.537 59.237l-3.31-8.193 3.31 8.193zM20.522 58.936l3.31-8.193-3.31 8.193z" />
					<circle class="eye__iris" cx="35" cy="35.31" r="5.221" />
					<circle class="eye__inner" cx="35" cy="35.31" r="10.041" />
				</svg>
			</a>
		<?php elseif ( $settings['button_style'] == 'fenrir' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>>
				<svg aria-hidden="true" class="progress" width="70" height="70" viewbox="0 0 70 70">
					<path class="progress__circle" d="m35,2.5c17.955803,0 32.5,14.544199 32.5,32.5c0,17.955803 -14.544197,32.5 -32.5,32.5c-17.955803,0 -32.5,-14.544197 -32.5,-32.5c0,-17.955801 14.544197,-32.5 32.5,-32.5z" />
					<path class="progress__path" d="m35,2.5c17.955803,0 32.5,14.544199 32.5,32.5c0,17.955803 -14.544197,32.5 -32.5,32.5c-17.955803,0 -32.5,-14.544197 -32.5,-32.5c0,-17.955801 14.544197,-32.5 32.5,-32.5z" pathLength="1" />
				</svg>
				<span><?php echo esc_html($settings['text']); ?></span>
			</a>
		<?php elseif ( $settings['button_style'] == 'reklo' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>>
				<span><?php echo esc_html($settings['text']); ?></span>
				<i class="ep-icon-arrow-right-0"></i>
			</a>
		<?php elseif ( $settings['button_style'] == 'gooey' ) : ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>>
				<?php echo esc_html($settings['text']); ?>
				<div class="c-button__blobs">
					<div></div>
					<div></div>
					<div></div>
				</div>
			</a>
			<svg xmlns="http://www.w3.org/2000/svg" version="1.1" style="display: block; height: 0; width: 0;">
			<defs>
				<filter id="goo">
				<feGaussianBlur in="SourceGraphic" stdDeviation="10" result="blur"></feGaussianBlur>
				<feColorMatrix in="blur" mode="matrix" values="1 0 0 0 0  0 1 0 0 0  0 0 1 0 0  0 0 0 18 -7" result="goo"></feColorMatrix>
				<feBlend in="SourceGraphic" in2="goo"></feBlend>
				</filter>
			</defs>
			</svg>
		<?php else: ?>
			<a <?php $this->print_render_attribute_string( 'creative_button' ); ?>><span><?php echo esc_html($settings['text']); ?></span></a>
		<?php endif; ?>

		<?php
	}

}

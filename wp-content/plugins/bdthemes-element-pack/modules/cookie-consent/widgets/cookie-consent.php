<?php

namespace ElementPack\Modules\CookieConsent\Widgets;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;
use Elementor\Group_Control_Box_Shadow;
use Elementor\Group_Control_Border;
use ElementPack\Base\Module_Base;
use ElementPack\Element_Pack_Loader;
use ElementPack\Utils;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
// Exit if accessed directly

class Cookie_Consent extends Module_Base {
	public function get_name() {
		return 'bdt-cookie-consent';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'Cookie Consent', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-cookie-consent';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_keywords() {
		return [ 'cookie', 'consent' ];
	}

	public function get_style_depends() {
		if ( $this->ep_is_edit_mode() ) {
			return [ 'ep-styles' ];
		} else {
			return [ 'ep-cookie-consent' ];
		}
	}

	public function get_script_depends() {
		if ( $this->ep_is_edit_mode() ) {
			return [ 'cookieconsent', 'ep-scripts' ];
		} else {
			return [ 'cookieconsent', 'ep-cookie-consent' ];
		}
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/BR4t5ngDzqM';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return true;
	}

	protected function register_controls() {

		$this->start_controls_section(
			'section_content_layout',
			[ 
				'label' => esc_html__( 'Layout', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'message',
			[ 
				'label'   => esc_html__( 'Message', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXTAREA,
				'default' => 'This website uses cookies to ensure you get the best experience on our website. ',
				'dynamic' => [ 'active' => true ],
			]
		);

		$this->add_control(
			'learn_more_text',
			[ 
				'label'       => esc_html__( 'Learn More Text', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::TEXT,
				'placeholder' => esc_html__( 'Learn more', 'bdthemes-element-pack' ),
				'dynamic'     => [ 'active' => true ],
				'default'     => 'Learn more',
			]
		);

		$this->add_control(
			'learn_more_link',
			[ 
				'label'         => esc_html__( 'Learn More Link', 'bdthemes-element-pack' ),
				'type'          => Controls_Manager::URL,
				'show_external' => false,
				'placeholder'   => esc_html__( 'https://your-link.com', 'bdthemes-element-pack' ),
				'default'       => [ 
					'url' => 'http://cookiesandyou.com/',
				],
				'dynamic'       => [ 'active' => true ],
			]
		);

		$this->add_control(
			'button_text',
			[ 
				'label'   => esc_html__( 'Button Text', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::TEXT,
				'dynamic' => [ 'active' => true ],
				'default' => 'Got it!',
			]
		);

		$this->add_control(
			'position',
			[ 
				'label'     => esc_html__( 'Position', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SELECT,
				'default'   => 'bottom',
				'options'   => [ 
					'bottom'       => esc_html__( 'Bottom', 'bdthemes-element-pack' ),
					'bottom-left'  => esc_html__( 'Bottom Left', 'bdthemes-element-pack' ),
					'bottom-right' => esc_html__( 'Bottom Right', 'bdthemes-element-pack' ),
					'top'          => esc_html__( 'Top', 'bdthemes-element-pack' ),
				],
				'separator' => 'before',
			]
		);

		$this->add_control(
			'pushdown',
			[ 
				'label'     => esc_html__( 'Push Down', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [ 
					'position' => 'top',
				],
			]
		);

		$this->add_control(
			'expiry_days',
			[ 
				'label'       => esc_html__( 'Expiry Days', 'bdthemes-element-pack' ),
				'description' => 'Specify -1 for no expiry',
				'type'        => Controls_Manager::SLIDER,
				'default'     => [ 
					'size' => 7,
				],
				'range'       => [ 
					'px' => [ 
						'min' => -1,
						'max' => 365,
					],
				],
				'dynamic'     => [ 'active' => true ],
			]
		);

		$this->add_control(
			'google_tag_assistant',
			[ 
				'label'       => esc_html__( 'Google Consent Mode', 'bdthemes-element-pack' ) . BDTEP_NC,
				'type'        => Controls_Manager::SWITCHER,
				'separator'   => 'before',
				'description' => esc_html__( 'Google Consent Mode for cookie consent. Must make sure, you have installed GTAG on your website. If you disabled any features then they will be sent as denied', 'bdthemes-element-pack' ) . ' <a href="https://developers.google.com/tag-platform/security/guides/consent?sjid=14345995958166516665-AP&consentmode=advanced" target="_blank">' . esc_html__( 'Learn More', 'bdthemes-element-pack' ) . '</a>',
			]
		);

		$this->add_control(
			'google_tag_ad_user_data',
			[ 
				'label'     => esc_html__( 'Ad User Data', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [ 
					'google_tag_assistant' => 'yes',
				],
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'google_tag_ad_storage',
			[ 
				'label'     => esc_html__( 'Ad Storage', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [ 
					'google_tag_assistant' => 'yes',
				],
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'google_tag_ad_personalization',
			[ 
				'label'     => esc_html__( 'Ad Personalization', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [ 
					'google_tag_assistant' => 'yes',
				],
				'default'   => 'yes',
			]
		);

		$this->add_control(
			'google_tag_analytics_storage',
			[ 
				'label'     => esc_html__( 'Analytics Storage', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SWITCHER,
				'condition' => [ 
					'google_tag_assistant' => 'yes',
				],
				'default'   => 'yes',
			]
		);

		$this->end_controls_section();

		/**
		 * Additional Options
		 */
		$this->start_controls_section(
			'section_additional_options',
			[ 
				'label' => esc_html__( 'Additional Options', 'bdthemes-element-pack' ),
			]
		);
		$this->add_control(
			'close_button',
			[ 
				'label'                => esc_html__( 'Close Button', 'bdthemes-element-pack' ),
				'type'                 => Controls_Manager::SELECT,
				'default'              => 'default',
				'options'              => [ 
					'default'      => esc_html__( 'Default', 'bdthemes-element-pack' ),
					'none'         => esc_html__( 'None', 'bdthemes-element-pack' ),
					'top-left'     => esc_html__( 'Top Left', 'bdthemes-element-pack' ),
					'top-right'    => esc_html__( 'Top Right', 'bdthemes-element-pack' ),
					'bottom-left'  => esc_html__( 'Bottom Left', 'bdthemes-element-pack' ),
					'bottom-right' => esc_html__( 'Bottom Right', 'bdthemes-element-pack' ),
				],
				'selectors_dictionary' => [ 
					'default'      => '',
					'none'         => 'display: none;',
					'top-left'     => 'position: absolute; top: 0; left: 0;',
					'top-right'    => 'position: absolute; top: 0; right: 0;',
					'bottom-left'  => 'position: absolute; bottom: 0; left: 0;',
					'bottom-right' => 'position: absolute; bottom: 0; right: 0;',
				],
				'selectors'            => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn' => '{{VALUE}}',
				],
			]
		);
		$this->end_controls_section();

		/**
		 * Style Tab
		 */
		$this->start_controls_section(
			'section_style',
			[ 
				'label' => esc_html__( 'Dialog', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);

		$this->add_control(
			'text_color',
			[ 
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#ffffff',
				'selectors' => [ 
					'body .cc-window' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'learn_more_color',
			[ 
				'label'     => esc_html__( 'Link Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4593E3',
				'selectors' => [ 
					'body .cc-window .cc-message a' => 'color: {{VALUE}} !important;',
				],
			]
		);
		$this->add_control(
			'learn_more_hover_color',
			[ 
				'label'     => esc_html__( 'Link Hover Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#4593E3',
				'selectors' => [ 
					'body .cc-window .cc-message a:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'background',
			[ 
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'default'   => '#3937a3',
				'selectors' => [ 
					'body .cc-window' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[ 
				'name'      => 'cc_border',
				'selector'  => 'body .cc-window',
				'separator' => 'before',
			]
		);

		$this->add_responsive_control(
			'cc_border_radius',
			[ 
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ 
					'body .cc-window' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'cc_padding',
			[ 
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);
		$this->add_responsive_control(
			'cc_margin',
			[ 
				'label'      => esc_html__( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
				],
			]
		);
		$this->add_responsive_control(
			'cc_spacing',
			[ 
				'label'      => esc_html__( 'Spacing', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window .cc-message' => 'margin-bottom: {{SIZE}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'     => 'subtitle_typography',
				'selector' => 'body .cc-window .cc-message',
			]
		);
		$this->add_group_control(
			Group_Control_Box_Shadow::get_type(),
			[ 
				'name'     => 'cc_box_shadow',
				'selector' => 'body .cc-window',
			]
		);
		$this->add_responsive_control(
			'cc_max_width',
			[ 
				'label'      => esc_html__( 'Max Width', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'range'      => [ 
					'px' => [ 
						'min' => 100,
						'max' => 2000,
					],
					'%'  => [ 
						'min' => 10,
						'max' => 100,
					],
					'em' => [ 
						'min' => 1,
						'max' => 100,
					],
				],
				'selectors'  => [ 
					'body .cc-window' => 'max-width: {{SIZE}}{{UNIT}} !important;',
				],
				'separator'  => 'before',
			]
		);

		$this->add_responsive_control(
			'content_alignment',
			[ 
				'label'     => esc_html__( 'Text Align', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [ 
					'left'    => [ 
						'title' => esc_html__( 'Left', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-left',
					],
					'center'  => [ 
						'title' => esc_html__( 'Center', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-center',
					],
					'right'   => [ 
						'title' => esc_html__( 'Right', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-right',
					],
					'justify' => [ 
						'title' => esc_html__( 'Justify', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-text-align-justify',
					],
				],
				'selectors' => [ 
					'body .cc-window' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style_dismiss_button',
			[ 
				'label' => esc_html__( 'Button', 'bdthemes-element-pack' ),
				'tab'   => Controls_Manager::TAB_STYLE,
			]
		);
		$this->add_responsive_control(
			'button_align',
			[ 
				'label'     => esc_html__( 'Alignment', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::CHOOSE,
				'options'   => [ 
					'flex-start' => [ 
						'title' => esc_html__( 'Start', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-align-start-h',
					],
					'center'     => [ 
						'title' => esc_html__( 'Center', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-align-center-h',
					],
					'flex-end'   => [ 
						'title' => esc_html__( 'End', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-align-end-h',
					],
					'stretch'    => [ 
						'title' => esc_html__( 'Stretch', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-align-stretch-h',
					],
				],
				'selectors' => [ 
					'body .cc-window' => 'align-items: {{VALUE}};',
				],
			]
		);
		$this->start_controls_tabs( 'tabs_dismiss_button_style' );
		$this->start_controls_tab(
			'tab_dismiss_button_normal',
			[ 
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'dismiss_button_color',
			[ 
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				// 'default'   => '#ffffff',
				'selectors' => [ 
					'body .cc-window a.cc-btn.cc-dismiss' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'dismiss_button_background',
			[ 
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				// 'default'   => '#41aab9',
				'selectors' => [ 
					'body .cc-window a.cc-btn.cc-dismiss' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[ 
				'name'      => 'dismiss_button_border',
				'selector'  => 'body .cc-window .cc-compliance a.cc-btn.cc-dismiss',
				'separator' => 'before',
			]
		);
		$this->add_responsive_control(
			'dismiss_button_radius',
			[ 
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ 
					'body .cc-window a.cc-btn.cc-dismiss' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'dismiss_button_padding',
			[ 
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window a.cc-btn.cc-dismiss' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'dismiss_button_margin',
			[ 
				'label'      => esc_html__( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window a.cc-btn.cc-dismiss' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);
		$this->add_responsive_control(
			'dismiss_button_spacing',
			[ 
				'label'      => esc_html__( 'Spacing', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window .cc-compliance' => 'gap: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[ 
				'name'     => 'dismiss_button_typography',
				'label'    => esc_html__( 'Typography', 'bdthemes-element-pack' ),
				//'scheme'    => Schemes\Typography::TYPOGRAPHY_4,
				'selector' => 'body .cc-window a.cc-btn.cc-dismiss',
			]
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_dismiss_button_hover',
			[ 
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'dismiss_button_hover_color',
			[ 
				'label'     => esc_html__( 'Text Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'body .cc-window a.cc-btn.cc-dismiss:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'dismiss_button_hover_background',
			[ 
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'body .cc-window a.cc-btn.cc-dismiss:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'dismiss_button_hover_border_color',
			[ 
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 
					'dismiss_button_border_border!' => '',
				],
				'selectors' => [ 
					'body .cc-window a.cc-btn.cc-dismiss:hover' => 'border-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();


		$this->start_controls_section(
			'section_style_dismiss_close_button',
			[ 
				'label'     => esc_html__( 'Close Button', 'bdthemes-element-pack' ),
				'tab'       => Controls_Manager::TAB_STYLE,
				'condition' => [ 
					'close_button!' => 'none',
				],
			]
		);

		$this->add_responsive_control(
			'close_button_alignment',
			[ 
				'label'                => esc_html__( 'Alignment', 'bdthemes-element-pack' ),
				'type'                 => Controls_Manager::CHOOSE,
				'options'              => [ 
					'row-reverse' => [ 
						'title' => esc_html__( 'Left', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-h-align-left',
					],
					'row'         => [ 
						'title' => esc_html__( 'Right', 'bdthemes-element-pack' ),
						'icon'  => 'eicon-h-align-right',
					],
				],
				'selectors_dictionary' => [ 
					'row-reverse' => 'flex-direction: row-reverse; justify-content: flex-end;',
					'row'         => 'flex-direction: row; justify-content: flex-start;',
				],
				'selectors'            => [ 
					'body .cc-window .cc-compliance' => '{{VALUE}}',
				],
				'condition'            => [ 
					'close_button' => 'default',
				],
			]
		);

		$this->start_controls_tabs( 'tabs_dismiss_close_button_style' );
		$this->start_controls_tab(
			'tab_dismiss_close_button_normal',
			[ 
				'label' => esc_html__( 'Normal', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'dismiss_close_button_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'dismiss_close_button_background',
			[ 
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_group_control(
			Group_Control_Border::get_type(),
			[ 
				'name'      => 'dismiss_close_button_border',
				'selector'  => 'body .cc-window .cc-compliance .bdt-cc-close-btn',
				'separator' => 'before',
			]
		);
		$this->add_responsive_control(
			'dismiss_close_button_radius',
			[ 
				'label'      => esc_html__( 'Border Radius', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', '%' ],
				'selectors'  => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn' => 'border-radius: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'dismiss_close_button_padding',
			[ 
				'label'      => esc_html__( 'Padding', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn' => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'dismiss_close_button_margin',
			[ 
				'label'      => esc_html__( 'Margin', 'bdthemes-element-pack' ),
				'type'       => Controls_Manager::DIMENSIONS,
				'size_units' => [ 'px', 'em', '%' ],
				'selectors'  => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn' => 'margin: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}} !important;',
				],
			]
		);

		$this->add_responsive_control(
			'dismiss_close_button_size',
			[ 
				'label'     => esc_html__( 'Icon Size', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::SLIDER,
				'range'     => [ 
					'px' => [ 
						'min' => 10,
						'max' => 100,
					],
				],
				'selectors' => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn' => 'font-size: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->end_controls_tab();
		$this->start_controls_tab(
			'tab_dismiss_close_button_hover',
			[ 
				'label' => esc_html__( 'Hover', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'dismiss_close_button_hover_color',
			[ 
				'label'     => esc_html__( 'Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn:hover' => 'color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'dismiss_close_button_hover_background',
			[ 
				'label'     => esc_html__( 'Background Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'selectors' => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn:hover' => 'background-color: {{VALUE}} !important;',
				],
			]
		);

		$this->add_control(
			'dismiss_close_button_hover_border_color',
			[ 
				'label'     => esc_html__( 'Border Color', 'bdthemes-element-pack' ),
				'type'      => Controls_Manager::COLOR,
				'condition' => [ 
					'dismiss_close_button_border_border!' => '',
				],
				'selectors' => [ 
					'body .cc-window .cc-compliance .bdt-cc-close-btn:hover' => 'border-color: {{VALUE}} !important;',
				],
			]
		);

		$this->end_controls_tab();
		$this->end_controls_tabs();
		$this->end_controls_section();

	}

	protected function render() {

		$settings = $this->get_settings_for_display();

		$cc_position = $settings['position'];

		if ( $cc_position == 'bottom-left' ) {
			$cc_position = 'cc-bottom cc-left cc-floating';
		} else if ( $cc_position == 'bottom-right' ) {
			$cc_position = 'cc-bottom cc-right cc-floating';
		} else if ( $cc_position == 'top' ) {
			$cc_position = 'cc-top cc-banner';
		} else if ( $cc_position == 'bottom' ) {
			$cc_position = 'cc-bottom cc-banner';
		}

		$this->add_render_attribute( 'cookie-consent', 'class', [ 'bdt-cookie-consent', 'bdt-hidden' ] );

		$this->add_link_attributes( 'custom-attr', $settings['learn_more_link'] );
		$this->add_render_attribute(
			[ 
				'cookie-consent' => [ 
					'data-settings' => [ 
						wp_json_encode( [ 
							'position' => $settings['position'],
							'static'   => ( 'top' == $settings['position'] and $settings['pushdown'] ) ? true : false,
							'content'  => [ 
								'message'     => esc_html( $settings['message'] ),
								'dismiss'     => esc_html( $settings['button_text'] ),
								'link'        => esc_html( $settings['learn_more_text'] ),
								'href'        => esc_url( $settings['learn_more_link']['url'] ),
								'custom_attr' => esc_attr( $this->get_render_attribute_string( 'custom-attr' ) ),
							],
							'cookie'   => [ 
								'name'       => 'element_pack_cookie_widget',
								'domain'     => Utils::get_site_domain(),
								'expiryDays' => $settings['expiry_days']['size'],
							],
						], JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT ), // Extra security
					],
				],
			]
		);

		if ( 'yes' == $settings['google_tag_assistant'] ) {
			$this->add_render_attribute(
				[ 
					'cookie-consent' => [ 
						'data-gtag' => [ 
							wp_json_encode( [ 
								'gtag_enabled'       => true,
								'ad_user_data'       => ( 'yes' == $settings['google_tag_ad_user_data'] ) ? 'granted' : 'denied',
								'ad_storage'         => ( 'yes' == $settings['google_tag_ad_storage'] ) ? 'granted' : 'denied',
								'ad_personalization' => ( 'yes' == $settings['google_tag_ad_personalization'] ) ? 'granted' : 'denied',
								'analytics_storage'  => ( 'yes' == $settings['google_tag_analytics_storage'] ) ? 'granted' : 'denied',
							] ),
						],
					],
				]
			);
		}

		if ( Element_Pack_Loader::elementor()->editor->is_edit_mode() ) : ?>

			<div role="dialog" aria-live="polite" aria-label="cookieconsent" aria-describedby="cookieconsent:desc"
				class="cc-window <?php echo esc_attr( $cc_position ); ?> cc-type-info cc-theme-block cc-color-override--2000495483">

				<!--googleoff: all-->
				<span id="cookieconsent:desc" class="cc-message">
					<?php echo wp_kses_post( $settings['message'] ); ?><a aria-label="learn more about cookies" role="button"
						tabindex="0" class="cc-link" href="<?php echo esc_url( $settings['learn_more_link']['url'] ); ?>"
						rel="noopener noreferrer nofollow" target="_blank" test>
						<?php echo esc_html( $settings['learn_more_text'] ); ?>
					</a>
				</span>
				<div class="cc-compliance">
					<a aria-label="dismiss cookie message" role="button" tabindex="0" class="cc-btn cc-dismiss">
						<?php echo esc_html( $settings['button_text'] ); ?>
					</a>

					<button class="bdt-cc-close-btn">
						<svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="none" viewBox="0 0 24 24">
							<path stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
								d="M6 18 17.94 6M18 18 6.06 6" />
						</svg>
					</button>
				</div>
				<!--googleon: all-->

			</div>

		<?php else : ?>

			<div <?php $this->print_render_attribute_string( 'cookie-consent' ); ?>></div>

		<?php endif;
	}
}

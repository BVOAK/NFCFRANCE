<?php
namespace UiCore\ThemeBuilder\Widgets;

use Uicore\Elementor\TB_Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Typography;

defined('ABSPATH') || exit();

/**
 * Breadcrumbs Widget
 *
 * @author Lucas Marini <lucas@uicore.co>
 * @since 6.0.0
 */
class Breadcrumbs extends TB_Widget_Base {

    public function get_name() {
		return 'uicore-woo-breadcrumbs';
	}
	public function get_title() {
		return esc_html__( 'Breadcrumbs', 'uicore-framework' );
	}
	public function get_icon() {
		return 'eicon-product-breadcrumbs ui-e-widget';
	}
	public function get_categories() {
		return ['uicore-woo', 'uicore-theme-builder'];
	}
	public function get_keywords() {
		return [ 'woocommerce', 'shop', 'store', 'product', 'breadcrumbs', 'internal links'];
	}
    public function get_styles() {
        return [
            'single-product' => [
                'deps' => [
                    'woocommerce-general'
                ]
            ],
        ];
	}
    public function get_scripts(){
        return [];
    }

	protected function register_controls() {
        if( $this->no_woo_fallback(true) ){
            return;
        }

		$this->start_controls_section(
			'section_breadcrumbs',
			[
				'label' => esc_html__( 'Style', 'uicore-elements' ),
			]
		);

            $this->add_control(
                'text_color',
                [
                    'label' => esc_html__( 'Text Color', 'uicore-elements' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-breadcrumb' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_control(
                'link_color',
                [
                    'label' => esc_html__( 'Link Color', 'uicore-elements' ),
                    'type' => Controls_Manager::COLOR,
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-breadcrumb > a' => 'color: {{VALUE}}',
                    ],
                ]
            );

            $this->add_group_control(
                Group_Control_Typography::get_type(),
                [
                    'name' => 'text_typography',
                    'selector' => '{{WRAPPER}} .woocommerce-breadcrumb',
                ]
            );

            $this->add_responsive_control(
                'alignment',
                [
                    'label' => esc_html__( 'Alignment', 'uicore-elements' ),
                    'type' => Controls_Manager::CHOOSE,
                    'options' => [
                        'left' => [
                            'title' => esc_html__( 'Left', 'uicore-elements' ),
                            'icon' => 'eicon-text-align-left',
                        ],
                        'center' => [
                            'title' => esc_html__( 'Center', 'uicore-elements' ),
                            'icon' => 'eicon-text-align-center',
                        ],
                        'right' => [
                            'title' => esc_html__( 'Right', 'uicore-elements' ),
                            'icon' => 'eicon-text-align-right',
                        ],
                    ],
                    'selectors' => [
                        '{{WRAPPER}} .woocommerce-breadcrumb' => 'text-align: {{VALUE}}',
                    ],
                ]
            );

		$this->end_controls_section();
	}

	protected function render() {
        if( $this->no_woo_fallback() ){
            return;
        }

        if($this->is_edit_mode()){
            $this->render_woo_wrapper();
        }

            \woocommerce_breadcrumb();

        if($this->is_edit_mode()){
            $this->render_woo_wrapper(true);
        }
	}

	protected function content_template() {}
}
\Elementor\Plugin::instance()->widgets_manager->register(new Breadcrumbs());

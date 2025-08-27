<?php
namespace UiCore\Elementor;

use Elementor\Controls_Manager;
use Elementor\Controls_Stack;

defined('ABSPATH') || exit();

/**
 *  Elementor extra features
 */
class Extender
{
    public function __construct()
    {
		//Extended Column
		add_action( 'elementor/element/column/layout/before_section_end', [$this, 'asimetric_column'], 20, 2);


		// add_action( 'elementor/element/container/section_effects/after_section_end', [$this, 'container_onscroll_effect'], 2, 2);
		// add_action('elementor/frontend/container/before_render', [$this, 'should_script_enqueue']);
		// add_action('elementor/preview/enqueue_scripts', [$this, 'enqueue_scripts']); 
    }



    function asimetric_column( Controls_Stack $element, $section_id )
    {
        $element->add_control(
			'shape_animation',
			[
				'label' => UICORE_BADGE . __( 'Align to Container', 'uicore-framework' ),
				'description' => __( 'Align the column to website container. Only works on top-level, full-width sections.', 'uicore-framework' ),
				'type' => Controls_Manager::SELECT,
				'options' => [
					'' => esc_html__( 'None', 'uicore-framework' ),
					'left' => esc_html__( 'Left', 'uicore-framework' ),
					'right' => esc_html__( 'Right', 'uicore-framework' ),
				],
                'default' => '',
                'separator' => 'before',
                'return_value' =>'',
                'prefix_class' => 'ui-col-align-',
			]
		);
    }


	// public function enqueue_scripts($type) {
	// 	$list = [
	// 		'onscroll-effects'=>[
	// 			'script'	=> true,
	// 			'style'		=> false,
	// 			'deps'		=> ['uicore-manifest'], // 'uicore-vendor'
	// 		]
	// 	];
	// 	if($type){
	// 		$list = [$type => $list[$type]];
	// 	}
	// 	foreach ($list as $type => $data) {
	// 		if($data['script']){
	// 			$deps = isset($data['deps']) ? $data['deps'] : [];
	// 			\UiCore\Helper::register_widget_script($type, $deps);
	// 			wp_enqueue_script('ui-e-'.$type);
	// 		}
	// 		if($data['style']){
	// 			\UiCore\Helper::register_widget_style($type);
	// 			wp_enqueue_style('ui-e-'.$type);
	// 		}
	// 	}
		
	// }
	// public function should_script_enqueue($widget) {
	// 	if ('' != $widget->get_settings_for_display('ui_onscroll_effect')) {

	// 		// $this->enqueue_scripts('onscroll'); 
	// 		//added this to our global file since the css is only 3 lines; 
	// 		//TODO: On migrate move to a better aproach

	// 		$widget->add_render_attribute('_wrapper', 'class', 'ui-onscroll-effect');
	// 		if($widget->get_settings_for_display('ui_onscroll_effect') != 'simple-sticky'){
	// 			$this->enqueue_scripts('onscroll-effects');
	// 		}
	// 	}
	// }
	
}

new Extender;

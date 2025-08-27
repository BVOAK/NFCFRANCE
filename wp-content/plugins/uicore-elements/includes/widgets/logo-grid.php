<?php

namespace UiCoreElements;

use Elementor\Controls_Manager;
use UiCoreElements\UiCoreWidget;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Grid_Trait;
use UiCoreElements\Utils\Logo_Trait;

defined('ABSPATH') || exit();

class Logo_Grid extends UiCoreWidget
{

    use Animation_Trait;
    use Grid_Trait;
    use Logo_Trait;

    public function get_name()
    {
        return 'uicore-logo-grid';
    }
    public function get_title()
    {
        return esc_html__('Logo Grid', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-logo ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['logo', 'grid', 'client', 'brand', 'showcase'];
    }
    public function get_styles()
    {
        $styles = [
            'logo-grid',
            'grid',
            'animation', // hover animations
            'entrance', // entrance basic style
        ];
        if (!class_exists('\UiCore\Core') && !class_exists('\UiCoreAnimate\Base')) {
            $styles['e-animations'] = [ // entrance animations
                'external' => true,
            ];
        }
        return $styles;
    }
    public function get_scripts()
    {
        return [
            'logo-grid',
            'entrance' => [
                'condition' => [
                    'animate_items' => 'ui-e-grid-animate'
                ],
            ]
        ];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }
    protected function register_controls()
    {

        $this->TRAIT_register_logo_repeater_controls('Logos'); // Repeater Controls

        $this->start_controls_section(
            'section_grid_settings',
            [
                'label' => __('Grid Settings', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->add_control(
            'layout',
            [
                'label'          => __('Grid Layout', 'uicore-elements'),
                'type'           => Controls_Manager::SELECT,
                'options'        => [
                    'inner-border' => __('Inner Border', 'uicore-elements'),
                    'outer-border' => __('Outer Border', 'uicore-elements'),
                    'divider'      => __('Inner Divider', 'uicore-elements'),
                ],
                'default'        => 'inner-border',
                'frontend_available' => true,
                'style_transfer' => true, // enables the param to be copied/pasted even without selector => []
                'render_type'    => 'template',
            ]
        );

        $this->TRAIT_register_grid_layout_controls();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_logo_settings',
            [
                'label' => __('Logo Settings', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->TRAIT_register_logo_adittional_controls();

        $this->end_controls_section();

        $this->start_controls_section(
            'section_logo_grid',
            [
                'label' => __('Logo Grid', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_logos_grid_style_controls();

        $this->end_controls_section();

        $this->start_controls_section(
            'animation_style_grid',
            [
                'label' => __('Animations', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_entrance_animations_controls();
        $this->TRAIT_register_hover_animation_control(
            'Item Hover Animation',
            [],
            ['underline']
        );
        $this->TRAIT_register_hover_animation_control(
            'Logo Hover Animation',
            [],
            ['underline']
        );

        $this->end_controls_section();

        // Updates and removes grid component controls
        $this->update_control('columns', [
            'default' => 4,
            'desktop_default' => 4,
            'tablet_default'  => 2,
            'mobile_default'  => 2,
            'render_type' => 'template',
            'frontend_available' => true,
            'selectors' => [
                '{{WRAPPER}} .ui-e-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr))',
            ],
        ]);
        $this->update_control('gap', [
            'condition' => [
                'layout' => 'inner-border'
            ],
        ]);
        $this->remove_control('masonry');
    }
    protected function render()
    {

        if (empty($this->get_settings_for_display('logo_list'))) {
            return;
        }

?>
        <div class='ui-e-grid'>
            <?php $this->TRAIT_render_logo_item(); ?>
        </div>
<?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new Logo_Grid());

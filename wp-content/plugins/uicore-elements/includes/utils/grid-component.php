<?php

namespace UiCoreElements\Utils;

use Elementor\Controls_Manager;
use Elementor\Group_Control_Background;
use Elementor\Group_Control_Border;
use Elementor\Group_Control_Box_Shadow;

defined('ABSPATH') || exit();

trait Grid_Trait
{

    /**
     * Registers the grid layout controls.
     *
     * @param array $conditions Conditions for the grid layout controls. Eg: ['{control-slug}' => '{control-value}']
     */
    function TRAIT_register_grid_layout_controls($conditions = [])
    {

        $this->add_responsive_control(
            'columns',
            [
                'label'           => __('Columns', 'uicore-elements'),
                'type'            => Controls_Manager::SELECT,
                'desktop_default' => 3,
                'tablet_default'  => 2,
                'mobile_default'  => 1,
                'options'         => [
                    1 => '1',
                    2 => '2',
                    3 => '3',
                    4 => '4',
                    5 => '5',
                    6 => '6',
                    7 => '7',
                    8 => '8',
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr)); --ui-e-column-count: {{VALUE}};', // the var is used by masonry
                    '{{WRAPPER}} .ui-e-adv-grid' => 'grid-template-columns: repeat({{VALUE}}, minmax(0, 1fr))', // Old APG Suport
                ],
                'condition' => $conditions
            ]
        );
        $this->add_responsive_control(
            'gap',
            [
                'label'     => esc_html__('Items Gap', 'uicore-elements'),
                'type'      => Controls_Manager::SLIDER,
                'size_units' => ['px'],
                'range' => [
                    'px' => [
                        'min' => 0,
                        'max' => 200,
                    ],
                ],
                'default' => [
                    'unit' => 'px',
                    'size' => 20,
                ],
                'selectors' => [
                    '{{WRAPPER}} .ui-e-grid' => 'grid-gap: {{SIZE}}{{UNIT}};',
                    '{{WRAPPER}} .ui-e-adv-grid' => 'grid-gap: {{SIZE}}{{UNIT}};', // Old APG Suport
                ],
                'condition' => $conditions
            ]
        );
        $this->add_control(
            'masonry',
            [
                'label'   => __('Masonry', 'uicore-elements'),
                'type'    => Controls_Manager::SWITCHER,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-grid' => 'column-count: var(--ui-e-column-count); column-gap: {{gap.SIZE}}{{gap.UNIT}};',
                    '{{WRAPPER}} .ui-e-wrp' => 'margin-bottom: {{gap.SIZE}}{{gap.UNIT}};'
                ],
                'render_type' => 'template',
                'condition' => $conditions
            ]
        );
    }
}

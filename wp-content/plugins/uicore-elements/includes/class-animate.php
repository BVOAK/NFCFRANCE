<?php

namespace UiCoreElements;

use Elementor\Controls_Stack;
use Elementor\Controls_Manager;

defined('ABSPATH') || exit();

/**
 * Replace UiCore Animate Plugin
 */
class Animate
{

    private static $badge = '<span title="Powerd by UiCore Animate" style="font-size: 11px; text-transform: uppercase; background: #5dbad8; color: black; padding: 2px 5px; border-radius: 3px; margin-right: 7px;">Animate</span>';

    public function __construct()
    {
        add_action('elementor/element/heading/section_title_style/after_section_end', [$this, 'split_animation'], 55);
        add_action('elementor/element/highlighted-text/section_title_style/after_section_end', [$this, 'split_animation'], 55);
        add_action('elementor/element/text-editor/section_drop_cap/after_section_end', [$this, 'split_animation'], 55);
    }

    static function split_animation(Controls_Stack $widget)
    {
        $widget->start_controls_section(
            'section_ui_split_animation',
            [
                'label' => self::$badge . esc_html__('Split Text Animation', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );
        $widget->add_control(
            'important_note',
            [
                'label' => esc_html__('UiCore Animate required', 'uicore-elements'),
                'type' => \Elementor\Controls_Manager::RAW_HTML,
                'raw' => '<p style="line-height:1.3;margin-top:1em;">'
                    . sprintf(
                        /* translators: %s: link to Uicore Animate */
                        __('Download and instal %s for more advanced animations control', 'uicore-elements'),
                        '<a href="https://wordpress.org/plugins/uicore-animate/advanced/" target="_blank"> Uicore Animate</a>'
                    )
                    . '</p>',
            ]
        );
        $widget->end_controls_section();
    }
}
new Animate();

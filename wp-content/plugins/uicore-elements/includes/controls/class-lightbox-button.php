<?php

namespace UiCoreElements\Controls;

use Elementor\Controls_Manager;
use Elementor\Frontend;
use Elementor\Embed;
use Elementor\Plugin;

defined('ABSPATH') || exit();

/**
 * Update Elementor Button Control by injecting a new lightbox option.
 * Functions were extracted and updated from the 'lightbox' dinamic tag module
 *
 * @since 1.0.15
 */

class Lightbox_Button
{

    public function __construct()
    {
        add_action('elementor/element/button/section_button/before_section_end', [$this, 'update_controls'], 10, 2);
        add_action('elementor/widget/render_content', [$this, 'render_widget'], 10, 2); // TODO: try targeting button specifically
    }

    public function update_controls($element)
    {

        $element->update_control(
            'link',
            [
                'condition' => [
                    'button_lightbox!' => 'yes',
                ],
            ]
        );

        $element->start_injection([
            'of' => 'link',
            'at' => 'after',
        ]);
        $element->add_control(
            'button_lightbox',
            [
                'label' => UICORE_ELEMENTS_BADGE . esc_html__('Lightbox', 'uicore-elements'),
                'type' => Controls_Manager::SWITCHER,
            ],
        );
        $element->add_control(
            'content_type',
            [
                'label' => esc_html__('Type', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'video' => [
                        'title' => esc_html__('Video', 'uicore-elements'),
                        'icon' => 'eicon-video-camera',
                    ],
                    'image' => [
                        'title' => esc_html__('Image', 'uicore-elements'),
                        'icon' => 'eicon-image-bold',
                    ],
                ],
                'condition' => [
                    'button_lightbox' => 'yes',
                ],
            ]
        );
        $element->add_control(
            'image',
            [
                'label' => esc_html__('Image', 'uicore-elements'),
                'type' => Controls_Manager::MEDIA,
                'condition' => [
                    'content_type' => 'image',
                    'button_lightbox' => 'yes',
                ],
            ]
        );
        $element->add_control(
            'video_url',
            [
                'label' => esc_html__('Video URL', 'uicore-elements'),
                'type' => Controls_Manager::TEXT,
                'condition' => [
                    'content_type' => 'video',
                    'button_lightbox' => 'yes',
                ],
                'ai' => [
                    'active' => false,
                ],
            ]
        );
        $element->end_injection();
    }

    private function get_image_settings($settings)
    {
        $image_settings = [
            'url' => $settings['image']['url'],
            'type' => 'image',
        ];

        $image_id = $settings['image']['id'];

        if ($image_id) {
            $lightbox_image_attributes = Plugin::instance()->images_manager->get_lightbox_image_attributes($image_id);
            $image_settings = array_merge($image_settings, $lightbox_image_attributes);
        }

        return $image_settings;
    }

    private function get_video_settings($settings)
    {
        $video_properties = Embed::get_video_properties($settings['video_url']);
        $video_url = null;
        if (! $video_properties) {
            $video_type = 'hosted';
            $video_url = $settings['video_url'];
        } else {
            $video_type = $video_properties['provider'];
            $video_url = Embed::get_embed_url($settings['video_url']);
        }

        if (null === $video_url) {
            return '';
        }

        return [
            'type' => 'video',
            'videoType' => $video_type,
            'url' => $video_url,
        ];
    }

    public function render_widget($widget_content, $widget)
    {

        // TODO: Discover how to target button widget specifically since this fallback at all widgets is not good
        if ('button' !== $widget->get_name()) {
            return $widget_content;
        }

        $settings = $widget->get_settings();
        $value = [];

        if ($settings['button_lightbox'] !== 'yes') {
            return $widget_content;
        }

        // Get proper content settings
        if ('image' === $settings['content_type'] && $settings['image']) {
            $value = $this->get_image_settings($settings);
        } elseif ('video' === $settings['content_type'] && $settings['video_url']) {
            $value = $this->get_video_settings($settings);
        }

        if (! $value) {
            return $widget_content;
        }

        // Create the lightbox URL hash
        $url = Frontend::instance()->create_action_hash('lightbox', $value);

        // Inject URL to markup
        $widget_content = str_replace(
            '<a ',
            '<a href="' . esc_url($url) . '" ',
            $widget_content
        );

        return $widget_content;
    }
}

// Initialize the class
new Lightbox_Button();

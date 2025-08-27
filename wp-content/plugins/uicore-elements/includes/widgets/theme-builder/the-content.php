<?php

namespace UiCoreElements\ThemeBuilder\Widgets;

use Elementor\Widget_Base;
use Elementor\Controls_Manager;
use Elementor\Core\Kits\Documents\Tabs\Global_Colors;
use Elementor\Core\Kits\Documents\Tabs\Global_Typography;
use Elementor\Group_Control_Typography;


defined('ABSPATH') || exit();

/**
 * Scripts and Styles Class
 */
class TheContent extends Widget_Base
{
    public function get_name()
    {
        return 'uicore-the-content';
    }

    public function get_title()
    {
        return esc_html__('Post Content', 'uicore-elements');
    }

    public function get_icon()
    {
        return 'eicon-post-content ui-e-widget';
    }

    public function get_categories()
    {
        return ['uicore', 'uicore-theme-builder'];
    }

    public function get_keywords()
    {
        return ['content', 'post'];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }

    public function show_in_panel()
    {
        return true;
    }

    protected function register_controls()
    {
        $this->start_controls_section(
            'section_style',
            [
                'label' => esc_html__('Style', 'uicore-elements'),
                'tab' => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->add_responsive_control(
            'align',
            [
                'label' => esc_html__('Alignment', 'uicore-elements'),
                'type' => Controls_Manager::CHOOSE,
                'options' => [
                    'left' => [
                        'title' => esc_html__('Left', 'uicore-elements'),
                        'icon' => 'eicon-text-align-left',
                    ],
                    'center' => [
                        'title' => esc_html__('Center', 'uicore-elements'),
                        'icon' => 'eicon-text-align-center',
                    ],
                    'right' => [
                        'title' => esc_html__('Right', 'uicore-elements'),
                        'icon' => 'eicon-text-align-right',
                    ],
                    'justify' => [
                        'title' => esc_html__('Justified', 'uicore-elements'),
                        'icon' => 'eicon-text-align-justify',
                    ],
                ],
                'selectors' => [
                    '{{WRAPPER}}' => 'text-align: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'text_color',
            [
                'label' => esc_html__('Text Color', 'uicore-elements'),
                'type' => Controls_Manager::COLOR,
                'default' => '',
                'selectors' => [
                    '{{WRAPPER}}' => 'color: {{VALUE}};',
                ],
                'global' => [
                    'default' => Global_Colors::COLOR_TEXT,
                ],
            ]
        );

        $this->add_group_control(
            Group_Control_Typography::get_type(),
            [
                'name' => 'typography',
                'global' => [
                    'default' => Global_Typography::TYPOGRAPHY_TEXT,
                ],
            ]
        );

        $this->end_controls_section();
    }

    protected function render()
    {
        return $this->render_post_content();
    }

    public function render_plain_content() {}

    public function render_post_content($with_wrapper = false)
    {
        static $did_posts = [];
        $elementor_instance = \Elementor\Plugin::instance();
        $post = get_post();

        if (post_password_required($post->ID)) {
            //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            echo get_the_password_form($post->ID); // WPCS: XSS ok.
            return;
        }

        // Avoid recursion
        if (isset($did_posts[$post->ID])) {
            return;
        }

        $did_posts[$post->ID] = true;

        // End avoid recursion
        $editor = $elementor_instance->editor;
        $is_edit_mode = $editor->is_edit_mode();

        if (!$is_edit_mode && !isset($_GET['uicore-tb'])) { //phpcs:ignore WordPress.Security.NonceVerification.Recommended
            the_content();
        } else {
            echo 'This is a dummy text to demonstration purposes. It will be replaced with the post content/excerpt. <p>Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Morbi scelerisque luctus velit. Etiam quis quam. Duis viverra diam non justo. Suspendisse sagittis ultrices augue. Duis sapien nunc, commodo et, interdum suscipit, sollicitudin et, dolor. Donec ipsum massa, ullamcorper in, auctor et. Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Aenean placerat. Pellentesque sapien. Mauris metus. Maecenas libero. Mauris dolor felis, sagittis at, luctus sed, aliquam non, tellus. In rutrum. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat. Praesent in mauris eu tortor porttitor accumsan. Nunc tincidunt ante vitae massa. Curabitur bibendum justo non orci. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Curabitur vitae diam non enim vestibulum interdum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Et harum quidem rerum facilis est et expedita distinctio. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus.</p><p>Proin pede metus, vulputate nec, fermentum fringilla, vehicula vitae, justo. Temporibus autem quibusdam et aut officiis debitis aut rerum necessitatibus saepe eveniet ut et voluptates repudiandae sint et molestiae non recusandae. Aenean placerat. Pellentesque sapien. Mauris metus. Maecenas libero. Mauris dolor felis, sagittis at, luctus sed, aliquam non, tellus. In rutrum. Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus maiores alias consequatur aut perferendis doloribus asperiores repellat. Praesent in mauris eu tortor porttitor accumsan. Nunc tincidunt ante vitae massa. Curabitur bibendum justo non orci. Quis autem vel eum iure reprehenderit qui in ea voluptate velit esse quam nihil molestiae consequatur, vel illum qui dolorem eum fugiat quo voluptas nulla pariatur? Curabitur vitae diam non enim vestibulum interdum. Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo. Et harum quidem rerum facilis est et expedita distinctio. Duis bibendum, lectus ut viverra rhoncus, dolor nunc faucibus libero, eget facilisis enim ipsum id lacus.</p> End of the dummy content.';
        }

        // Restore edit mode state
        $elementor_instance->editor->set_edit_mode($is_edit_mode);
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new TheContent());

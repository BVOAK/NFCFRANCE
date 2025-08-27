<?php

namespace ElementPack\Modules\AcfTabs\Widgets;

use Elementor\Controls_Manager;
use ElementPack\Base\Module_Base;
use ElementPack\Includes\ACF_Global;
use ElementPack\Traits\Global_Widget_Controls;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class Acf_Tabs extends Module_Base
{
    use Global_Widget_Controls;

    public function get_name()
    {
        return 'bdt-acf-tabs';
    }

    public function get_title()
    {
        return BDTEP . esc_html__('ACF Tabs', 'bdthemes-element-pack');
    }

    public function get_icon()
    {
        return 'bdt-wi-acf-tabs';
    }

    public function get_categories()
    {
        return ['element-pack'];
    }

    public function get_keywords()
    {
        return ['acf', 'acf-tabs', 'tabs', 'toggle', 'accordion'];
    }

    public function is_reload_preview_required()
    {
        return false;
    }

    public function get_style_depends()
    {
        if ($this->ep_is_edit_mode()) {
            return ['ep-styles'];
        } else {
            return ['ep-tabs'];
        }
    }
    public function get_script_depends()
    {
        if ($this->ep_is_edit_mode()) {
            return ['ep-scripts'];
        } else {
            return ['ep-tabs'];
        }
    }

    public function get_custom_help_url()
    {
        return '';
    }

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}
    
    protected function register_controls()
    {
        $this->start_controls_section(
            'section_title',
            [
                'label' => esc_html__('Tabs', 'bdthemes-element-pack'),
            ]
        );

        $this->add_control(
            'tab_layout',
            [
                'label'   => esc_html__('Layout', 'bdthemes-element-pack'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'default',
                'options' => [
                    'default' => esc_html__('Top (Default)', 'bdthemes-element-pack'),
                    'bottom'  => esc_html__('Bottom', 'bdthemes-element-pack'),
                    'left'    => esc_html__('Left', 'bdthemes-element-pack'),
                    'right'   => esc_html__('Right', 'bdthemes-element-pack'),
                ],
            ]
        );

        $this->add_control(
            'field',
            [
                'label' => __('Repeater Field', 'bdthemes-element-pack'),
                'dynamic' => ['active' => false],
                'type'    => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => __('Type and select the repeater field...', 'bdthemes-element-pack'),
                /* translators: %1$s: Field type name */
                'description' => sprintf(__('Supported field type: <b>%1$s</b>', 'bdthemes-element-pack'), 'Repeater'),
                'query_args'  => [
                    'query'   => 'acf',
                    'field_type' => ['repeater'],
                ],
            ]
        );

        $this->add_control(
            'title',
            [
                'label' => __('Title', 'bdthemes-element-pack'),
                'dynamic' => ['active' => false],
                'type'        => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => __('Type repeater sub field for accordion title', 'bdthemes-element-pack'),
                /* translators: %1$s, %2$s, %3$s: Field type names */
                'description' => sprintf(__('Supported field type: <b>%1$s</b>, <b>%2$s</b>, <b>%3$s</b>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['text', 'textarea', 'wysiwyg'],
                ],
            ]
        );
        $this->add_control(
            'sub_title',
            [
                'label' => __('Sub Title', 'bdthemes-element-pack'),
                'dynamic' => ['active' => false],
                'type'        => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => __('Type repeater sub field for accordion sub title', 'bdthemes-element-pack'),
                /* translators: %1$s, %2$s, %3$s: Field type names */
                'description' => sprintf(__('Supported field type: <b>%1$s</b>, <b>%2$s</b>, <b>%3$s</b>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['text', 'textarea', 'wysiwyg'],
                ],
            ]
        );


        $this->add_control(
            'content',
            [
                'label' => __('Content', 'bdthemes-element-pack'),
                'dynamic' => ['active' => false],
                'type'        => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => __('Type repeater sub field for accordion content', 'bdthemes-element-pack'),
                /* translators: %1$s, %2$s, %3$s: Field type names */
                'description' => sprintf(__('Supported field type: <b>%1$s</b>, <b>%2$s</b>, <b>%3$s</b>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['text', 'textarea', 'wysiwyg'],
                ],
            ]
        );

        $this->register_tabs_controls(); // Global controls from trait
    }

    protected function render()
    {
        $settings    = $this->get_settings_for_display();
        $id          = $this->get_id() .'-'. rand(1000, 9999);
        $stickyClass = '';
        if (isset($settings['nav_sticky_mode']) && $settings['nav_sticky_mode'] == 'yes') {
            $stickyClass = 'bdt-sticky-custom';
        }

        $this->add_render_attribute(
            [
                'tabs_sticky_data' => [
                    'data-settings' => [
                        wp_json_encode(
                            array_filter([
                                "id"                  => 'bdt-tabs-' . $id,
                                "status"              => $stickyClass,
                                "activeHash"          => $settings['active_hash'],
                                "hashTopOffset"       => (isset($settings['hash_top_offset']['size']) && !empty($settings['hash_top_offset']['size'])) ? $settings['hash_top_offset']['size'] : 70,
                                "hashScrollspyTime"   => (isset($settings['hash_scrollspy_time']['size']) ? $settings['hash_scrollspy_time']['size'] : 1500),
                                "navStickyOffset"     => (isset($settings['nav_sticky_offset']['size']) ? $settings['nav_sticky_offset']['size'] : 1),
                                "activeItem"          => (!empty($settings['active_item'])) ? $settings['active_item'] : NULL,
                                "linkWidgetId"        => $id,
                                "sectionBgSelector"   => $settings['enable_section_bg'] == 'yes' ? $settings['section_bg_selector'] : false,
                                "sectionBg"           => $settings['enable_section_bg'] == 'yes' ? $this->render_tabs_section_background() : false,
                                "sectionBgAnim"       => $settings['enable_section_bg'] == 'yes' ? $settings['section_bg_anim'] : false,
                            ])
                        ),
                    ],
                ],
            ]
        );

        $this->add_render_attribute('tabs', 'id', 'bdt-tabs-' . esc_attr($id));
        $this->add_render_attribute('tabs', 'class', 'bdt-tabs ');
        $this->add_render_attribute('tabs', 'class', 'bdt-tabs-' . $settings['tab_layout']);

        if ($settings['fullwidth_on_mobile']) {
            $this->add_render_attribute('tabs', 'class', 'fullwidth-on-mobile');
        }

        ?>
        <div class="bdt-tabs-area">

            <div <?php $this->print_render_attribute_string('tabs'); ?> <?php $this->print_render_attribute_string('tabs_sticky_data'); ?>>
                <?php if ('left' == $settings['tab_layout'] or 'right' == $settings['tab_layout']) {
                    echo '<div class="bdt-grid-collapse"  bdt-grid>';
                }
                
                if ('bottom' == $settings['tab_layout']) :
                    $this->tabs_content($id);
                endif;

                $this->desktop_tab_items($id);


                if ('bottom' != $settings['tab_layout']) :
                    $this->tabs_content($id);
                endif;

                if ('left' == $settings['tab_layout'] or 'right' == $settings['tab_layout']) {
                    echo "</div>";
                }
                ?>
                <a href="#" id="bottom-anchor-<?php echo esc_attr($id); ?>" data-bdt-hidden></a>
            </div>
        </div>
    <?php
    }

    public function tabs_content($id)
    {
        $settings = $this->get_settings_for_display();

        $this->add_render_attribute('switcher-width', 'class', 'bdt-switcher-wrapper');

        if ('left' == $settings['tab_layout'] or 'right' == $settings['tab_layout']) {

            if (768 == $settings['media']) {
                $this->add_render_attribute('switcher-width', 'class', 'bdt-width-expand@s');
            } else {
                $this->add_render_attribute('switcher-width', 'class', 'bdt-width-expand@m');
            }
        }

    ?>

        <div <?php $this->print_render_attribute_string('switcher-width'); ?>>
            <div id="bdt-tab-content-<?php echo esc_attr($id); ?>" class="bdt-switcher bdt-switcher-item-content">
                <?php

                $repeater_field = get_field_object( $settings['field'] );

                if (empty($settings['field'] && $repeater_field)) {
                    return;
                }

                $acf_helper = new ACF_Global();
                $field_values = $acf_helper->get_acf_field_value( $settings['field'], $repeater_field['parent'] );

                if (empty($field_values)) {
                    return;
                }

                $content = $settings['content'];
                $title = $settings['title'];
                
                foreach ($field_values as $index => $item) : ?>
                    <?php

                    $tab_count = $index + 1;
                    $tab_count_active = '';
                    if ($tab_count === $settings['active_item']) {
                        $tab_count_active = 'bdt-active';
                    }

                    $field_content = isset($item[$content]) ? $item[$content] : '';
                    $field_title = isset($item[$title]) ? $item[$title] : '';

                    ?>
                    <div class="bdt-tab-content-item <?php echo esc_attr($tab_count_active); ?>" data-content-id="<?php echo esc_attr(strtolower(preg_replace('#[ -]+#', '-', trim(preg_replace("![^a-z0-9]+!i", " ", esc_html($field_title)))))); ?>">
                        <div><?php echo wp_kses_post($field_content); ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    }
    public function render_loop_item($settings, $id) {

        $repeater_field = get_field_object( $settings['field'] );
        if (empty($settings['field'] && $repeater_field)) {
            return;
        }
        $acf_helper = new ACF_Global();
        $field_values = $acf_helper->get_acf_field_value( $settings['field'], $repeater_field['parent'] );
        if (empty($field_values)) {
            return;
        }
        $title = $settings['title']; 
        $sub_title = $settings['sub_title']; 
        
        ?>
        <div <?php $this->print_render_attribute_string('tabs-width'); ?>>
            <div <?php $this->print_render_attribute_string('tabs-sticky'); ?>>
                <div <?php $this->print_render_attribute_string('tab-settings'); ?>>
                    <?php foreach ($field_values as $index => $item) :

                        $field_title = isset($item[$title]) ? $item[$title] : '';
                        $field_sub_title = isset($item[$sub_title]) ? $item[$sub_title] : '';

                        $tab_count = $index + 1;
                        $tab_id = ($field_title) ? $field_title : $id . $tab_count;

                        $hash_text = sanitize_text_field(trim(str_replace(" ", "-", $tab_id)));

                        $tab_id =   'bdt-tab-' . $hash_text;
                        

                        $this->add_render_attribute('tabs-item', 'class', 'bdt-tabs-item', true);
                        if (empty($field_title)) {
                            $this->add_render_attribute('tabs-item', 'class', 'bdt-has-no-title');
                        }
                        if ($tab_count === $settings['active_item']) {
                            $this->add_render_attribute('tabs-item', 'class', 'bdt-active');
                        }

                        $this->add_render_attribute('tab-link', 'data-title', $hash_text, true);

                        if (empty($field_title)) {
                            $this->add_render_attribute('tab-link', 'data-title', $this->get_id() . '-' . $tab_count, true);
                        }

                        $this->add_render_attribute('tab-link', 'class', 'bdt-tabs-item-title', true);
                        $this->add_render_attribute('tab-link', 'id', esc_attr($tab_id), true);
                        $this->add_render_attribute('tab-link', 'data-tab-index', esc_attr($index), true);

                        // New Added
                        $this->remove_render_attribute('tab-link', 'onclick');
                        $this->add_render_attribute('tab-link', 'href', '#', true);

                    ?>
                        <div <?php $this->print_render_attribute_string('tabs-item'); ?>>
                            <a <?php $this->print_render_attribute_string('tab-link'); ?>>
                                <div class="bdt-tab-text-wrapper bdt-flex-column">

                                    <div class="bdt-tab-title-icon-wrapper">                              
                                        <?php if ($field_title) : ?>
                                            <span class="bdt-tab-text">
                                                <?php echo wp_kses($field_title, element_pack_allow_tags('title')); ?>
                                            </span>
                                        <?php endif; ?>
                                    </div>

                                    <?php if ($field_sub_title and $field_title) : ?>
                                        <span class="bdt-tab-sub-title bdt-text-small">
                                            <?php echo wp_kses($field_sub_title, element_pack_allow_tags('title')); ?>
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
<?php
    }
}

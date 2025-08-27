<?php

namespace ElementPack\Modules\AcfAccordion\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Icons_Manager;
use ElementPack\Utils;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;
use ElementPack\Includes\ACF_Global;
use ElementPack\Traits\Global_Widget_Controls;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class Acf_Accordion extends Module_Base {
    use Global_Widget_Controls;

    public function get_name() {
        return 'bdt-acf-accordion';
    }

    public function get_title() {
        return BDTEP . esc_html__('ACF Accordion', 'bdthemes-element-pack');
    }

    public function get_icon() {
        return 'bdt-wi-acf-accordion';
    }

    public function get_categories() {
        return ['element-pack'];
    }

    public function get_keywords() {
        return ['acf', 'accordion', 'acf-accordion'];
    }

    public function get_style_depends() {
        if ($this->ep_is_edit_mode()) {
            return ['ep-styles'];
        } else {
            return ['ep-accordion'];
        }
    }

    public function get_script_depends() {
        if ($this->ep_is_edit_mode()) {
            return ['ep-scripts'];
        } else {
            return ['ep-accordion'];
        }
    }

    public function get_custom_help_url() {
        return '';
    }

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}

    protected function register_controls() {
        $this->start_controls_section(
            'section_title',
            [
                'label' => __('ACF Accordion', 'bdthemes-element-pack'),
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
                /* translators: %1$s is field type */
                'description' => sprintf(esc_html__('Supported field type: <b>%1$s</b>', 'bdthemes-element-pack'), 'Repeater'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['repeater'],
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
                /* translators: %1$s, %2$s and %3$s are field types */
                'description' => sprintf(esc_html__('Supported field type: <b>%1$s</b>, <b>%2$s</b>, <b>%3$s</b>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
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
                /* translators: %1$s, %2$s and %3$s are field types */
                'description' => sprintf(esc_html__('Supported field type: <b>%1$s</b>, <b>%2$s</b>, <b>%3$s</b>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['text', 'textarea', 'wysiwyg'],
                ],
            ]
        );

        $this->add_control(
            'title_html_tag',
            [
                'label'   => __('Title HTML Tag', 'bdthemes-element-pack'),
                'type'    => Controls_Manager::SELECT,
                'options' => element_pack_title_tags(),
                'default' => 'div',
            ]
        );

        $this->add_control(
            'accordion_icon',
            [
                'label'            => __('Icon', 'bdthemes-element-pack'),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon',
                'default'          => [
                    'value'   => 'fas fa-plus',
                    'library' => 'fa-solid',
                ],
                'recommended'      => [
                    'fa-solid'   => [
                        'chevron-down',
                        'angle-down',
                        'angle-double-down',
                        'caret-down',
                        'caret-square-down',
                    ],
                    'fa-regular' => [
                        'caret-square-down',
                    ],
                ],
                'skin'             => 'inline',
                'label_block'      => false,
            ]
        );

        $this->add_control(
            'accordion_active_icon',
            [
                'label'            => __('Active Icon', 'bdthemes-element-pack'),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'icon_active',
                'default'          => [
                    'value'   => 'fas fa-minus',
                    'library' => 'fa-solid',
                ],
                'recommended'      => [
                    'fa-solid'   => [
                        'chevron-up',
                        'angle-up',
                        'angle-double-up',
                        'caret-up',
                        'caret-square-up',
                    ],
                    'fa-regular' => [
                        'caret-square-up',
                    ],
                ],
                'skin'             => 'inline',
                'label_block'      => false,
                'condition'        => [
                    'accordion_icon[value]!' => '',
                ],
            ]
        );

        $this->add_control(
            'show_custom_icon',
            [
                'label'   => esc_html__('Show Title Icon', 'bdthemes-element-pack'),
                'type'    => Controls_Manager::SWITCHER,
                'separator' => 'before'
            ]
        );

        $this->end_controls_section();

        // Global controls from trait
        $this->register_accordion_controls();
    }

    protected function render() {

        $settings = $this->get_settings_for_display();
        $id       = 'bdt-ep-accordion-' . $this->get_id();
        
        $this->add_render_attribute(
            [
                'accordion' => [
                    'id'            => $id,
                    'class'         => 'bdt-ep-accordion bdt-accordion',
                    'data-bdt-accordion' => [
                        wp_json_encode([
                            "collapsible" => $settings["collapsible"] ? true : false,
                            "multiple"    => $settings["multiple"] ? true : false,
                            "transition"  => "ease-in-out",
                        ])
                    ]
                ]
            ]
        );

        $this->add_render_attribute(
            [
                'accordion_data' => [
                    'data-settings' => [
                        wp_json_encode([
                            "id"                => 'bdt-ep-accordion-' . $this->get_id(),
                            'activeHash'        => $settings['active_hash'],
                            'activeScrollspy'   => $settings['active_scrollspy'],
                            'hashTopOffset'     => isset($settings['hash_top_offset']['size']) ? $settings['hash_top_offset']['size'] : false,
                            'hashScrollspyTime' => isset($settings['hash_scrollspy_time']['size']) ? $settings['hash_scrollspy_time']['size'] : false,
                            "closeAllItemsOnMobile"    => $settings["close_all_items_on_mobile"] ? true : false,
                        ]),
                    ],
                ],
            ]
        );

        $migrated = isset($settings['__fa4_migrated']['accordion_icon']);
        $is_new   = empty($settings['icon']) && Icons_Manager::is_migration_allowed();

        $active_migrated = isset($settings['__fa4_migrated']['accordion_active_icon']);
        $active_is_new   = empty($settings['icon_active']) && Icons_Manager::is_migration_allowed();

        if ($settings['schema_activity'] == 'yes') {
            $this->add_render_attribute('accordion', 'itemscope');
            $this->add_render_attribute('accordion', ['itemtype' => 'https://schema.org/FAQPage']);
        }

        ?>
        <div class="bdt-ep-accordion-container">
            <div <?php $this->print_render_attribute_string('accordion'); ?> <?php $this->print_render_attribute_string('accordion_data'); ?>>
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

                $title = $settings['title'];
                $content = $settings['content'];

                foreach ($field_values as $index => $value) :
                    $acc_count = $index + 1;
                    $field_title = isset($value[$title]) ? $value[$title] : '';
                    $field_content = isset($value[$content]) ? $value[$content] : '';

                    if (!empty($field_title) or !empty($field_content)) :                          

                    $acc_id = ($field_title) ? element_pack_string_id($field_title) : $id . $acc_count;
                    $acc_id = 'bdt-ep-accordion-' . $acc_id;

                    $tab_title_setting_key = 'tab_title'.$index;
                    $tab_content_setting_key = 'tab_content'.$index;

                    $this->add_render_attribute($tab_title_setting_key, [
                        'class' => ['bdt-ep-accordion-title bdt-accordion-title bdt-flex bdt-flex-middle']
                    ]);

                    $this->add_render_attribute($tab_title_setting_key, 'class', ('right' == $settings['icon_align']) ? 'bdt-flex-between' : '');


                    $this->add_render_attribute($tab_content_setting_key, [
                        'class' => ['bdt-ep-accordion-content bdt-accordion-content'],
                    ]);

                    $item_key = 'bdt-item-' . $index;

                    $this->add_render_attribute($item_key, [
                        'class' => ($acc_count === $settings['active_item']) ? 'bdt-ep-accordion-item bdt-open' : 'bdt-ep-accordion-item',
                    ]);

                    if ($settings['schema_activity'] == 'yes') {
                        $this->add_render_attribute($item_key, 'itemscope');
                        $this->add_render_attribute($item_key, 'itemprop', 'mainEntity');
                        $this->add_render_attribute($item_key, 'itemtype', 'https://schema.org/Question');

                        $this->add_render_attribute($tab_content_setting_key, 'itemscope');
                        $this->add_render_attribute($tab_content_setting_key, 'itemprop', 'acceptedAnswer', true);
                        $this->add_render_attribute($tab_content_setting_key, 'itemtype', 'https://schema.org/Answer', true);
                    }

                    ?>
                    <div <?php $this->print_render_attribute_string($item_key); ?>>
                        <<?php echo esc_attr(Utils::get_valid_html_tag($settings['title_html_tag'])); ?> <?php $this->print_render_attribute_string($tab_title_setting_key); ?> id="<?php echo esc_attr(strtolower(preg_replace('#[ -]+#', '-', trim(preg_replace("![^a-z0-9]+!i", " ", esc_attr($acc_id)))))); ?>" data-accordion-index="<?php echo esc_attr($index); ?>"  data-title="<?php echo esc_attr(strtolower(preg_replace('#[ -]+#', '-', trim(preg_replace("![^a-z0-9]+!i", " ", esc_html($field_title)))))); ?>">

                            <?php if ($settings['accordion_icon']['value']) : ?>
                                <span class="bdt-ep-accordion-icon bdt-flex-align-<?php echo esc_attr($settings['icon_align']); ?>" aria-hidden="true">

                                    <?php if ($is_new || $migrated) : ?>
                                        <span class="bdt-ep-accordion-icon-closed">
                                            <?php Icons_Manager::render_icon($settings['accordion_icon'], ['aria-hidden' => 'true', 'class' => 'fa-fw']); ?>
                                        </span>
                                    <?php else : ?>
                                        <i class="bdt-ep-accordion-icon-closed <?php echo esc_attr($settings['icon']); ?>" aria-hidden="true"></i>
                                    <?php endif; ?>

                                    <?php if ($active_is_new || $active_migrated) : ?>
                                        <span class="bdt-ep-accordion-icon-opened">
                                            <?php Icons_Manager::render_icon($settings['accordion_active_icon'], ['aria-hidden' => 'true', 'class' => 'fa-fw']); ?>
                                        </span>
                                    <?php else : ?>
                                        <i class="bdt-ep-accordion-icon-opened <?php echo esc_attr($settings['icon_active']); ?>" aria-hidden="true"></i>
                                    <?php endif; ?>

                                </span>
                            <?php endif; ?>

                            <span role="heading" class="bdt-ep-title-text bdt-flex-inline bdt-flex-middle">

                                <?php if (!empty($item['repeater_icon']['value']) and $settings['show_custom_icon'] == 'yes') : ?>
                                    <span class="bdt-ep-accordion-custom-icon">
                                        <?php Icons_Manager::render_icon($item['repeater_icon'], ['aria-hidden' => 'true', 'class' => 'fa-fw']); ?>
                                    </span>
                                <?php endif; ?>
                                <?php echo esc_html($field_title); ?>
                            </span>

                        </<?php echo esc_attr(Utils::get_valid_html_tag($settings['title_html_tag'])); ?>>
                        <div <?php $this->print_render_attribute_string($tab_content_setting_key); ?>>
                        <?php echo wp_kses_post($field_content); ?>
                    
                        </div>
                    </div>
                <?php endif; endforeach; ?>
            </div>
        </div>
    <?php
    }    
}

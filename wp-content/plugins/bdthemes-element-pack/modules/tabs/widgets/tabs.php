<?php

namespace ElementPack\Modules\Tabs\Widgets;

use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use ElementPack\Base\Module_Base;
use ElementPack\Element_Pack_Loader;
use ElementPack\Traits\Global_Widget_Controls;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;

if (!defined('ABSPATH')) {
    exit;
} // Exit if accessed directly

class Tabs extends Module_Base
{
    use Global_Widget_Controls;

    public function get_name()
    {
        return 'bdt-tabs';
    }

    public function get_title()
    {
        return BDTEP . esc_html__('Tabs', 'bdthemes-element-pack');
    }

    public function get_icon()
    {
        return 'bdt-wi-tabs';
    }

    public function get_categories()
    {
        return ['element-pack'];
    }

    public function get_keywords()
    {
        return ['tabs', 'toggle', 'accordion'];
    }

    public function is_reload_preview_required()
    {
        return true;
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
        return 'https://youtu.be/1BmS_8VpBF4';
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

        $repeater = new Repeater();

        $repeater->add_control(
            'tab_title',
            [
                'label'       => esc_html__('Title', 'bdthemes-element-pack'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'default'     => esc_html__('Tab Title', 'bdthemes-element-pack'),
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tab_sub_title',
            [
                'label'       => esc_html__('Sub Title', 'bdthemes-element-pack'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'label_block' => true,
            ]
        );

        $repeater->add_control(
            'tab_select_icon',
            [
                'label'            => esc_html__('Icon', 'bdthemes-element-pack'),
                'type'             => Controls_Manager::ICONS,
                'fa4compatibility' => 'tab_icon',
            ]
        );

        $repeater->add_control(
            'source',
            [
                'label'   => esc_html__('Select Source', 'bdthemes-element-pack'),
                'type'    => Controls_Manager::SELECT,
                'default' => 'custom',
                'options' => [
                    'custom'        => esc_html__('Custom Content', 'bdthemes-element-pack'),
                    "elementor"     => esc_html__('Elementor Template', 'bdthemes-element-pack'),
                    'anywhere'      => esc_html__('AE Template', 'bdthemes-element-pack'),
                    'external_link' => esc_html__('External Link', 'bdthemes-element-pack'),
                    'link_widget'   => esc_html__('Link Widget', 'bdthemes-element-pack'),
                    'link_section'  => esc_html__('Link Section', 'bdthemes-element-pack'),
                ],
            ]
        );
        $repeater->add_control(
            'tab_content',
            [
                'type'      => Controls_Manager::WYSIWYG,
                'dynamic'   => ['active' => true],
                'default'   => esc_html__('Tab Content', 'bdthemes-element-pack'),
                'condition' => ['source' => 'custom'],
            ]
        );
        $repeater->add_control(
            'template_id',
            [
                'label'       => esc_html__('Select Template', 'bdthemes-element-pack'),
                'type'        => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => esc_html__('Type and select template', 'bdthemes-element-pack'),
                'query_args'  => [
                    'query'        => 'elementor_template',
                ],
                'condition'   => ['source' => "elementor"],
            ]
        );
        $repeater->add_control(
            'anywhere_id',
            [
                'label'       => esc_html__('Select Template', 'bdthemes-element-pack'),
                'type'        => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => esc_html__('Type and select template', 'bdthemes-element-pack'),
                'query_args'  => [
                    'query'        => 'anywhere_template',
                ],
                'condition'   => ['source' => "anywhere"],
            ]
        );

        $repeater->add_control(
            'source_link_widget',
            [
                'label'       => esc_html__('Link Widget ID', 'bdthemes-element-pack') . BDTEP_NC,
                'placeholder' => esc_html('#test'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'condition'   => ['source' => "link_widget"],
            ]
        );

        $repeater->add_control(
            'source_link_widget_note',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('Note: Please insert multiple widgets on the same section then place your widgets id here. You must use Link widget on every Items. Results will visible on the front page.', 'bdthemes-element-pack'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                'condition'   => ['source' => "link_widget"],
            ]
        );

        $repeater->add_control(
            'source_link_section',
            [
                'label'       => esc_html__('Link Section ID', 'bdthemes-element-pack') . BDTEP_NC,
                'placeholder' => esc_html('#test'),
                'type'        => Controls_Manager::TEXT,
                'dynamic'     => ['active' => true],
                'condition'   => ['source' => "link_section"],
            ]
        );

        $repeater->add_control(
            'source_link_section_note',
            [
                'type' => Controls_Manager::RAW_HTML,
                'raw' => esc_html__('Note: You must use Link Section on every Items. Results will visible on the front page.', 'bdthemes-element-pack'),
                'content_classes' => 'elementor-panel-alert elementor-panel-alert-info',
                'condition'   => ['source' => "link_section"],
            ]
        );

        $repeater->add_control(
            'external_link',
            [
                'label'       => esc_html__('External Link', 'bdthemes-element-pack'),
                'type'        => Controls_Manager::URL,
                'dynamic'     => ['active' => true],
                'placeholder' => esc_html__('https://your-link.com', 'bdthemes-element-pack'),
                'default'     => [
                    'url' => '#',
                ],
            ]
        );

        $this->add_control(
            'tabs',
            [
                'label'       => esc_html__('Tab Items', 'bdthemes-element-pack'),
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    [
                        'tab_title'   => esc_html__('Tab #1', 'bdthemes-element-pack'),
                        'tab_content' => esc_html__('I am tab #1 content. Click edit button to change this text. One morning, when Gregor Samsa woke from troubled dreams, he found himself transformed in his bed into a horrible vermin.', 'bdthemes-element-pack'),
                    ],
                    [
                        'tab_title'   => esc_html__('Tab #2', 'bdthemes-element-pack'),
                        'tab_content' => esc_html__('I am tab #2 content. Click edit button to change this text. A collection of textile samples lay spread out on the table - Samsa was a travelling salesman.', 'bdthemes-element-pack'),
                    ],
                    [
                        'tab_title'   => esc_html__('Tab #3', 'bdthemes-element-pack'),
                        'tab_content' => esc_html__('I am tab #3 content. Click edit button to change this text. Drops of rain could be heard hitting the pane, which made him feel quite sad. How about if I sleep a little bit longer and forget all this nonsense.', 'bdthemes-element-pack'),
                    ],
                ],
                'title_field' => '{{{ tab_title }}}',
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


        // link widget
        $link_widget_arr = [];
        foreach ($settings['tabs'] as $index => $item) {
            $link_widget_arr[$index] = $item['source_link_widget'];
        }

        // link section
        $link_section_arr = [];
        foreach ($settings['tabs'] as $index => $item) {
            $link_section_arr[$index] = $item['source_link_section'];
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
                                "linkWidgetSettings"  => ($item['source'] == 'link_widget') ? $link_widget_arr : NULL,
                                "linkSectionSettings" => ($item['source'] == 'link_section') ? $link_section_arr : NULL,
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
                <?php foreach ($settings['tabs'] as $index => $item) : ?>
                    <?php

                    $tab_count = $index + 1;
                    $tab_count_active = '';
                    if ($tab_count === $settings['active_item']) {
                        $tab_count_active = 'bdt-active';
                    }

                    ?>
                    <div class="bdt-tab-content-item <?php echo esc_attr($tab_count_active); ?>" 
                    data-content-id="<?php echo esc_attr(strtolower(preg_replace('#[ -]+#', '-', trim(preg_replace("![^a-z0-9]+!i", " ", esc_html($item['tab_title'])))))); ?>">
                        <div>
                            <?php
                            $tabId = $this->get_id() . '-' . $tab_count;
                            if ('custom' == $item['source'] and !empty($item['tab_content'])) {
                                $this->print_text_editor($item['tab_content']);
                            } elseif ("elementor" == $item['source'] and !empty($item['template_id'])) {
                                element_pack_template_on_modal_with_iframe($item['template_id'], $tabId);
                                echo Element_Pack_Loader::elementor()->frontend->get_builder_content_for_display($item['template_id']);
                            } elseif ('anywhere' == $item['source'] and !empty($item['anywhere_id'])) {
                                element_pack_template_on_modal_with_iframe($item['anywhere_id'], $tabId);
                                echo Element_Pack_Loader::elementor()->frontend->get_builder_content_for_display($item['anywhere_id']);
                            }
                            ?>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php
    }
    public function render_loop_item($settings, $id) {
        ?>
        <div <?php $this->print_render_attribute_string('tabs-width'); ?>>
            <div <?php $this->print_render_attribute_string('tabs-sticky'); ?>>
                <div <?php $this->print_render_attribute_string('tab-settings'); ?>>
                    <?php foreach ($settings['tabs'] as $index => $item) :

                        $tab_count = $index + 1;
                        $tab_id = ($item['tab_title']) ? $item['tab_title'] : $id . $tab_count;
                        // $tab_id = 'bdt-tab-' . $tab_id;
                        // $tab_id = 'bdt-tab-' . strtolower(preg_replace('#[ -]+#', '-', trim(preg_replace("![^a-z0-9]+!i", " ", $tab_id))));

                        $hash_text = sanitize_text_field(trim(str_replace(" ", "-", $tab_id)));

                        $tab_id =   'bdt-tab-' . $hash_text;
                        

                        $this->add_render_attribute('tabs-item', 'class', 'bdt-tabs-item', true);
                        if (empty($item['tab_title'])) {
                            $this->add_render_attribute('tabs-item', 'class', 'bdt-has-no-title');
                        }
                        if ($tab_count === $settings['active_item']) {
                            $this->add_render_attribute('tabs-item', 'class', 'bdt-active');
                        }

                        if (!isset($item['tab_icon']) && !Icons_Manager::is_migration_allowed()) {
                            // add old default
                            $item['tab_icon'] = 'fas fa-book';
                        }

                        $migrated = isset($item['__fa4_migrated']['tab_select_icon']);
                        $is_new   = empty($item['tab_icon']) && Icons_Manager::is_migration_allowed();

                        $this->add_render_attribute('tab-link', 'data-title', $hash_text, true);

                        if (empty($item['tab_title'])) {
                            $this->add_render_attribute('tab-link', 'data-title', $this->get_id() . '-' . $tab_count, true);
                        }

                        $this->add_render_attribute('tab-link', 'class', 'bdt-tabs-item-title', true);
                        $this->add_render_attribute('tab-link', 'id', esc_attr($tab_id), true);
                        $this->add_render_attribute('tab-link', 'data-tab-index', esc_attr($index), true);
                        if ('external_link' == $item['source'] and '' !== $item['external_link']['url']) {
                            $target = $item['external_link']['is_external'] ? '_blank' : '_self';
                            $this->add_render_attribute('tab-link', 'href', $item['external_link']['url'], true);
                            $this->add_render_attribute('tab-link', 'onclick', "window.open('" . esc_url($item['external_link']['url']) . "', '$target')", true);
                        } else {
                            $this->remove_render_attribute('tab-link', 'onclick');
                            $this->add_render_attribute('tab-link', 'href', '#', true);
                        }

                    ?>
                        <div <?php $this->print_render_attribute_string('tabs-item'); ?>>
                            <a <?php $this->print_render_attribute_string('tab-link'); ?>>
                                <div class="bdt-tab-text-wrapper bdt-flex-column">

                                    <div class="bdt-tab-title-icon-wrapper">

                                        <?php if ('' != $item['tab_select_icon']['value'] and 'left' == $settings['icon_align']) : ?>
                                            <span class="bdt-button-icon-align-<?php echo esc_html($settings['icon_align']); ?>">

                                                <?php if ($is_new || $migrated) :
                                                    Icons_Manager::render_icon($item['tab_select_icon'], [
                                                        'aria-hidden' => 'true',
                                                        'class'       => 'fa-fw'
                                                    ]);
                                                else : ?>
                                                    <i class="<?php echo esc_attr($item['tab_icon']); ?>" aria-hidden="true"></i>
                                                <?php endif; ?>

                                            </span>
                                        <?php endif; ?>

                                        <?php if ($item['tab_title']) : ?>
                                            <span class="bdt-tab-text">
                                                <?php echo wp_kses($item['tab_title'], element_pack_allow_tags('title')); ?>
                                            </span>
                                        <?php endif; ?>

                                        <?php if ('' != $item['tab_select_icon']['value'] and 'right' == $settings['icon_align']) : ?>
                                            <span class="bdt-button-icon-align-<?php echo esc_html($settings['icon_align']); ?>">

                                                <?php if ($is_new || $migrated) :
                                                    Icons_Manager::render_icon($item['tab_select_icon'], [
                                                        'aria-hidden' => 'true',
                                                        'class'       => 'fa-fw'
                                                    ]);
                                                else : ?>
                                                    <i class="<?php echo esc_attr($item['tab_icon']); ?>" aria-hidden="true"></i>
                                                <?php endif; ?>

                                            </span>
                                        <?php endif; ?>

                                    </div>

                                    <?php if ($item['tab_sub_title'] and $item['tab_title']) : ?>
                                        <span class="bdt-tab-sub-title bdt-text-small">
                                            <?php echo wp_kses($item['tab_sub_title'], element_pack_allow_tags('title')); ?>
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

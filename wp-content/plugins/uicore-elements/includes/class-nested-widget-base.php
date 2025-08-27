<?php

namespace UiCoreElements;

use Elementor\Plugin;
use Elementor\Settings;

/**
 * Base for nested widgets.
 * Based on Elementor Nested Widget Class. Keep it in sync with future updates on Elementor core.
 *
 * @since 1.0.6
 */

abstract class UiCoreNestedWidget extends UiCoreWidget
{

    /**
     * Get default children elements structure.
     *
     * @return array
     */
    abstract protected function get_default_children_elements();

    /**
     * Get repeater title setting key name.
     *
     * @return string
     */
    abstract protected function get_default_repeater_title_setting_key();

    /**
     * Get default children title for the navigator, using `%d` as index in the format.
     *
     * @note The title in this method is used to set the default title for each created child in nested element.
     * for handling the children title for new created widget(s), use `get_default_children_elements()` method,
     * eg:
     * [
     *      'elType' => 'container',
     *      'settings' => [
     *          '_title' => __( 'Tab #1', 'uicore-elements' ),
     *      ],
     * ],
     * @return string
     */
    protected function get_default_children_title()
    {
        /* translators: item number */
        return esc_html__('Item #%d', 'uicore-elements');
    }

    /**
     * Get default children placeholder selector, Empty string, means will be added at the end view.
     *
     * @return string
     */
    protected function get_default_children_placeholder_selector()
    {
        return '';
    }

    protected function get_default_children_container_placeholder_selector()
    {
        return '';
    }

    /**
     * @inheritDoc
     *
     * To support nesting.
     */
    protected function _get_default_child_type(array $element_data)
    {
        return Plugin::$instance->elements_manager->get_element_types($element_data['elType']);
    }

    /**
     * @inheritDoc
     *
     * Adding new 'defaults' config for handling children elements.
     */
    protected function get_initial_config()
    {
        return array_merge(parent::get_initial_config(), [
            'defaults' => [
                'elements' => $this->get_default_children_elements(),
                'elements_title' => $this->get_default_children_title(),
                'elements_placeholder_selector' => $this->get_default_children_placeholder_selector(),
                'child_container_placeholder_selector' => $this->get_default_children_container_placeholder_selector(),
                'repeater_title_setting' => $this->get_default_repeater_title_setting_key(),
            ],
            'support_nesting' => true,
        ]);
    }

    /**
     * @inheritDoc
     *
     * Each element including its children elements.
     */
    public function get_raw_data($with_html_content = false)
    {
        $elements = [];
        $data = $this->get_data();

        $children = $this->get_children();

        foreach ($children as $child) {
            $child_raw_data = $child->get_raw_data($with_html_content);

            $elements[] = $child_raw_data;
        }

        return [
            'id' => $this->get_id(),
            'elType' => $data['elType'],
            'widgetType' => $data['widgetType'],
            'settings' => $data['settings'],
            'elements' => $elements,
        ];
    }

    /**
     * Print child, helper method to print the child element.
     *
     * @param int $index
     */
    public function print_child($index)
    {
        $children = $this->get_children();
        // \error_log('print_child');
        // \error_log( print_r( $children, true ) );
        // \error_log( print_r( $index, true ) );
        if (! empty($children[$index])) {
            $children[$index]->print_element();
        }
    }

    protected function content_template_single_repeater_item() {}

    public function print_template()
    {
        parent::print_template();
        if ($this->get_initial_config()['support_improved_repeaters'] ?? false) {
?>
            <script type="text/html" id="tmpl-elementor-<?php echo esc_attr($this->get_name()); ?>-content-single">
                <?php $this->content_template_single_repeater_item(); ?>
            </script>
<?php
        }
    }

    /**
     * Displays fallback content for the nesting feature.
     *
     * @param string $type The type of fallback content. Can be `controls` or `text`. Default is `text`.
     * @return void
     * @since 1.0.6
     */
    public function nesting_fallback(string $type = 'text')
    {

        /* translators: 1: Opening anchor tag, 2: Closing anchor tag for the link to the Elementor settings page. */
        $warning = sprintf(
            /* translators: 1: opening url tags 2: closing url tag */
            __('Please, %1$s click here %2$s and enable the Nested Elements experiment in Elementor settings', 'uicore-elements'),
            '<a href="' . esc_attr(Settings::get_settings_tab_url('experiments')) . '" target="_blank">',
            '</a>'
        );

        if ('text' === $type) {

            echo wp_kses_post($warning);
        } else if ('controls' === $type) {
            $this->start_controls_section(
                'section_tabs',
                [
                    'label' => esc_html__('Tabs', 'uicore-elements'),
                    'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
                ]
            );

            $this->add_control(
                'tabs_alert',
                [
                    'type' => \Elementor\Controls_Manager::ALERT,
                    'alert_type' => 'danger',
                    'heading' => esc_html__('Feature Disabled.', 'uicore-elements'),
                    'content' => wp_kses_post($warning)
                ]
            );

            $this->end_controls_section();
        }
    }
}

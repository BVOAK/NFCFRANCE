<?php

namespace UiCoreElements;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;
use UiCoreElements\Utils\Animation_Trait;

defined('ABSPATH') || exit();

class CustomTable extends UiCoreNestedWidget
{

    use Animation_Trait;

    public function get_name()
    {
        return 'uicore-custom-table';
    }
    public function get_title()
    {
        return esc_html__('Custom Table', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-table ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['slide', 'table', 'nested'];
    }
    public function get_styles()
    {
        return ['custom-table'];
    }
    public function get_scripts()
    {
        return [];
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }

    protected function carousel_content_container(int $index)
    {
        return [
            'elType' => 'container',
            'settings' => [
                /* translators: %s: Item index */
                '_title' => sprintf(__('Cel #%s', 'uicore-elements'), $index),
                'content_width' => 'full',
            ],
        ];
    }

    protected function get_default_children_elements()
    {
        return [
            $this->carousel_content_container(1),
            $this->carousel_content_container(2),
            $this->carousel_content_container(3),
        ];
    }

    protected function get_default_repeater_title_setting_key()
    {
        return 'table_cels';
    }

    protected function get_default_children_title()
    {
        /* translators: %d: Item index */
        return esc_html__('Cel #%d', 'uicore-elements');
    }
    protected function get_default_children_placeholder_selector()
    {
        return '.ui-e-table';
    }

    protected function register_controls()
    {
        if (!Plugin::$instance->experiments->is_feature_active('nested-elements')) {
            $this->nesting_fallback('controls');
            return;
        }

        $this->start_controls_section(
            'section_content',
            [
                'label' => __('Items', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );


        //Columns
        $columns = new Repeater();
        $columns->add_responsive_control(
            'col_size',
            [
                'label'   => __('Column Size', 'uicore-elements'),
                'type'    => Controls_Manager::SLIDER,
                'size_units' => ['px', 'fr', 'custom'],
                'default' => [
                    'size' => 300,
                    'unit' => 'px',
                ],
                'range' => [
                    'px' => [
                        'min' => 1,
                        'max' => 1200,
                    ],
                    'fr' => [
                        'min' => 1,
                        'max' => 12,
                    ],
                ],
                'render_type'  => 'template',
                'selectors' => [
                    '{{WRAPPER}}' => '--ui-e-last:{{SIZE}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'columns',
            [
                'type'        => Controls_Manager::REPEATER,
                'fields'      => $columns->get_controls(),
                'title_field' => 'Column',
                'render_type'  => 'template',
                'default'     => [
                    ['col_size' => ['size' => 1, 'unit' => 'fr']],
                    ['col_size' => ['size' => 1, 'unit' => 'fr']],
                    ['col_size' => ['size' => 1, 'unit' => 'fr']],
                ],
            ]
        );



        //Cells
        $repeater = new Repeater();
        $repeater->add_control(
            'item',
            [
                'label'       => __('Title', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
            ]
        );

        $this->add_control(
            'cells',
            [
                'type'        => Control_Nested_Repeater::CONTROL_TYPE,
                'fields'      => $repeater->get_controls(),
                'title_field' => '{{{ item }}}',
                'allow_empty' => false,
                'default'     => [
                    ['item' => __('Cel #1', 'uicore-elements')],
                    ['item' => __('Cel #2', 'uicore-elements')],
                    ['item' => __('Cel #3', 'uicore-elements')],
                ],
            ]
        );

        $this->end_controls_section();


        //style
        $this->start_controls_section(
            'section_table_style',
            [
                'label' => __('Table Style', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->start_controls_tabs(
            'row_tabs'
        );

        $this->start_controls_tab(
            'odd_row',
            [
                'label' => esc_html__('Odd row', 'textdomain'),
            ]
        );

        $this->add_control(
            'odd_row_bg_color',
            [
                'label'     => __('Row background', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-table > .ui-e-odd' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'odd_row_padding',
            [
                'label' => esc_html__('Row Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    "{{WRAPPER}} .ui-e-table > .ui-e-odd" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'odd_row_radius',
            [
                'label' => esc_html__('Row Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    "{{WRAPPER}} .ui-e-table > .ui-e-odd.ui-e-first-row-cel" => 'border-radius: {{TOP}}{{UNIT}} 0 0 {{LEFT}}{{UNIT}};',
                    "{{WRAPPER}} .ui-e-table > .ui-e-odd.ui-e-last-row-cel" => 'border-radius: 0 {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 0;'
                ],
            ]
        );

        $this->end_controls_tab();

        $this->start_controls_tab(
            'even_row',
            [
                'label' => esc_html__('Even row', 'textdomain'),
            ]
        );

        $this->add_control(
            'even_row_bg_color',
            [
                'label'     => __('Row background', 'uicore-elements'),
                'type'      => Controls_Manager::COLOR,
                'selectors' => [
                    '{{WRAPPER}} .ui-e-table > .ui-e-even' => 'background-color: {{VALUE}};',
                ],
            ]
        );

        $this->add_control(
            'even_row_padding',
            [
                'label' => esc_html__('Row Padding', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    "{{WRAPPER}} .ui-e-table > .ui-e-even" => 'padding: {{TOP}}{{UNIT}} {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} {{LEFT}}{{UNIT}};',
                ],
            ]
        );

        $this->add_control(
            'even_row_radius',
            [
                'label' => esc_html__('Row Border Radius', 'uicore-elements'),
                'type' => Controls_Manager::DIMENSIONS,
                'size_units' => ['px', 'em', 'rem', 'custom'],
                'selectors' => [
                    "{{WRAPPER}} .ui-e-table > .ui-e-even.ui-e-first-row-cel" => 'border-radius: {{TOP}}{{UNIT}} 0 0 {{LEFT}}{{UNIT}};',
                    "{{WRAPPER}} .ui-e-table > .ui-e-even.ui-e-last-row-cel" => 'border-radius: 0 {{RIGHT}}{{UNIT}} {{BOTTOM}}{{UNIT}} 0;'
                ],
            ]
        );

        $this->end_controls_tab();

        $this->end_controls_tabs();


        $this->end_controls_section();
    }

    protected function build_grid_css($cols)
    {
        // Build the grid columns css
        if ($cols) {
            $grid_css = '';
            $grid_columns = [];
            $breakpoints = Plugin::$instance->breakpoints->get_active_breakpoints();

            foreach ($cols as $index => $item) {

                // Set desktop column size
                $grid_columns['desktop'][] = isset($item['col_size']['size']) && isset($item['col_size']['unit'])
                    ? $item['col_size']['size'] . $item['col_size']['unit']
                    : '1fr'; // fallback

                foreach ($breakpoints as $breakpoint => $object) {
                    $size_slug = 'col_size_' . $breakpoint;

                    // Register the current column responsive size if exists and the
                    // value is not empty, because empty values breaks the table design
                    $grid_columns[$breakpoint][] = isset($item[$size_slug]['size']) && isset($item[$size_slug]['unit'])
                        ? $item[$size_slug]['size'] . $item[$size_slug]['unit']
                        : $grid_columns['desktop'][$index];
                }
            }

            // Generate the css variables
            foreach ($grid_columns as $device => $values) {
                $breakpoint = $device === 'desktop' ? '' : '-' . $device;
                $grid_css .= '--ui-e-table-cols' . $breakpoint . ':' . implode(' ', $values) . ';';
            }

            return $grid_css;
        }
    }

    public function render()
    {
        if (!Plugin::$instance->experiments->is_feature_active('nested-elements')) {
            $this->nesting_fallback();
            return;
        }

        $cells = $this->get_settings_for_display('cells');
        $cols  = $this->get_settings_for_display('columns');
        $grid_css = $this->build_grid_css($cols);

        // Add it to the widget wrapper
        $this->add_render_attribute('_wrapper', ['style' => $grid_css]);

?>
        <div class="ui-e-table">
            <?php

            $columns = count($cols);

            foreach ($cells as $index => $item) {
                $row_class = floor($index / $columns) % 2 !== 0
                    ? 'ui-e-even'
                    : 'ui-e-odd';

                // TODO: in the future experiment a nth-child approach so we don't need this first/last classes
                // Add classes to the first and last items of each row
                if ($index % $columns === 0) {
                    $row_class .= ' ui-e-first-row-cel';
                } else if (($index + 1) % $columns === 0) {
                    $row_class .= ' ui-e-last-row-cel';
                }

                $this->print_child($index, ['class' => $row_class]);
            }
            ?>
        </div>
    <?php
    }

    public function print_child($index, array $atts = [])
    {
        $children = $this->get_children();

        if (! empty($children[$index])) {

            // Add custom attributes to each child container.
            if (!empty($atts)) {
                //$child_settings = $children[ $index ]->get_settings_for_display();
                foreach ($atts as $key => $value) {
                    $children[$index]->add_render_attribute('_wrapper', $key, $value);
                }
            }

            $children[$index]->print_element();
        }
    }

    protected function get_initial_config(): array
    {
        if (Plugin::$instance->experiments->is_feature_active('e_nested_atomic_repeaters')) {
            return array_merge(parent::get_initial_config(), [
                'support_improved_repeaters' => true,
                'node' => '.ui-e-table'
            ]);
        }

        return parent::get_initial_config();
    }


    protected function content_template()
    {
    ?>
        <#
            const cols=settings.columns;
            const cols_qty='--ui-e-cols-qty:' + settings.columns.length;

            if ( cols ) {
            let gridCss='' ;
            const gridColumns={};
            const breakpoints=elementorFrontend.config.responsive.activeBreakpoints;

            _.each( cols, function( item, index ) {

            if ( ! gridColumns.desktop ) {
            gridColumns.desktop=[];
            }

            gridColumns.desktop.push( item.col_size.size + item.col_size.unit );

            _.each( breakpoints, function( object, breakpoint ) {
            const sizeSlug='col_size_' + breakpoint;

            if ( item[sizeSlug] && item[sizeSlug].size ) {
            if ( ! gridColumns[breakpoint] ) {
            gridColumns[breakpoint]=[];
            }
            gridColumns[breakpoint].push( item[sizeSlug].size + item[sizeSlug].unit );
            } else {
            if ( ! gridColumns[breakpoint] ) {
            gridColumns[breakpoint]=[];
            }
            gridColumns[breakpoint].push( item.col_size.size + item.col_size.unit );
            }
            });
            });

            _.each( gridColumns, function( values, device ) {
            const breakpoint=device==='desktop' ? '' : '-' + device;
            gridCss +='--ui-e-table-cols' + breakpoint + ':' + values.join(' ') + ' ;';
            });

            view.addRenderAttribute('table', 'style' , [gridCss, cols_qty].join(' '));
            }
        #>
        <div class="ui-e-table" {{{ view.getRenderAttributeString('table') }}}>

        </div>
        <?php
    }
}
\Elementor\Plugin::instance()->widgets_manager->register(new CustomTable());

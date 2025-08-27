<?php

namespace UiCoreElements;

use Elementor\Plugin;
use Elementor\Controls_Manager;
use Elementor\Repeater;
use Elementor\Modules\NestedElements\Controls\Control_Nested_Repeater;
use UiCoreElements\Utils\Carousel_Trait;
use UiCoreElements\Utils\Animation_Trait;
use UiCoreElements\Utils\Item_Style_Component;

defined('ABSPATH') || exit();

/**
 * Custom Carousel
 *
 * @author Lucas Marini Falbo <lucas@uicore.co>
 * @since 1.0.7
 */

class CustomCarousel extends UiCoreNestedWidget
{

    use Carousel_Trait;
    use Animation_Trait;
    use Item_Style_Component;

    public function get_name()
    {
        return 'uicore-custom-carousel';
    }
    public function get_title()
    {
        return esc_html__('Custom Carousel', 'uicore-elements');
    }
    public function get_icon()
    {
        return 'eicon-carousel-loop ui-e-widget';
    }
    public function get_categories()
    {
        return ['uicore'];
    }
    public function get_keywords()
    {
        return ['slide', 'carousel', 'nested'];
    }
    public function get_styles()
    {
        $styles = [
            'custom-carousel',
            'carousel'
        ];
        return $styles;
    }
    public function get_scripts()
    {
        return $this->TRAIT_get_carousel_scripts(false);
    }
    public function has_widget_inner_wrapper(): bool
    {
        // TODO: remove after Optmized Markup experiment is merged to the core
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active('e_optimized_markup');
    }

    // Nested required functions
    protected function carousel_content_container(int $index)
    {
        return [
            'elType' => 'container',
            'settings' => [
                /* translators: %s: Item number */
                '_title' => sprintf(esc_html__('Item #%s', 'uicore-elements'), $index),
                'content_width' => 'full',
                'flex_justify_content' => 'center',
                'flex_align_items' => 'center',
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
        return 'carousel_item_title';
    }
    protected function get_default_children_title()
    {
        /* translators: %d: Item number */
        return esc_html__('Item #%d', 'uicore-elements');
    }
    protected function get_default_children_placeholder_selector()
    {
        return '.swiper-wrapper';
    }

    /**
     * We've set a bool variable to this function because Custom Slider extends this widget and requires one extra control.
     *
     * @param bool $is_slider - If the widget is a slider, it will enable the slider height control.
     */
    protected function register_controls(bool $is_slider = false)
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

        $repeater = new Repeater();

        $repeater->add_control(
            'carousel_item_title',
            [
                'label'       => __('Title', 'uicore-elements'),
                'type'        => Controls_Manager::TEXT,
                'render_type' => 'template',
                'dynamic'     => [
                    'active' => true,
                ],
            ]
        );

        $this->add_control(
            'carousel_items',
            [
                'type'        => Control_Nested_Repeater::CONTROL_TYPE,
                'fields'      => $repeater->get_controls(),
                'default'     => [
                    ['carousel_item_title' => __('Item #1', 'uicore-elements')],
                    ['carousel_item_title' => __('Item #2', 'uicore-elements')],
                    ['carousel_item_title' => __('Item #3', 'uicore-elements')],
                ]
            ]
        );

        $this->end_controls_section();

        // Additional Carousel Controls
        $this->start_controls_section(
            'section_additional_settings',
            [
                'label' => __('Additional Settings', 'uicore-elements'),
                'tab'   => Controls_Manager::TAB_CONTENT,
            ]
        );

        $this->TRAIT_register_carousel_additional_controls($is_slider); // Carousel Additionals

        $this->end_controls_section();

        $this->start_controls_section(
            'section_carousel_settings',
            [
                'label' => __('Carousel Settings', 'uicore-elements'),
            ]
        );

        $this->TRAIT_register_carousel_settings_controls(); // Carousel settings

        $this->end_controls_section();

        $this->TRAIT_register_navigation_controls(); // Navigation settings

        $this->start_controls_section(
            'section_style_review_items',
            [
                'label'     => esc_html__('Items', 'uicore-elements'),
                'tab'       => Controls_Manager::TAB_STYLE,
            ]
        );

        $this->TRAIT_register_all_item_style_controls();

        $this->end_controls_section();

        $this->TRAIT_register_navigation_style_controls(); // Carousel Navigation Styles

        // Decrease default item padding
        $this->update_control('item_padding', [
            'default' => [
                'top' => 25,
                'right' => 25,
                'bottom' => 25,
                'left' => 25,
                'unit' => 'px',
                'isLinked' => true,
            ],
        ]);

        // Adds description to loop
        $this->update_control('loop', [
            'description' => esc_html__('Loop preview is disabled here on the editor, due to compatibility issues. But you can test on the front-end, since it works outside the editor.', 'uicore-elements'),
        ]);
    }

    public function render()
    {
        if (Plugin::$instance->experiments->is_feature_active('nested-elements') == false) {
            $this->nesting_fallback();
            return;
        }

        $items  = $this->get_settings_for_display('carousel_items');
        $carousel_items = '';

        $total_slides = count($items);
        $should_duplicate = $this->TRAIT_should_duplicate_slides($total_slides);
        $duplicated_slides = [];

        foreach ($items as $index => $item) {
            ob_start();
            $this->render_item($index);
            $current_slide = ob_get_clean();

            if ($should_duplicate) {
                $duplicated_slides[$index] = $current_slide;
            }

            $carousel_items .= $current_slide;
        }

        // Most recent swiper versions requires, if loop, at least one extra slide compared to visible slides
        if ($should_duplicate) {
            $diff = $this->TRAIT_get_duplication_diff($total_slides);
            for ($i = 0; $i <= $diff; $i++) {
                $carousel_items .= $duplicated_slides[$i];
            }
        }

?>
        <div class="ui-e-carousel swiper">

            <div class='swiper-wrapper'>
                <?php echo $carousel_items; //phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
                ?>
            </div>

        </div>

        <?php $this->TRAIT_render_carousel_navigations(); ?>
    <?php
    }

    public function render_item($index)
    {
    ?>
        <div class="ui-e-wrp swiper-slide" data-hash="<?php echo esc_html($index); ?>">
            <?php $this->print_child($index); ?>
        </div>
    <?php
    }

    public function print_child($index)
    {
        $children  = $this->get_children();
        $child_ids = [];

        if (empty($children) || ! isset($children[$index])) {
            return;
        }

        foreach ($children as $child) {
            $child_ids[] = $child->get_id();
        }

        add_filter('elementor/frontend/container/should_render', function ($should_render, $container) use ($child_ids) {
            return $this->add_child_attributes($should_render, $container, $child_ids);
        }, 10, 3);

        $children[$index]->print_element();

        remove_filter('elementor/frontend/container/should_render', [$this, 'add_child_attributes']);
    }

    /**
     * Adds attributes to child elements containers.
     */
    protected function add_child_attributes($should_render, $container, $child_ids, $classnames = ['ui-e-item'])
    {

        // custom cases may be added here

        if (in_array($container->get_id(), $child_ids)) {
            $container->add_render_attribute('_wrapper', [
                'class' => $classnames,
            ]);
        }

        return $should_render;
    }

    protected function get_initial_config(): array
    {
        if (Plugin::$instance->experiments->is_feature_active('e_nested_atomic_repeaters')) {
            return array_merge(parent::get_initial_config(), [
                'support_improved_repeaters' => true,
                'node' => 'button',
            ]);
        }

        return parent::get_initial_config();
    }

    protected function content_template()
    {

        if (Plugin::$instance->experiments->is_feature_active('nested-elements') == false) {
            return;
        }

    ?>
        <#
            var carouselItems=settings.carousel_items,
            hideNavigation=settings.animation_style.includes('marquee'),
            navigationDots=settings.navigation.includes('dots') && !hideNavigation,
            navigationArrows=settings.navigation.includes('arrows') && !hideNavigation,
            navigationFraction=settings.navigation.includes('fraction') && !hideNavigation;

            var prev=elementor.helpers.renderIcon( view, settings.previous_arrow, { 'aria-hidden' : true }, 'i' , 'object' ),
            next=elementor.helpers.renderIcon( view, settings.next_arrow, { 'aria-hidden' : true }, 'i' , 'object' );
            #>

            <div class="ui-e-carousel swiper {{ elementorFrontend.config.swiperClass }}">
                <div class='swiper-wrapper'> </div>
            </div>

            <# if ( navigationDots ) { #>
                <div class="swiper-pagination ui-e-dots ui-e-carousel-dots"></div>
                <# } #>
                    <# if ( navigationArrows ) { #>
                        <div class="ui-e-button ui-e-carousel-button ui-e-previous" role="button" aria-label="Previous slide">
                            {{{ prev.value }}}
                        </div>
                        <div class="ui-e-button ui-e-carousel-button ui-e-next" role="button" aria-label="Next slide">
                            {{{ next.value }}}
                        </div>
                        <# } #>
                            <# if ( navigationFraction ) { #>
                                <div class="ui-e-fraction ui-e-carousel-fraction">
                                    <span class="ui-e-current"></span>
                                    /
                                    <span class="ui-e-total"></span>
                                </div>
                                <# } #>
                            <?php
                        }
                    }
                    \Elementor\Plugin::instance()->widgets_manager->register(new CustomCarousel());

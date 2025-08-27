<?php

namespace ElementPack\Modules\AcfSlider\Widgets;

use ElementPack\Utils;
use Elementor\Icons_Manager;
use ElementPack\Base\Module_Base;
use ElementPack\Includes\ACF_Global;
use ElementPack\Traits\Global_Widget_Controls;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class ACF Slider
 */
class Acf_Slider extends Module_Base {
	use Global_Widget_Controls;

	public function get_name() {
		return 'bdt-acf-slider';
	}

	public function get_title() {
		return BDTEP . esc_html__('ACF Slider', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-acf-slider';
	}

	public function get_categories() {
		return ['element-pack'];
	}

	public function get_keywords() {
		return ['acf-slider', 'acf', 'slider', 'hero'];
	}

	public function get_style_depends() {
		if ($this->ep_is_edit_mode()) {
			return ['swiper', 'ep-styles'];
		} else {
			return ['swiper', 'ep-slider', 'ep-font'];
		}
	}

	public function get_script_depends() {
		if ($this->ep_is_edit_mode()) {
			return ['swiper', 'imagesloaded', 'ep-scripts'];
		} else {
			return ['swiper', 'imagesloaded', 'ep-slider'];
		}
	}

	public function on_import($element) {
		if (!get_post_type_object($element['settings']['posts_post_type'])) {
			$element['settings']['posts_post_type'] = 'services';
		}

		return $element;
	}

	public function get_custom_help_url() {
		return 'https://youtu.be/PE411IBFw0I';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}
	
	protected function register_controls() {
		$this->start_controls_section(
			'section_content_sliders',
			[
				'label' => esc_html__('Sliders', 'bdthemes-element-pack'),
			]
		);			
		$this->add_control(
            'repeater_field',
            [
                'label' => __('Repeater Field', 'bdthemes-element-pack'),
                'dynamic' => ['active' => false],
                'type'    => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => __('Type and select the repeater field...', 'bdthemes-element-pack'),
				// translators: %1s is the name of the supported field type, e.g., "Repeater"
                'description' => sprintf(__('Supported field type: <strong>%1s</strong>', 'bdthemes-element-pack'), 'Repeater'),
                'query_args'  => [
					'query'        => 'acf',
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
				'placeholder' => __('Type repeater sub field for slider title', 'bdthemes-element-pack'),
				// translators: %1s is the name of the supported fields type, e.g., "Text", "Textarea", "WYSIWYG"
				'description' => sprintf(__('Supported field type: <strong>%1s</strong>, <strong>%2s</strong>, <strong>%3s</strong>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
				'query_args'  => [
					'query'        => 'acf',
					'field_type'   => ['text', 'textarea', 'wysiwyg'],
				],
			]
		);
		$this->add_control(
			'image',
			[
				'label' => __('Image', 'bdthemes-element-pack'),
				'dynamic' => ['active' => false],
				'type'        => Dynamic_Select::TYPE,
				'label_block' => true,
				'placeholder' => __('Type repeater sub field for slider image', 'bdthemes-element-pack'),
				// translators: %1s is the name of the supported field type, e.g., "Image"
				'description' => sprintf(__('Supported field type: <strong>%1s</strong>', 'bdthemes-element-pack'), 'Image'),
				'query_args'  => [
					'query'        => 'acf',
					'field_type'   => ['image'],
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
                'placeholder' => __('Type repeater sub field for slider content', 'bdthemes-element-pack'),
				// translators: %1s is the name of the supported field type, e.g., "Text", "Textarea", "WYSIWYG"	
				'description' => sprintf(__('Supported field type: <strong>%1$1s</strong>, <strong>%2s</strong>, <strong>%3s</strong>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
                'query_args'  => [
					'query'        => 'acf',
                    'field_type'   => ['text', 'textarea', 'wysiwyg'],
                ],
			]
		);		
		$this->add_control(
			'link',
			[
				'label' => __('Link', 'bdthemes-element-pack'),
				'dynamic' => ['active' => false],
				'type'        => Dynamic_Select::TYPE,
				'label_block' => true,
				'placeholder' => __('Type repeater sub field for slider link', 'bdthemes-element-pack'),
				// translators: %1s is the name of the supported field type, e.g., "URL"
				'description' => sprintf(__('Supported field type: <strong>%1s</strong>', 'bdthemes-element-pack'), 'URL'),
				'query_args'  => [
					'query'        => 'acf',
					'field_type'   => ['url'],
				],
			]
		);
		$this->end_controls_section();

		$this->register_slider_controls(); // Global controls from trait
	}

	public function render() {
		$settings  = $this->get_settings_for_display();
		$repeater_field = get_field_object( $settings['repeater_field'] );

		if (empty($settings['repeater_field'] && $repeater_field)) {
			return;
		}

		$this->render_slider_loop_header(); // Global function from trait

		?>
		<div class="swiper-wrapper">
		<?php
		$counter = 1;
		$acf_helper = new ACF_Global();
		$field_values = $acf_helper->get_acf_field_value( $settings['repeater_field'], $repeater_field['parent'] );
		
		if (empty($field_values)) {
			return;
		}

		$title = $settings['title'];
		$image = $settings['image'];
		$content = $settings['content'];
		$link = $settings['link'];

		foreach ($field_values as $index => $value) :

			$image_src = !empty($value[$image]) ? wp_get_attachment_image_src($value[$image]['id'], 'full') : '';
			$image_src =  $image_src ? $image_src[0] : '';

			$this->add_render_attribute(
				[
					'slide-item' => [
						'class' => [
							'bdt-slide-item',
							'swiper-slide',
							'bdt-slide-effect-' . $settings['effect'] ? $settings['effect'] : 'left',
						],
					]
				],
				'',
				'',
				true
			);
			
			$link_key = 'link_' . $index;
			$this->add_render_attribute(
				[
					$link_key => [
						'href' => isset($value[$link]) ? $value[$link] : '',
						'class' => [
							'bdt-slide-link',
							$settings['button_hover_animation'] ? 'elementor-animation-' . $settings['button_hover_animation'] : '',
						],
					]
				],
				'',
				'',
				true
			);
			
			if (!isset($settings['icon']) && !Icons_Manager::is_migration_allowed()) {
				// add old default
				$settings['icon'] = 'fas fa-arrow-right';
			}

			$migrated  = isset($settings['__fa4_migrated']['slider_icon']);
			$is_new    = empty($settings['icon']) && Icons_Manager::is_migration_allowed();

			$this->add_render_attribute('bdt-slide-title', 'class', ['bdt-slide-title bdt-clearfix'], true);

			?>
			<div <?php $this->print_render_attribute_string('slide-item'); ?>>

			<?php if ($image_src) : ?>
				<div class="bdt-slider-image-wrapper">
					<?php print(wp_get_attachment_image(
						$value[$image]['id'],
						'full',
						false,
						[
							'class' => 'bdt-cover',
							'alt' => isset($value[$title]) ? wp_kses_post($value[$title]) : '',
							'data-bdt-cover' => true
						]
					)); ?>
				</div>
			<?php endif; ?>

				<div class="bdt-slide-desc bdt-position-large bdt-position-<?php echo esc_attr($settings['origin']); ?> bdt-position-z-index">
					
					<?php if (!empty($value[$title]) && ($settings['show_title'])) : ?>
						<<?php echo esc_attr(Utils::get_valid_html_tag($settings['title_tags'])); ?>
						<?php $this->print_render_attribute_string('bdt-slide-title'); ?>>
							<?php echo wp_kses_post($value[$title]); ?>
						</<?php echo esc_attr(Utils::get_valid_html_tag($settings['title_tags'])); ?>>
					<?php endif; ?>

					<?php if (!empty($value[$content])) : ?>
						<div class="bdt-slide-text"><?php $this->print_text_editor($value[$content]); ?></div>
					<?php endif; ?>

					<?php if ((!empty($value[$link])) && ($settings['show_button'])) : ?>
						<div class="bdt-slide-link-wrapper">
							<a <?php $this->print_render_attribute_string($link_key); ?>>

								<?php echo esc_html($settings['button_text']); ?>

								<?php if ($settings['slider_icon']['value']) : ?>
									<span class="bdt-button-icon-align-<?php echo esc_attr($settings['icon_align']); ?>">

										<?php if ($is_new || $migrated) :
											Icons_Manager::render_icon($settings['slider_icon'], ['aria-hidden' => 'true', 'class' => 'fa-fw']);
										else : ?>
											<i class="<?php echo esc_attr($settings['icon']); ?>" aria-hidden="true"></i>
										<?php endif; ?>

									</span>
								<?php endif; ?>
							</a>
						</div>
					<?php endif; ?>
				</div>

			</div>
		<?php $counter++; endforeach; ?>
		</div>
		<?php $this->render_slider_loop_footer(); // Global function from trait
	}
}

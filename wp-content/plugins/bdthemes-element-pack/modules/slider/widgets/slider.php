<?php

namespace ElementPack\Modules\Slider\Widgets;

use ElementPack\Utils;
use Elementor\Repeater;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use ElementPack\Base\Module_Base;
use ElementPack\Element_Pack_Loader;
use ElementPack\Traits\Global_Widget_Controls;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Class Slider
 */
class Slider extends Module_Base {
	use Global_Widget_Controls;

	public function get_name() {
		return 'bdt-slider';
	}

	public function get_title() {
		return BDTEP . esc_html__('Slider', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-slider';
	}

	public function get_categories() {
		return ['element-pack'];
	}

	public function get_keywords() {
		return ['slider', 'hero'];
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
		return 'https://youtu.be/SI4K4zuNOoE';
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return true;
	}
	
	protected function register_controls() {
		$this->start_controls_section(
			'section_content_sliders',
			[
				'label' => esc_html__('Sliders', 'bdthemes-element-pack'),
			]
		);

		$repeater = new Repeater();

		$repeater->add_control(
			'source',
			[
				'label'   => esc_html__('Select Source', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::SELECT,
				'default' => 'custom',
				'options' => [
					'custom'        => esc_html__('Custom Content', 'bdthemes-element-pack'),
					"elementor"     => esc_html__('Elementor Template', 'bdthemes-element-pack'),
				],
			]
		);
		$repeater->add_control(
			'template_id',
			[
				'label'       => __('Select Template', 'bdthemes-element-pack'),
				'type'        => Dynamic_Select::TYPE,
				'label_block' => true,
				'placeholder' => __('Type and select template', 'bdthemes-element-pack'),
				'query_args'  => [
					'query'        => 'elementor_template',
				],
				'condition'   => ['source' => 'elementor'],
			]
		);

		$repeater->add_control(
			'tab_title',
			[
				'label'       => esc_html__('Title', 'bdthemes-element-pack'),
				'type'        => Controls_Manager::TEXT,
				'dynamic'     => ['active' => true],
				'default'     => esc_html__('Slide Title', 'bdthemes-element-pack'),
				'label_block' => true,
				'condition' => ['source' => 'custom'],
			]
		);

		$repeater->add_control(
			'tab_image',
			[
				'label'   => esc_html__('Image', 'bdthemes-element-pack'),
				'type'    => Controls_Manager::MEDIA,
				'dynamic' => ['active' => true],
				'condition' => ['source' => 'custom'],
			]
		);

		$repeater->add_control(
			'tab_content',
			[
				'label'      => esc_html__('Content', 'bdthemes-element-pack'),
				'type'       => Controls_Manager::WYSIWYG,
				'dynamic'    => ['active' => true],
				'default'    => esc_html__('Slide Content', 'bdthemes-element-pack'),
				'show_label' => false,
				'condition' => ['source' => 'custom'],
			]
		);

		$repeater->add_control(
			'tab_link',
			[
				'label'       => esc_html__('Link', 'bdthemes-element-pack'),
				'type'        => Controls_Manager::URL,
				'dynamic'     => ['active' => true],
				'placeholder' => 'http://your-link.com',
				'default'     => [
					'url' => '#',
				],
				'condition' => ['source' => 'custom'],
			]
		);

		$this->add_control(
			'tabs',
			[
				'label' => esc_html__('Slider Items', 'bdthemes-element-pack'),
				'type' => Controls_Manager::REPEATER,
				'fields' => $repeater->get_controls(),
				'default' => [
					[
						'tab_title'   => esc_html__('Slide #1', 'bdthemes-element-pack'),
						'tab_content' => esc_html__('I am item content. Click edit button to change this text.', 'bdthemes-element-pack'),
					],
					[
						'tab_title'   => esc_html__('Slide #2', 'bdthemes-element-pack'),
						'tab_content' => esc_html__('I am item content. Click edit button to change this text.', 'bdthemes-element-pack'),
					],
					[
						'tab_title'   => esc_html__('Slide #3', 'bdthemes-element-pack'),
						'tab_content' => esc_html__('I am item content. Click edit button to change this text.', 'bdthemes-element-pack'),
					],
					[
						'tab_title'   => esc_html__('Slide #4', 'bdthemes-element-pack'),
						'tab_content' => esc_html__('I am item content. Click edit button to change this text.', 'bdthemes-element-pack'),
					],
				],
				'title_field' => '{{{ tab_title }}}',
			]
		);

		$this->end_controls_section();

		$this->register_slider_controls(); // Global controls from trait
	}

	public function render() {
		$settings  = $this->get_settings_for_display();

		$this->render_slider_loop_header(); // Global function from trait

		?>
		<div class="swiper-wrapper">
		<?php $counter = 1;

		foreach ($settings['tabs'] as $index => $item) :
			$image_src = isset($item['tab_image']['id']) ? wp_get_attachment_image_src($item['tab_image']['id'], 'full') : '';
			$image     =  $image_src ? $image_src[0] : '';

			$this->add_render_attribute('slide-item', 'class', 'bdt-slide-item swiper-slide bdt-slide-effect-' . $settings['effect'], true);
			
			$link_key = 'link_' . $index;
			$this->add_render_attribute(
				[
					$link_key => [
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
			
			if ('custom' == $item['source']) {
				$this->add_link_attributes($link_key, $item['tab_link']);
			}

			if (!isset($settings['icon']) && !Icons_Manager::is_migration_allowed()) {
				// add old default
				$settings['icon'] = 'fas fa-arrow-right';
			}

			$migrated  = isset($settings['__fa4_migrated']['slider_icon']);
			$is_new    = empty($settings['icon']) && Icons_Manager::is_migration_allowed();

			$this->add_render_attribute('bdt-slide-title', 'class', ['bdt-slide-title bdt-clearfix'], true);

			?>
			<div <?php $this->print_render_attribute_string('slide-item'); ?>>

				<?php
				if ('custom' == $item['source']) {
				?>

					<?php if ($image) : ?>
						<div class="bdt-slider-image-wrapper">
							<?php
							print(wp_get_attachment_image(
								$item['tab_image']['id'],
								'full',
								false,
								[
									'class' => 'bdt-cover',
									'alt' => wp_kses_post($item['tab_title']),
									'data-bdt-cover' => true
								]
							));
							?>
						</div>
					<?php endif; ?>

					<div class="bdt-slide-desc bdt-position-large bdt-position-<?php echo esc_attr($settings['origin']); ?> bdt-position-z-index">

						<?php if (('' !== $item['tab_title']) && ($settings['show_title'])) : ?>
							<<?php echo esc_attr(Utils::get_valid_html_tag($settings['title_tags'])); ?> <?php $this->print_render_attribute_string('bdt-slide-title'); ?>>
								<?php echo wp_kses_post($item['tab_title']); ?>
							</<?php echo esc_attr(Utils::get_valid_html_tag($settings['title_tags'])); ?>>
						<?php endif; ?>

						<?php if ('' !== $item['tab_content']) : ?>
							<div class="bdt-slide-text"><?php echo $this->parse_text_editor($item['tab_content']); ?></div>
						<?php endif; ?>

						<?php if ((!empty($item['tab_link']['url'])) && ($settings['show_button'])) : ?>
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

				<?php

				} elseif ("elementor" == $item['source']) {
					echo Element_Pack_Loader::elementor()->frontend->get_builder_content_for_display($item['template_id']);
					echo element_pack_template_edit_link($item['template_id']);
				}
				?>

			</div>
			<?php $counter++; endforeach; ?>
		</div>
		<?php $this->render_slider_loop_footer(); // Global function from trait
	}
}

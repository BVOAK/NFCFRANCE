<?php

namespace ElementPack\Modules\AcfGallery\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Controls_Manager;
use Elementor\Group_Control_Image_Size;
use Elementor\Icons_Manager;
use ElementPack\Modules\AcfGallery\Skins;
use ElementPack\Traits\Global_Mask_Controls;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;
use ElementPack\Traits\Global_Widget_Controls;

if (!defined('ABSPATH')) exit; // Exit if accessed directly

class Acf_Gallery extends Module_Base {

	use Global_Mask_Controls;
	use Global_Widget_Controls;

	public $lightbox_slide_index;

	public function get_name() {
		return 'bdt-acf-gallery';
	}

	public function get_title() {
		return BDTEP . esc_html__('ACF Gallery', 'bdthemes-element-pack');
	}

	public function get_icon() {
		return 'bdt-wi-acf-gallery';
	}

	public function get_categories() {
		return ['element-pack'];
	}

	public function get_keywords() {
		return ['acf-gallery', 'acf', 'gallery', 'photo', 'image'];
	}

	public function get_style_depends() {
		if ($this->ep_is_edit_mode()) {
			return ['ep-styles'];
		} else {
			return ['ep-advanced-image-gallery', 'ep-font'];
		}
	}

	public function get_script_depends() {
		if ($this->ep_is_edit_mode()) {
			return ['imagesloaded', 'ep-justified-gallery', 'tilt', 'ep-scripts'];
		} else {
			return ['imagesloaded', 'ep-justified-gallery', 'tilt', 'ep-advanced-image-gallery'];
		}
	}

	public function get_custom_help_url() {
		return '';
	}

	public function register_skins() {
		$this->add_skin(new Skins\Skin_Hidden($this));
		$this->add_skin(new Skins\Skin_Carousel($this));
	}

	public function has_widget_inner_wrapper(): bool {
        return ! \Elementor\Plugin::$instance->experiments->is_feature_active( 'e_optimized_markup' );
    }
	protected function is_dynamic_content(): bool {
		return false;
	}

	protected function register_controls() {
		$this->start_controls_section(
			'section_gallery',
			[
				'label' => esc_html__('Image Gallery', 'bdthemes-element-pack'),
			]
		);

		$this->add_control(
            'field',
            [
                'label' => __('Gallery Field', 'bdthemes-element-pack'),
                'dynamic' => ['active' => false],
                'type'    => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => __('Type and select the gallery field...', 'bdthemes-element-pack'),
				/* translators: %1$s: Field type name */
				'description' => sprintf(__('Supported field type: <b>%1$s</b>', 'bdthemes-element-pack'), 'Gallery'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['gallery'],
                ],
            ]
        );

		$this->add_group_control(
			Group_Control_Image_Size::get_type(),
			[
				'name'      => 'thumbnail',
				'condition' => ['_skin!' => 'bdt-hidden'],
			]
		);

		$this->add_control(
			'image_mask_popover',
			[
				'label'        => esc_html__('Image Mask', 'bdthemes-element-pack'),
				'type'         => Controls_Manager::POPOVER_TOGGLE,
				'render_type'  => 'template',
				'return_value' => 'yes',
				'condition' => ['_skin!' => 'bdt-hidden'],
			]
		);

		// Global Image Mask Controls 
		$this->register_image_mask_controls();

		$this->end_controls_section();

		// Global Controls from trait
		$this->register_aig_controls();
	}

		private function render_gallery_image($settings, $images) {

			$image_mask = $settings['image_mask_popover'] == 'yes' ? ' bdt-image-mask' : '';

			$this->add_render_attribute('advanced-image-gallery-item', 'class', ['bdt-ep-advanced-image-gallery-item', 'bdt-transition-toggle']);

			$this->add_render_attribute('advanced-image-gallery-inner', 'class', 'bdt-ep-advanced-image-gallery-inner' . $image_mask);

			if ($settings['tilt_show']) {
				$this->add_render_attribute('advanced-image-gallery-inner', 'data-tilt', '');
				if ($settings['tilt_scale']['size']) {
					$this->add_render_attribute('advanced-image-gallery-inner', 'data-tilt-scale', $settings['tilt_scale']['size']);
				}
			}

			foreach ($images as $index => $image) : ?>

				<div <?php $this->print_render_attribute_string('advanced-image-gallery-item'); ?>>
					<div <?php $this->print_render_attribute_string('advanced-image-gallery-inner'); ?>>
						<?php
						$this->render_thumbnail($image);

						if ($settings['show_lightbox'] or ($settings['show_caption'] and 'yes' !== $settings['caption_all_time'])) :
							$this->render_overlay($image);
						endif;

						?>
					</div>
					<?php if ($settings['show_caption'] and 'yes' == $settings['caption_all_time']) : ?>
						<?php $this->render_caption($image); ?>
					<?php endif; ?>
				</div>

			<?php endforeach;
		}

		public function render_footer() {
			?>
		</div>
	<?php
		}

		protected function render() {
			$settings = $this->get_settings_for_display();
			$id       = $this->get_id();

			// ACF - Gallery fields.
			$gallery_field = get_field( $settings['field'] );
			if (empty($gallery_field)) {
				return;
			}

			$this->render_aig_header(); // Global header from trait
			$this->render_gallery_image($settings, $gallery_field);
			$this->render_footer();
		}

		public function render_thumbnail($image) {
			$settings = $this->get_settings_for_display();
			echo '<div class="bdt-ep-advanced-image-gallery-thumbnail bdt-transition-toggle">';
			print(wp_get_attachment_image(
				$image['id'],
				$settings['thumbnail_size'],
				false,
				[
					'class' => 'jgalleryImage',
					'alt'   => esc_attr($image['alt'])
				]
			));
			echo '</div>';
		}

		public function render_caption($text) {
			$image_caption = get_post($text['id']);
			$settings      = $this->get_settings_for_display();

			$this->add_render_attribute('caption', 'class', 'bdt-ep-advanced-image-gallery-item-caption bdt-display-inline-block', true);

			if ($settings['caption_all_time']) {
				$this->add_render_attribute('caption', 'class', ('' != $settings['caption_position']) ? 'bdt-position-' . $settings['caption_position'] : 'bdt-caption-position-default');
			}

		if (!empty($image_caption->post_excerpt)) : ?>
			<div>
				<div <?php $this->print_render_attribute_string('caption'); ?>>
					<?php echo wp_kses_post($image_caption->post_excerpt); ?>
				</div>
			</div>
		<?php endif;
		}

		public function render_overlay($content) {
			$settings                  = $this->get_settings_for_display();
			$image_caption = get_post($content['id']);

			$this->add_render_attribute('overlay-settings', 'class', ['bdt-position-cover', 'bdt-overlay', 'bdt-overlay-default'], true);

			if ($settings['overlay_animation']) {
				$this->add_render_attribute('overlay-settings', 'class', 'bdt-transition-' . $settings['overlay_animation']);
			}
			$animation = $settings['button_hover_animation'] ? 'elementor-animation-' . $settings['button_hover_animation'] : '';

		?>
		<div <?php $this->print_render_attribute_string('overlay-settings'); ?>>
			<div class="bdt-ep-advanced-image-gallery-content">
				<div class="bdt-ep-advanced-image-gallery-content-inner">

					<?php $this->add_render_attribute(
						[
							'overlay-lightbox-attr' => [
								'class' => [
									('image' == $settings['link_type']) ? 'bdt-position-cover' : 'bdt-ep-advanced-image-gallery-item-link',
									'elementor-clickable',
									'icon-type-' . $settings['link_type'],
									$animation,
								],
								'data-elementor-open-lightbox' => 'no',
								'data-caption'                 => wp_kses_post($image_caption->post_excerpt),
							],
						],
						'',
						'',
						true
					);

					$image_url = wp_get_attachment_image_src($content['id'], 'full');

					if (!$image_url) {
						$this->add_render_attribute('overlay-lightbox-attr', 'href', $content['url'], true);
					} else {
						$this->add_render_attribute('overlay-lightbox-attr', 'data-href', $image_url[0], true);
					}

					?>
					<?php if ('yes' == $settings['show_lightbox']) : ?>
						<div class="bdt-flex-inline bdt-ep-advanced-image-gallery-item-link-wrapper">
							<a <?php $this->print_render_attribute_string('overlay-lightbox-attr'); ?>>
								<?php if ('icon' == $settings['link_type']) : ?>

									<?php if ($settings['ep_gallery_link_icon']['value']) : ?>
										<span><?php Icons_Manager::render_icon($settings['ep_gallery_link_icon'], ['aria-hidden' => 'true', 'class' => 'fa-fw']); ?></span>
									<?php else : ?>
										<span>
											<i class="ep-icon-plus-2"></i>
										</span>
									<?php endif; ?>

								<?php elseif ( 'text' == $settings['link_type'] && $settings['ep_gallery_link_text']) : ?>
									<span class="bdt-text"><?php esc_html_e( $settings['ep_gallery_link_text'], 'bdthemes-element-pack' ); ?></span>
								<?php endif; ?>
							</a>
						</div>
					<?php endif; ?>

					<?php if ($settings['show_caption'] and 'yes' != $settings['caption_all_time']) : ?>
						<?php $this->render_caption($content); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
<?php
		}

		public function link_only($content) {
			$settings      = $this->get_settings_for_display();
			$image_caption = get_post($content['id']);
			$animation = $settings['button_hover_animation'] ? 'elementor-animation-' . $settings['button_hover_animation'] : '';

			$this->add_render_attribute(
				[
					'lightbox-attributes' => [
						'class' => [
							'elementor-clickable',
							'icon-type-' . $settings['link_type'],
							$animation,
						],
						'data-elementor-open-lightbox' => 'no',
						'data-caption'                 => wp_kses_post($image_caption->post_excerpt),
					],
				],
				'',
				'',
				true
			);

			$image_url = wp_get_attachment_image_src($content['id'], 'full');

			if (!$image_url) {
				$this->add_render_attribute('lightbox-attributes', 'href', $content['url'], true);
			} else {
				$this->add_render_attribute('lightbox-attributes', 'href', $image_url[0], true);
			}

			$this->lightbox_slide_index++;

			if (1 === $this->lightbox_slide_index) {
				$this->add_render_attribute('lightbox-attributes', 'class', ['bdt-ep-advanced-image-gallery-item-link', 'bdt-hidden-gallery-button']);
				echo '<a ' . wp_kses_post($this->get_render_attribute_string('lightbox-attributes')) . '>';

				if ('simple_text' == $settings['lightbox_link_type']) {
					echo '<span>' . esc_html($settings['gallery_link_text']) . '</span>';
				} elseif ('link_icon' == $settings['lightbox_link_type']) {
					Icons_Manager::render_icon($settings['gallery_link_icon'], ['aria-hidden' => 'true', 'class' => 'fa-fw']);
				} else {
					$link_image_src = Group_Control_Image_Size::get_attachment_image_src($settings['link_image']['id'], 'link_image_size', $settings);
					$link_image_src = ($link_image_src) ? $link_image_src : $settings['link_image']['url'];
					echo '<img src=' . esc_url($link_image_src) . ' alt="' . esc_html(get_the_title()) . '">';
				}
				echo '</a>';
			} else {
				$this->add_render_attribute('lightbox-attributes', 'class', 'bdt-hidden');
				echo '<a ' . wp_kses_post($this->get_render_attribute_string('lightbox-attributes')) . '></a>';
			}
		}
	}

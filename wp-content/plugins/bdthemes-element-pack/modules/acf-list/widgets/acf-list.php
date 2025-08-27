<?php

namespace ElementPack\Modules\AcfList\Widgets;

use ElementPack\Base\Module_Base;
use Elementor\Icons_Manager;
use Elementor\Controls_Manager;
use ElementPack\Utils;
use ElementPack\Includes\Controls\SelectInput\Dynamic_Select;
use ElementPack\Includes\ACF_Global;
use ElementPack\Traits\Global_Widget_Controls;

if ( ! defined( 'ABSPATH' ) )
	exit; // Exit if accessed directly

class Acf_List extends Module_Base {
	use Global_Widget_Controls;

	public function get_name() {
		return 'bdt-acf-list';
	}

	public function get_title() {
		return BDTEP . esc_html__( 'ACF List', 'bdthemes-element-pack' );
	}

	public function get_icon() {
		return 'bdt-wi-acf-list';
	}

	public function get_categories() {
		return [ 'element-pack' ];
	}

	public function get_style_depends() {
		if ( $this->ep_is_edit_mode() ) {
			return [ 'ep-styles' ];
		} else {
			return [ 'ep-fancy-list' ];
		}
	}

	public function get_keywords() {
		return [ 'acf list', 'acf', 'list' ];
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
			'section_acf_field',
			[ 
				'label' => esc_html__( 'ACF Field', 'bdthemes-element-pack' ),
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
                'description' => sprintf(__('Supported field type: <b>%1s</b>', 'bdthemes-element-pack'), 'Repeater'),
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
                'placeholder' => __('Type repeater sub field for list title', 'bdthemes-element-pack'),
                // translators: %1s, %2s, and %3s are the names of the supported field types, e.g., "Text", "Textarea", "WYSIWYG"
                'description' => sprintf(__('Supported field type: <b>%1s</b>, <b>%2s</b>, <b>%3s</b>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['text', 'textarea', 'wysiwyg'],
                ],
            ]
        );

        $this->add_control(
            'text',
            [
                'label' => __('Text', 'bdthemes-element-pack'),
                'dynamic' => ['active' => false],
                'type'        => Dynamic_Select::TYPE,
                'label_block' => true,
                'placeholder' => __('Type repeater sub field for list text', 'bdthemes-element-pack'),
                // translators: %1s, %2s, and %3s are the names of the supported field types, e.g., "Text", "Textarea", "WYSIWYG"
                'description' => sprintf(__('Supported field type: <b>%1s</b>, <b>%2s</b>, <b>%3s</b>', 'bdthemes-element-pack'), 'Text','Textarea','WYSIWYG'),
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
                'placeholder' => __('Type repeater sub field for list image', 'bdthemes-element-pack'),
                // translators: %1s is the name of the supported field type, e.g., "Image"
                'description' => sprintf(__('Supported field type: <b>%1s</b>', 'bdthemes-element-pack'), 'Image'),
                'query_args'  => [
                    'query'        => 'acf',
                    'field_type'   => ['image'],
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
                'placeholder' => __('Type repeater sub field for list link', 'bdthemes-element-pack'),
                // translators: %1s is the name of the supported field type, e.g., "URL"
                'description' => sprintf(__('Supported field type: <b>%1s</b>', 'bdthemes-element-pack'), 'URL'),
                'query_args'  => [
					'query'        => 'acf',
                    'field_type'   => ['url'],
                ],
            ]
        );

		$this->end_controls_section();

		$this->start_controls_section(
			'section_layout',
			[ 
				'label' => esc_html__( 'Layout', 'bdthemes-element-pack' ),
			]
		);

		$this->add_control(
			'layout_style',
			[ 
				'label'   => esc_html__( 'Layout Style', 'bdthemes-element-pack' ),
				'type'    => Controls_Manager::SELECT,
				'default' => 'style-1',
				'options' => [ 
					'style-1' => '01',
					'style-2' => '02',
					'style-3' => '03',
				],
			]
		);

		$this->add_control(
			'list_icon',
			[ 
				'label'       => esc_html__( 'Icon', 'bdthemes-element-pack' ),
				'type'        => Controls_Manager::ICONS,
				'label_block' => false,
				'skin'        => 'inline',
				
			]
		);

		$this->register_fancy_list_controls();
	}

	protected function render() {
		$settings = $this->get_settings_for_display();
		$this->add_render_attribute( 'icon_list', 'class', 'bdt-fancy-list-icon' );
		$this->add_render_attribute( 'list_item', 'class', 'elementor-icon-list-item' );

        $repeater_field = get_field_object( $settings['repeater_field'] );

        if (empty($settings['repeater_field'] && $repeater_field)) {
            return;
        }

        $acf_helper = new ACF_Global();
        $field_values = $acf_helper->get_acf_field_value( $settings['repeater_field'], $repeater_field['parent'] );

        if (empty($field_values)) {
            return;
        }

        $title = $settings['title'];
        $text = $settings['text'];
        $image = $settings['image'];
        $link = $settings['link'];



		?>
		<div class="bdt-fancy-list bdt-acf bdt-fancy-list-<?php echo esc_attr( $settings['layout_style'] ); ?>">
			<ul class="bdt-list bdt-fancy-list-group" <?php $this->print_render_attribute_string( 'icon_list' ); ?>>
				<?php
				foreach ( $field_values as $index => $item ) :
					$repeater_setting_key = $this->get_repeater_setting_key( 'text', 'icon_list', $index );
					$this->add_render_attribute( $repeater_setting_key, 'class', 'elementor-icon-list-text' );
					$this->add_inline_editing_attributes( $repeater_setting_key );

					$this->add_render_attribute( 'list_title_tags', 'class', 'bdt-fancy-list-title', true );
                    
                    $list_title = isset($item[$title]) ? $item[$title] : '';
                    $list_text = isset($item[$text]) ? $item[$text] : '';
                    $image_src = !empty($item[$image]) ? wp_get_attachment_image_src($item[$image]['id'], 'full') : '';
                    $image_src =  $image_src ? $image_src[0] : '';
                    $list_link = isset($item[$link]) ? $item[$link] : '';
                    

					?>
					<li>
						<?php
						if ( ! empty( $list_link ) ) {
							$link_key = 'link_' . $index;

							$this->add_render_attribute(
								[
									$link_key => [
										'href' => esc_url($list_link),
										'class' => 'bdt-fancy-list-wrap',
									]
								],
								'',
								'',
								true
							);

							echo '<a ' . wp_kses_post($this->get_render_attribute_string( $link_key )) . '>';
						} else {
							echo '<div class="bdt-fancy-list-wrap">';
						}
						?>
						<div class="bdt-flex flex-wrap">
							<?php
							if ( $settings['show_number_icon'] == 'yes' ) {
								echo '<div class="bdt-fancy-list-number-icon"><span>'; ?>
								<?php echo esc_html( $index + 1); ?>
								<?php echo '</span></div>';
							}
							?>
							<?php if ( ! empty( $image_src ) ) : ?>
								<div class="bdt-fancy-list-img">
									<?php
									$thumb_url = $image_src;
									if ( $thumb_url ) {
										print( wp_get_attachment_image(
											$item[$image]['id'],
											'medium',
											false,
											[ 
												'alt' => esc_html( $list_title )
											]
										) );
									}
									?>
								</div>
							<?php endif; ?>
							<div class="bdt-fancy-list-content">
								<<?php echo esc_attr( Utils::get_valid_html_tag( $settings['title_tags'] ) ); ?>
									<?php $this->print_render_attribute_string( 'list_title_tags' ); ?>>
									<?php echo wp_kses_post( $list_title ); ?>
								</<?php echo esc_attr( Utils::get_valid_html_tag( $settings['title_tags'] ) ); ?>>
								<p class="bdt-fancy-list-text">
									<?php echo wp_kses_post( $list_text ); ?>
								</p>
							</div>
							<?php if ( ! empty( $settings['list_icon']['value'] ) ) : ?>
								<div class="bdt-fancy-list-icon">
									<?php Icons_Manager::render_icon( $settings['list_icon'], [ 'aria-hidden' => 'true' ] ); ?>
								</div>
							<?php endif; ?>
						</div>
						<?php
						if ( ! empty( $list_link ) ) :
							?>
							</a>
						<?php else : ?>
				</div>
			<?php endif; ?>
			</li>
		<?php endforeach; ?>
		</ul>
		</div>
		<?php
	}
}

<?php
namespace ElementPack\VariationSwatches\Admin;

defined( 'ABSPATH' ) || exit;

use ElementPack\VariationSwatches\Helper;
use ElementPack\VariationSwatches\Variation_Swatches;
use ElementPack\Base\Singleton;

class Term_Meta {
	use Singleton;
	const COLOR_META_KEY = 'swatches_color';
	const LABEL_META_KEY = 'swatches_label';
	const IMAGE_META_KEY = 'swatches_image';	

	public function __construct() {
		add_filter( 'product_attributes_type_selector', [ $this, 'add_attribute_types' ] );

		add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_scripts' ] );

		$attribute_taxonomies = wc_get_attribute_taxonomies();

		if ( empty( $attribute_taxonomies ) ) {
			return;
		}

		// Add custom fields.
		foreach ( $attribute_taxonomies as $tax ) {
			add_action( 'pa_' . $tax->attribute_name . '_add_form_fields', [ $this, 'add_attribute_fields' ] );
			add_action( 'pa_' . $tax->attribute_name . '_edit_form_fields', [ $this, 'edit_attribute_fields' ], 10, 2 );

			add_filter( 'manage_edit-pa_' . $tax->attribute_name . '_columns', [ $this, 'add_attribute_columns' ] );
			add_action( 'manage_pa_' . $tax->attribute_name . '_custom_column', [ $this, 'add_attribute_column_content' ], 10, 3 );
		}

		add_action( 'created_term', [ $this, 'save_term_meta' ] );
		add_action( 'edit_term', [ $this, 'save_term_meta' ] );
	}


	public function add_attribute_types( $types ) {
		$types = array_merge( $types, $this->get_swatches_types() );

		return $types;
	}

	public function get_swatches_types() {
		return Helper::get_swatches_types();
	}

	public function enqueue_scripts() {
		
		$screen = get_current_screen();

		if ( strpos( $screen->id, 'edit-pa_' ) === false && strpos( $screen->id, 'product' ) === false ) {
			return;
		}

		wp_enqueue_script( 'ep-variation-swataches-term' );
	}

	public function add_attribute_fields( $taxonomy ) {
		$attribute = Helper::get_attribute_taxonomy( $taxonomy );

		if ( ! Helper::attribute_is_swatches( $attribute, 'edit' ) ) {
			return;
		}
		?>

		<div class="form-field term-swatches-wrap">
			<label><?php echo esc_html( $this->field_label( $attribute->attribute_type ) ); ?></label>
			<?php $this->field_input( $attribute->attribute_type ); ?>
			<p class="description"><?php esc_html_e( 'This data will be used for variation swatches of variable products.', 'bdthemes-element-pack' ) ?></p>
		</div>

		<?php
	}

	public function edit_attribute_fields( $term, $taxonomy ) {
		$attribute = Helper::get_attribute_taxonomy( $taxonomy );

		if ( ! Helper::attribute_is_swatches( $attribute, 'edit' ) ) {
			return;
		}
		?>

		<tr class="form-field form-required">
			<th scope="row" valign="top">
				<label><?php echo esc_html( $this->field_label( $attribute->attribute_type ) ); ?></label>
			</th>
			<td>
				<?php $this->field_input( $attribute->attribute_type, $term ); ?>
				<p class="description"><?php esc_html_e( 'This data will be used for variation swatches of variable products.', 'bdthemes-element-pack' ) ?></p>
			</td>
		</tr>

		<?php
	}

	public function field_label( $type ) {
		$labels = [
			'color'  => esc_html__( 'Swatches Color', 'bdthemes-element-pack' ),
			'image'  => esc_html__( 'Swatches Image', 'bdthemes-element-pack' ),
			'label'  => esc_html__( 'Swatches Label', 'bdthemes-element-pack' ),
		];

		if ( array_key_exists( $type, $labels ) ) {
			return $labels[ $type ];
		}

		return '';
	}

	protected function field_name( $type ) {
		return 'ep_variation_swatches_' . $type;
	}

	public function field_input( $type, $term = null ) {
		if ( ! in_array( $type, [ 'image', 'color', 'label' ] ) ) {
			return;
		}

		$value = '';

		if ( $term && is_object( $term ) ) {
			$value = $this->get_meta( $term->term_id, $type );
		}

		$args = apply_filters(
			'ep_variation_swatches_term_field_args',
			[
				'type'  => $type,
				'value' => $value,
				'name'  => $this->field_name( $type ),
			],
			$term
		);

		static::swatches_field( $args );
	}

	public function save_term_meta( $term_id ) {
		$types = $this->get_swatches_types();

		foreach ( $types as $type => $label ) {
			$input_name = $this->field_name( $type );
			$term_meta  = isset( $_POST[ $input_name ] ) ? sanitize_text_field( wp_unslash( $_POST[ $input_name ] ) ) : null;

			if ( $term_meta ) {
				$this->update_meta( $term_id, $type, $term_meta );
			}
		}
	}

	public function add_attribute_columns( $columns ) {
		$attribute_name     = ! empty( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : '';
		$attribute_taxonomy = $attribute_name ? Helper::get_attribute_taxonomy( $attribute_name ) : null;

		if ( ! $attribute_taxonomy ) {
			return $columns;
		}

		if ( ! Helper::attribute_is_swatches( $attribute_taxonomy, 'edit' ) ) {
			return $columns;
		}

		$new_columns = [];

		if ( isset( $columns['cb'] ) ) {
			$new_columns['cb'] = $columns['cb'];
			unset( $columns['cb'] );
		}

		$new_columns['thumb'] = '';

		return array_merge( $new_columns, $columns );
	}

	public function add_attribute_column_content( $content, $column, $term_id ) {
		if ( 'thumb' != $column ) {
			return;
		}

		$attribute = ! empty( $_GET['taxonomy'] ) ? sanitize_text_field( wp_unslash( $_GET['taxonomy'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended

		if ( $attribute ) {
			$attr = Helper::get_attribute_taxonomy( $attribute );
		}

		if ( ! $attr ) {
			return;
		}

		$value = $this->get_meta( $term_id, $attr->attribute_type );
		$html  = '';

		switch ( $attr->attribute_type ) {
			case 'color':
				$html = sprintf(
					'<div class="ep-variation-swatches-item ep-variation-swatches-item--color" style="--ep-swatches-color: %s"></div>',
					esc_attr( $value )
				);
				break;

			case 'image':
				$image_src = $value ? wp_get_attachment_image_url( $value ) : false;
				$image_src = $image_src ? $image_src : wc_placeholder_img_src( 'thumbnail' );
				$html      = sprintf(
					'<img class="ep-variation-swatches-item ep-variation-swatches-item--image" src="%s" width="40px" height="40px">',
					esc_url( $image_src )
				);
				break;

			case 'label':
				$html = sprintf(
					'<div class="ep-variation-swatches-item ep-variation-swatches-item--label">%s</div>',
					esc_html( $value )
				);
				break;
		}

		$html = apply_filters( 'ep_variation_swatches_attribute_thumb_column_content', $html, $value, $attr, $term_id );

		if ( ! empty( $html ) ) {
			echo '<div class="ep-variation-swatches__thumbnail ep-variation-swatches--' . esc_attr( $attr->attribute_type ) . '">';
			echo wp_kses_post( $html );
			echo '</div>';
		}
	}

	public function insert_term( $name, $tax, $data = [] ) {
		$term = wp_insert_term( $name, $tax );

		if ( is_wp_error( $term ) ) {
			return $term;
		}

		if ( ! empty( $data['type'] ) && isset( $data['value'] ) ) {
			$this->update_meta( $term['term_id'], $data['type'], $data['value'] );
		}

		return $term;
	}

	public function update_meta( $term_id, $type, $value ) {
		$meta_key = $this->get_meta_key( $type );

		if ( empty( $meta_key ) ) {
			return;
		}

		update_term_meta( $term_id, $meta_key, $value );

		do_action( 'ep_variation_swatches_term_meta_updated', $value, $term_id, $meta_key, $type );
	}

	public function get_meta( $term_id, $type ) {
		if ( ! $term_id ) {
			return '';
		}

		$value = false;
		$key   = $this->get_meta_key( $type );
		$value = get_term_meta( $term_id, $key, true );

		if ( false === $value || '' === $value ) {
			$value = Variation_Swatches::instance()->get_mapping()->get_attribute_meta( $term_id, $type );
			
			if ( false === $value ) {
				$value = apply_filters( 'ep_variation_swatches_translate_term_meta', $value, $term_id, $key, $type );
			}
			
			if ( ! empty( $value ) ) {
				update_term_meta( $term_id, $key, $value );
			}
		}

		return apply_filters( 'ep_variation_swatches_term_meta', $value, $term_id, $key, $type );
	}

	public function get_meta_key( $type ) {
		switch ( $type ) {
			case 'color':
				$key = self::COLOR_META_KEY;
				break;

			case 'image':
				$key = self::IMAGE_META_KEY;
				break;

			case 'label':
				$key = self::LABEL_META_KEY;
				break;

			default:
				$key = '';
				break;
		}

		return $key;
	}

	public static function swatches_field( $args ) {
		$args = wp_parse_args( $args, [
			'type'  => 'color',
			'value' => '',
			'name'  => '',
			'label' => '',
			'desc'  => '',
			'echo'  => true,
		]);

		if ( empty( $args['name'] ) )  {
			return;
		}

		$html = '';

		switch ( $args['type'] ) {
			case 'image':
				$placeholder = wc_placeholder_img_src( 'thumbnail' );
				$image_src   = $args['value'] ? wp_get_attachment_image_url( $args['value'] ) : false;
				$image_src   = $image_src ? $image_src : $placeholder;

				$html = '<div class="ep-variation-swatches-field ep-variation-swatches__field-image ' . ( empty( $args['value'] ) ? 'is-empty' : '' ) . '">';
				$html .= ! empty( $args['label'] ) ? '<span class="label">' . esc_html( $args['label'] ) . '</span>' : '';
				$html .= '<div class="ep-variation-swatches__field-image-controls">';
				$html .= sprintf( '<img src="%s" data-placeholder="%s" width="60" height="60">', esc_url( $image_src ), esc_url( $placeholder ) );
				$html .= sprintf(
					'<a href="javascript:void(0)" class="button-link button-add-image" aria-label="%s" data-choose="%s">
						<span class="dashicons dashicons-plus-alt2"></span>
						<span class="screen-reader-text">%s</span>
					</a>',
					esc_attr__( 'Swatches Image', 'bdthemes-element-pack' ),
					esc_attr__( 'Use image', 'bdthemes-element-pack' ),
					esc_html__( 'Upload', 'bdthemes-element-pack' )
				);
				$html .= sprintf(
					'<a href="javascript:void(0)" class="button-link button-remove-image %s">
						<span class="dashicons dashicons-plus-alt2"></span>
						<span class="screen-reader-text">%s</span>
					</a>',
					empty( $args['value'] ) ? 'hidden' : '',
					esc_html__( 'Remove', 'bdthemes-element-pack' )
				);
				$html .= '</div>';
				$html .= ! empty( $args['desc'] ) ? '<p class="description">' . esc_html( $args['desc'] ) . '</p>' : '';
				$html .= sprintf( '<input type="hidden" name="%s" value="%s">', esc_attr( $args['name'] ), esc_attr( $args['value'] ) );
				$html .= '</div>';
				break;

			case 'color':
				if ( is_array( $args['value'] ) && isset( $args['value']['colors'] ) ) {
					$color = $args['value']['colors'][0];
				} else {
					$color = is_array( $args['value'] ) ? current( $args['value'] ) : $args['value'];
				}

				$html = '<div class="ep-variation-swatches-field ep-variation-swatches__field-color">';
				$html .= ! empty( $args['label'] ) ? '<span class="label">' . esc_html( $args['label'] ) . '</span>' : '';
				$html .= sprintf( '<input type="text" name="%s" value="%s">', esc_attr( $args['name'] ), esc_attr( $color ) );
				$html .= ! empty( $args['desc'] ) ? '<p class="description">' . esc_html( $args['desc'] ) . '</p>' : '';
				$html .= '</div>';
				break;

			case 'label':
				$html = '<div class="ep-variation-swatches-field ep-variation-swatches__field-label">';
				$html .= ! empty( $args['label'] ) ? '<span class="label">' . esc_html( $args['label'] ) . '</span>' : '';
				$html .= sprintf( '<input type="text" name="%s" value="%s" size="5">', esc_attr( $args['name'] ), esc_attr( $args['value'] ) );
				$html .= ! empty( $args['desc'] ) ? '<p class="description">' . esc_html( $args['desc'] ) . '</p>' : '';
				$html .= '</div>';
				break;
		}

		$html = apply_filters( 'ep_variation_swatches_field_html', $html, $args );

		if ( $args['echo'] ) {
			echo $html; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		}

		return $html;
	}
}

Term_Meta::instance();

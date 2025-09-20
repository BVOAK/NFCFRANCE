<?php
/**
 * @var $key
 * @var $active
 * @var $price
 * @var $name
 * @var $is_variation
 * @var $product_id
 */

defined( 'ABSPATH' ) || exit;

if ( ! str_contains( $key, '**' ) ) {
	$role = $key;
	$key  = Wpcpq_Helper()::generate_key() . '**' . $role;
} else {
	$key_arr = explode( '**', $key );
	$role    = ! empty( $key_arr[1] ) ? $key_arr[1] : 'all';
}

global $wp_roles;

switch ( $role ) {
	case 'all':
	case 'wpcpq_all':
		$role_name = esc_html__( 'All', 'wpc-price-by-quantity' );
		break;

	case 'wpcpq_user':
		$role_name = esc_html__( 'User (logged in)', 'wpc-price-by-quantity' );
		break;

	case 'wpcpq_guest':
		$role_name = esc_html__( 'Guest (not logged in)', 'wpc-price-by-quantity' );
		break;

	default:
		$role_name = isset( $wp_roles->roles[ $role ] ) ? $wp_roles->roles[ $role ]['name'] : esc_html__( 'All', 'wpc-price-by-quantity' );
}

$apply          = ! empty( $price['apply'] ) ? $price['apply'] : 'all';
$apply_val      = ! empty( $price['apply_val'] ) ? $price['apply_val'] : '';
$method         = ! empty( $price['method'] ) ? $price['method'] : 'volume';
$layout         = ! empty( $price['layout'] ) ? $price['layout'] : 'default';
$exclude_onsale = ! empty( $price['exclude_onsale'] ) ? $price['exclude_onsale'] : 'no';
?>
<div class="<?php echo esc_attr( $active ? 'wpcpq-item active' : 'wpcpq-item' ); ?>">
    <div class="wpcpq-item-header">
        <span class="wpcpq-item-move ui-sortable-handle"><?php esc_html_e( 'move', 'wpc-price-by-quantity' ); ?></span>
        <span class="wpcpq-item-label"><span class="wpcpq-label-role"><?php echo esc_html( $role_name ); ?></span> <span
                    class="wpcpq-label-apply"><?php echo esc_html( $apply === 'all' ? 'all' : $apply . ': ' . $apply_val ); ?></span></span>
        <span class="wpcpq-item-duplicate"><?php esc_html_e( 'duplicate', 'wpc-price-by-quantity' ); ?></span>
        <span class="wpcpq-item-remove"><?php esc_html_e( 'remove', 'wpc-price-by-quantity' ); ?></span>
    </div>
    <div class="wpcpq-item-content">
        <div class="wpcpq-item-line wpcpq-item-apply">
            <span><?php esc_html_e( 'Apply for', 'wpc-price-by-quantity' ); ?></span>
            <input type="hidden" name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][role]' ); ?>"
                   class="wpcpq_role" value="<?php echo esc_attr( $role ); ?>"/>
            <div>
                <select class="wpcpq_apply"
                        name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][apply]' ); ?>">
                    <option value="all" <?php selected( $apply, 'all' ); ?>><?php esc_attr_e( 'All products', 'wpc-price-by-quantity' ); ?></option>
                    <option value="products" <?php selected( $apply, 'products' ); ?>
                            disabled><?php esc_attr_e( 'Selected products (Premium)', 'wpc-price-by-quantity' ); ?></option>
					<?php
					$taxonomies = get_object_taxonomies( 'product', 'objects' );

					foreach ( $taxonomies as $taxonomy ) {
						echo '<option value="' . esc_attr( $taxonomy->name ) . '" ' . selected( $apply, $taxonomy->name, false ) . '>' . esc_html( $taxonomy->label ) . '</option>';
					}
					?>
                </select> <span><?php esc_html_e( 'Exclude on-sale products', 'wpc-price-by-quantity' ); ?> <input
                            type="checkbox"
                            name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][exclude_onsale]' ); ?>"
                            value="yes" <?php echo esc_attr( wc_string_to_bool( $exclude_onsale ) ? 'checked' : '' ); ?>/></span>
            </div>
            <div class="hide_if_apply_all">
                <input class="wpcpq_apply_val"
                       name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][apply_val]' ); ?>"
                       type="hidden" value="<?php echo esc_attr( $apply_val ); ?>"/>
				<?php if ( ! is_array( $apply_val ) ) {
					$apply_val = array_map( 'trim', explode( ',', $apply_val ) );
				} ?>
                <select class="wpcpq_terms" multiple="multiple"
                        data-<?php echo esc_attr( $apply ); ?>="<?php echo esc_attr( implode( ',', $apply_val ) ); ?>">
					<?php if ( is_array( $apply_val ) && ! empty( $apply_val ) ) {
						foreach ( $apply_val as $t ) {
							if ( $term = get_term_by( 'slug', $t, $apply ) ) {
								echo '<option value="' . esc_attr( $t ) . '" selected>' . esc_html( $term->name ) . '</option>';
							}
						}
					} ?>
                </select>
            </div>
        </div>
        <div class="wpcpq-item-line">
            <span><?php esc_html_e( 'Pricing method', 'wpc-price-by-quantity' ); ?></span>
            <div>
                <span class="hint--right hint--big"
                      aria-label="<?php esc_attr_e( 'Volume pricing: One unit price only that is corresponding to the quantity level will be applied. &#10; Tiered pricing: Multiple unit prices will be applied when the selected quantity covers over the tiers.', 'wpc-price-by-quantity' ); ?>">
                    <select class="wpcpq_method"
                            name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][method]' ); ?>">
                        <option value="volume" <?php selected( $method, 'volume' ); ?>><?php esc_attr_e( 'Volume pricing', 'wpc-price-by-quantity' ); ?></option>
                        <option value="tiered" <?php selected( $method, 'tiered' ); ?>><?php esc_attr_e( 'Tiered pricing', 'wpc-price-by-quantity' ); ?></option>
                    </select>
                </span>
            </div>
        </div>
        <div class="wpcpq-item-line">
            <span><?php esc_html_e( 'Layout', 'wpc-price-by-quantity' ); ?></span>
            <div>
				<span class="hint--right hint--big"
                      aria-label="<?php esc_attr_e( 'Choose the layout for the pricing table on a single product page.', 'wpc-price-by-quantity' ); ?>">
					<select class="wpcpq_layout"
                            name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][layout]' ); ?>">
						<option value="default" <?php selected( $layout, 'default' ); ?>><?php esc_attr_e( 'Default Table', 'wpc-price-by-quantity' ); ?></option>
						<option value="quick_buy" <?php selected( $layout, 'quick_buy' ); ?>><?php esc_attr_e( 'Quick Buy Table', 'wpc-price-by-quantity' ); ?></option>
						<option value="compact" <?php selected( $layout, 'compact' ); ?>><?php esc_attr_e( 'Compact', 'wpc-price-by-quantity' ); ?></option>
					</select>
				</span>
            </div>
        </div>
        <div class="wpcpq-item-line">
            <span><?php esc_html_e( 'Quantity-based pricing options', 'wpc-price-by-quantity' ); ?></span> <span
                    class="hint--top hint--big"
                    aria-label="<?php echo esc_attr( esc_html__( 'Set the price in numbers and the following characters: + / - / %. &#10; For example: &#10; +2: Increase the product price by 2 &#10; -5: Decrease the product price by 5 &#10; 10: Set the new product price as 10 &#10; 90%: Set the new product price as 90% of the original price.', 'wpc-price-by-quantity' ) ); ?>"><span
                        class="wpcpq-help-tip"></span></span>
			<?php
			$count = 0;

			if ( ! empty( $price['tiers'] ) && is_array( $price['tiers'] ) ) {
				foreach ( $price['tiers'] as $tier ) {
					$tier       = array_merge( [ 'quantity' => '', 'price' => '', 'text' => '' ], $tier );
					$tier_price = Wpcpq_Helper()::clean_price( $tier['price'] );
					?>
                    <div class="input-panel wpcpq-quantity">
                        <span class="wpcpq-qty-wrapper hint--top"
                              aria-label="<?php esc_attr_e( 'Quantity', 'wpc-price-by-quantity' ); ?>">
                            <input type="number" value="<?php echo esc_attr( $tier['quantity'] ); ?>" min="0"
                                   step="0.0001" class="wpcpq-quantity-qty"
                                   placeholder="<?php echo esc_attr( 'quantity' ); ?>"
                                   name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][tiers][' . $count . '][quantity]' ); ?>"/>
                        </span>
                        <span class="wpcpq-price-wrapper hint--top"
                              aria-label="<?php esc_attr_e( 'Price', 'wpc-price-by-quantity' ); ?>">
                            <input type="text" value="<?php echo esc_attr( $tier_price ); ?>"
                                   placeholder="<?php echo esc_attr( 'price' ); ?>" class="wpcpq-quantity-price"
                                   name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][tiers][' . $count . '][price]' ); ?>"/>
                        </span>
                        <span class="wpcpq-text-wrapper hint--top"
                              aria-label="<?php esc_attr_e( 'Leave empty to use the default text.', 'wpc-price-by-quantity' ); ?>">
                            <input type="text" value="<?php echo esc_attr( $tier['text'] ); ?>"
                                   placeholder="<?php echo esc_attr( 'after text' ); ?>" class="wpcpq-quantity-text"
                                   name="<?php echo esc_attr( 'wpcpq_prices' . $name . '[' . $key . '][tiers][' . $count . '][text]' ); ?>"/>
                        </span>
                        <span class="wpcpq-remove-qty hint--top"
                              aria-label="<?php esc_attr_e( 'remove', 'wpc-price-by-quantity' ); ?>">&times;</span>
                    </div>
					<?php
					$count ++;
				}
			} ?>
            <button class="button wpcpq-add-qty" type="button"
                    data-id="<?php echo esc_attr( $is_variation ? $product_id : 0 ); ?>"
                    data-key="<?php echo esc_attr( $key ); ?>"
                    data-count="<?php echo esc_attr( is_array( $price['tiers'] ) && ! empty( $price['tiers'] ) ? count( $price['tiers'] ) : 0 ); ?>">
				<?php esc_html_e( '+ New row', 'wpc-price-by-quantity' ); ?>
            </button>
        </div>
    </div>
</div>

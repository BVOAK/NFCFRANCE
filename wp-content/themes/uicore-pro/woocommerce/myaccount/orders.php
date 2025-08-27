<?php
/**
 * Template WooCommerce - Historique des commandes avec aperçus NFC
 * Chemin: /themes/uicore-pro/woocommerce/myaccount/orders.php
 * 
 * Ce template remplace le template WooCommerce par défaut pour ajouter
 * la colonne d'aperçu des configurations NFC
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_account_orders', $has_orders ); ?>

<?php if ( $has_orders ) : ?>

	<table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders account-orders-table">
		<thead>
			<tr>
				<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
					<th class="woocommerce-orders-table__header woocommerce-orders-table__header-<?php echo esc_attr( $column_id ); ?>">
						<span class="nobr"><?php echo esc_html( $column_name ); ?></span>
					</th>
				<?php endforeach; ?>
			</tr>
		</thead>

		<tbody>
			<?php
			foreach ( $customer_orders->orders as $customer_order ) {
				$order      = wc_get_order( $customer_order ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
				$item_count = $order->get_item_count() - $order->get_item_count_refunded();
				?>
				<tr class="woocommerce-orders-table__row woocommerce-orders-table__row--status-<?php echo esc_attr( $order->get_status() ); ?> order">
					<?php foreach ( wc_get_account_orders_columns() as $column_id => $column_name ) : ?>
						<td class="woocommerce-orders-table__cell woocommerce-orders-table__cell-<?php echo esc_attr( $column_id ); ?>" data-title="<?php echo esc_attr( $column_name ); ?>">
							<?php if ( has_action( 'woocommerce_my_account_my_orders_column_' . $column_id ) ) : ?>
								<?php do_action( 'woocommerce_my_account_my_orders_column_' . $column_id, $order ); ?>

							<?php elseif ( 'order-number' === $column_id ) : ?>
								<a href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
									<?php echo esc_html( _x( '#', 'hash before order number', 'woocommerce' ) . $order->get_order_number() ); ?>
								</a>

							<?php elseif ( 'order-date' === $column_id ) : ?>
								<time datetime="<?php echo esc_attr( $order->get_date_created()->date( 'c' ) ); ?>">
									<?php echo esc_html( wc_format_datetime( $order->get_date_created() ) ); ?>
								</time>

							<?php elseif ( 'order-status' === $column_id ) : ?>
								<?php echo esc_html( wc_get_order_status_name( $order->get_status() ) ); ?>

							<?php elseif ( 'order-total' === $column_id ) : ?>
								<?php
								/* translators: 1: formatted order total 2: total order items */
								echo wp_kses_post( sprintf( _n( '%1$s for %2$s item', '%1$s for %2$s items', $item_count, 'woocommerce' ), $order->get_formatted_order_total(), $item_count ) );
								?>

							<?php elseif ( 'order-actions' === $column_id ) : ?>
								<?php
								$actions = wc_get_account_orders_actions( $order );

								if ( ! empty( $actions ) ) {
									foreach ( $actions as $key => $action ) { // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
										echo '<a href="' . esc_url( $action['url'] ) . '" class="woocommerce-button' . esc_attr( $action['class'] ?? '' ) . '">' . esc_html( $action['name'] ) . '</a>';
									}
								}
								?>
							<?php endif; ?>
						</td>
					<?php endforeach; ?>
				</tr>
				<?php
			}
			?>
		</tbody>
	</table>

	<?php do_action( 'woocommerce_before_account_orders_pagination' ); ?>

	<?php if ( 1 < $customer_orders->max_num_pages ) : ?>
		<div class="woocommerce-pagination woocommerce-pagination--without-numbers woocommerce-Pagination">
			<?php if ( 1 !== $customer_orders->current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--previous woocommerce-Button woocommerce-Button--previous button" href="<?php echo esc_url( wc_get_account_orders_url( $customer_orders->current_page - 1 ) ); ?>"><?php esc_html_e( 'Previous', 'woocommerce' ); ?></a>
			<?php endif; ?>

			<?php if ( intval( $customer_orders->max_num_pages ) !== $customer_orders->current_page ) : ?>
				<a class="woocommerce-button woocommerce-button--next woocommerce-Button woocommerce-Button--next button" href="<?php echo esc_url( wc_get_account_orders_url( $customer_orders->current_page + 1 ) ); ?>"><?php esc_html_e( 'Next', 'woocommerce' ); ?></a>
			<?php endif; ?>
		</div>
	<?php endif; ?>

<?php else : ?>

	<?php wc_print_notice( esc_html__( 'No order has been made yet.', 'woocommerce' ) . ' <a class="woocommerce-Button wc-forward" href="' . esc_url( apply_filters( 'woocommerce_return_to_shop_redirect', wc_get_page_permalink( 'shop' ) ) ) . '">' . esc_html__( 'Browse products', 'woocommerce' ) . '</a>', 'notice' ); ?>

<?php endif; ?>

<style>
/* Styles spécifiques pour la table des commandes avec aperçus NFC */
.woocommerce-orders-table .woocommerce-orders-table__header-nfc-preview {
    width: 80px;
    text-align: center;
}

.woocommerce-orders-table .woocommerce-orders-table__cell-nfc-preview {
    text-align: center;
    padding: 8px 4px;
}

/* Mobile responsive */
@media (max-width: 768px) {
    .woocommerce-orders-table {
        font-size: 0.8em;
    }
    
    .woocommerce-orders-table .woocommerce-orders-table__header-nfc-preview {
        width: 60px;
    }
    
    .nfc-order-preview .nfc-thumb-small {
        width: 35px;
    }
    
    .nfc-badge {
        font-size: 9px;
        padding: 1px 4px;
    }
}

/* Amélioration de l'affichage responsive WooCommerce */
@media (max-width: 768px) {
    .woocommerce-orders-table thead {
        display: none;
    }
    
    .woocommerce-orders-table .woocommerce-orders-table__row {
        display: block;
        border: 1px solid #ddd;
        margin-bottom: 15px;
        border-radius: 8px;
        padding: 15px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    }
    
    .woocommerce-orders-table .woocommerce-orders-table__cell {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid #f0f0f0;
    }
    
    .woocommerce-orders-table .woocommerce-orders-table__cell:last-child {
        border-bottom: none;
    }
    
    .woocommerce-orders-table .woocommerce-orders-table__cell:before {
        content: attr(data-title) ": ";
        font-weight: bold;
        color: #666;
        flex: 0 0 auto;
        margin-right: 10px;
    }
    
    .woocommerce-orders-table .woocommerce-orders-table__cell-nfc-preview {
        justify-content: flex-end;
    }
}

/* Animation hover pour les aperçus */
.nfc-order-preview {
    transition: transform 0.2s ease;
}

.nfc-order-preview:hover {
    transform: scale(1.05);
}

/* Styles pour le badge NFC */
.nfc-order-preview .nfc-badge {
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.7; }
    100% { opacity: 1; }
}
</style>

<?php do_action( 'woocommerce_after_account_orders', $has_orders ); ?>
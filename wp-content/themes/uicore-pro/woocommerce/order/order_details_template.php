<?php
/**
 * Order details (version avec section NFC globale)
 * Template : woocommerce/order/order-details.php
 */

defined( 'ABSPATH' ) || exit;

$order = wc_get_order( $order_id );

if ( ! $order ) {
	return;
}

$order_items        = $order->get_items( apply_filters( 'woocommerce_purchase_order_item_types', 'line_item' ) );
$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$downloads          = $order->get_downloadable_items();
$actions            = array_filter(
	wc_get_account_orders_actions( $order ),
	function ( $key ) {
		return 'view' !== $key;
	},
	ARRAY_FILTER_USE_KEY
);

$show_customer_details = $order->get_user_id() === get_current_user_id();

// Collecter les items NFC pour section globale
$nfc_items = [];
foreach ( $order_items as $item_id => $item ) {
    $nfc_config_data = $item->get_meta('_nfc_config_complete');
    if ( !empty($nfc_config_data) ) {
        $nfc_config = json_decode($nfc_config_data, true);
        $nfc_screenshot_data = $item->get_meta('_nfc_screenshot_data');
        $nfc_screenshots = !empty($nfc_screenshot_data) ? json_decode($nfc_screenshot_data, true) : null;
        
        $nfc_items[] = [
            'item_id' => $item_id,
            'item' => $item,
            'config' => $nfc_config,
            'screenshots' => $nfc_screenshots
        ];
    }
}

if ( $show_downloads ) {
	wc_get_template(
		'order/order-downloads.php',
		array(
			'downloads'  => $downloads,
			'show_title' => true,
		)
	);
}
?>

<section class="woocommerce-order-details">
	<?php do_action( 'woocommerce_order_details_before_order_table', $order ); ?>

	<h2 class="woocommerce-order-details__title"><?php esc_html_e( 'Order details', 'woocommerce' ); ?></h2>

	<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
		<thead>
			<tr>
				<th class="woocommerce-table__product-name product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
				<th class="woocommerce-table__product-table product-total"><?php esc_html_e( 'Total', 'woocommerce' ); ?></th>
			</tr>
		</thead>

		<tbody>
			<?php
			do_action( 'woocommerce_order_details_before_order_table_items', $order );

			foreach ( $order_items as $item_id => $item ) {
				$product = $item->get_product();

				wc_get_template(
					'order/order-details-item.php',
					array(
						'order'              => $order,
						'item_id'            => $item_id,
						'item'               => $item,
						'show_purchase_note' => $show_purchase_note,
						'purchase_note'      => $product ? $product->get_purchase_note() : '',
						'product'            => $product,
					)
				);
			}

			do_action( 'woocommerce_order_details_after_order_table_items', $order );
			?>
		</tbody>

		<tfoot>
			<?php
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				?>
				<tr>
					<th scope="row"><?php echo esc_html( $total['label'] ); ?></th>
					<td><?php echo ( 'payment_method' === $key ) ? esc_html( $total['value'] ) : wp_kses_post( $total['value'] ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></td>
				</tr>
				<?php
			}
			?>
			<?php if ( $order->get_customer_note() ) : ?>
				<tr>
					<th><?php esc_html_e( 'Note:', 'woocommerce' ); ?></th>
					<td><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
			<?php endif; ?>
		</tfoot>
	</table>

	<?php do_action( 'woocommerce_order_details_after_order_table', $order ); ?>

</section>

<?php if ( !empty($nfc_items) ): ?>
<!-- Section NFC globale (données brutes pour styling) -->
<section class="nfc-order-summary">
	<div class="nfc-summary-data">
		<h3>Configurations NFC de cette commande</h3>
		
		<!-- Métadonnées globales -->
		<div class="nfc-order-meta">
			<span class="nfc-order-status" data-status="<?php echo esc_attr($order->get_status()); ?>">
				Statut : <?php echo esc_html(wc_get_order_status_name($order->get_status())); ?>
			</span>
			<span class="nfc-order-paid" data-paid="<?php echo $order->is_paid() ? 'true' : 'false'; ?>">
				<?php echo $order->is_paid() ? 'Commande payée' : 'En attente de paiement'; ?>
			</span>
			<span class="nfc-items-count">
				<?php echo count($nfc_items); ?> carte(s) personnalisée(s)
			</span>
		</div>
		
		<!-- Liste des configurations -->
		<div class="nfc-configurations-list">
			<?php foreach ($nfc_items as $index => $nfc_item): ?>
			<div class="nfc-config-summary" data-item-id="<?php echo esc_attr($nfc_item['item_id']); ?>">
				<div class="nfc-config-header">
					<span class="nfc-card-number">Carte #<?php echo $index + 1; ?></span>
					<span class="nfc-product-name"><?php echo esc_html($nfc_item['item']->get_name()); ?></span>
				</div>
				
				<div class="nfc-config-details">
					<?php if (isset($nfc_item['config']['color'])): ?>
					<span class="nfc-detail" data-type="color">
						<span class="nfc-detail-label">Couleur :</span>
						<span class="nfc-detail-value nfc-color-<?php echo esc_attr(strtolower($nfc_item['config']['color'])); ?>">
							<?php echo esc_html(ucfirst($nfc_item['config']['color'])); ?>
						</span>
					</span>
					<?php endif; ?>
					
					<?php 
					if (isset($nfc_item['config']['user'])):
						$user = $nfc_item['config']['user'];
						$fullName = trim(($user['firstName'] ?? '') . ' ' . ($user['lastName'] ?? ''));
					?>
					<?php if (!empty($fullName)): ?>
					<span class="nfc-detail" data-type="name">
						<span class="nfc-detail-label">Nom :</span>
						<span class="nfc-detail-value"><?php echo esc_html($fullName); ?></span>
					</span>
					<?php endif; ?>
					
					<?php if (!empty($user['email'])): ?>
					<span class="nfc-detail" data-type="email">
						<span class="nfc-detail-label">Email :</span>
						<span class="nfc-detail-value"><?php echo esc_html($user['email']); ?></span>
					</span>
					<?php endif; ?>
					
					<?php if (!empty($user['phone'])): ?>
					<span class="nfc-detail" data-type="phone">
						<span class="nfc-detail-label">Téléphone :</span>
						<span class="nfc-detail-value"><?php echo esc_html($user['phone']); ?></span>
					</span>
					<?php endif; ?>
					
					<?php if (!empty($user['company'])): ?>
					<span class="nfc-detail" data-type="company">
						<span class="nfc-detail-label">Entreprise :</span>
						<span class="nfc-detail-value"><?php echo esc_html($user['company']); ?></span>
					</span>
					<?php endif; ?>
					
					<?php if (!empty($user['position'])): ?>
					<span class="nfc-detail" data-type="position">
						<span class="nfc-detail-label">Poste :</span>
						<span class="nfc-detail-value"><?php echo esc_html($user['position']); ?></span>
					</span>
					<?php endif; ?>
					<?php endif; ?>
					
					<?php if (isset($nfc_item['config']['image']['name']) && !empty($nfc_item['config']['image']['name'])): ?>
					<span class="nfc-detail" data-type="image">
						<span class="nfc-detail-label">Logo :</span>
						<span class="nfc-detail-value"><?php echo esc_html($nfc_item['config']['image']['name']); ?></span>
					</span>
					<?php endif; ?>
				</div>
				
				<?php if (!empty($nfc_item['screenshots'])): ?>
				<div class="nfc-config-screenshot">
					<?php if (!empty($nfc_item['screenshots']['thumbnail'])): ?>
					<img src="<?php echo esc_url($nfc_item['screenshots']['thumbnail']); ?>" 
					     alt="Aperçu carte #<?php echo $index + 1; ?>" 
					     class="nfc-screenshot-thumb"
					     <?php if (!empty($nfc_item['screenshots']['full'])): ?>
					     data-full="<?php echo esc_url($nfc_item['screenshots']['full']); ?>"
					     <?php endif; ?> />
					<?php endif; ?>
				</div>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div>
	</div>
	
	<!-- JSON Data pour JavaScript si besoin -->
	<script type="application/json" id="nfc-order-data">
	<?php echo wp_json_encode([
		'order_id' => $order->get_id(),
		'order_status' => $order->get_status(),
		'is_paid' => $order->is_paid(),
		'customer_id' => $order->get_customer_id(),
		'current_user_id' => get_current_user_id(),
		'can_access_dashboard' => ($order->is_paid() && is_user_logged_in() && $order->get_customer_id() == get_current_user_id()),
		'items_count' => count($nfc_items),
		'configurations' => array_map(function($item) {
			return [
				'item_id' => $item['item_id'],
				'config' => $item['config'],
				'has_screenshots' => !empty($item['screenshots'])
			];
		}, $nfc_items)
	]); ?>
	</script>
</section>
<?php endif; ?>

<?php if ( $show_customer_details ) : ?>
	<?php wc_get_template( 'order/order-details-customer.php', array( 'order' => $order ) ); ?>
<?php endif; ?>
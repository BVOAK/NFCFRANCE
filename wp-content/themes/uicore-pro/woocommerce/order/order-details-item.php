<?php
/**
 * Order details item (version simplifiée pour NFC)
 * Template : woocommerce/order/order-details-item.php
 */

defined( 'ABSPATH' ) || exit;

if ( ! is_a( $product, 'WC_Product' ) ) {
	return;
}

$show_purchase_note = $order->has_status( apply_filters( 'woocommerce_purchase_note_order_statuses', array( 'completed', 'processing' ) ) );
$purchase_note      = $product ? $product->get_purchase_note() : '';

// Récupérer les données NFC si disponibles
$nfc_config_data = $item->get_meta('_nfc_config_complete');
$nfc_screenshot_data = $item->get_meta('_nfc_screenshot_data');
$has_nfc_config = !empty($nfc_config_data);

// Parser les données NFC
$nfc_config = null;
$nfc_screenshots = null;
if ($has_nfc_config) {
    $nfc_config = json_decode($nfc_config_data, true);
    if (!empty($nfc_screenshot_data)) {
        $nfc_screenshots = json_decode($nfc_screenshot_data, true);
    }
}
?>

<tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'woocommerce-table__line-item order_item', $item, $order ) ); ?>">

	<td class="woocommerce-table__product-name product-name">
		<?php
		$is_visible        = $product && $product->is_visible();
		$product_permalink = apply_filters( 'woocommerce_order_item_permalink', $is_visible ? $product->get_permalink( $item ) : '', $item, $order );

		echo wp_kses_post( apply_filters( 'woocommerce_order_item_name', $product_permalink ? sprintf( '<a href="%s">%s</a>', $product_permalink, $item->get_name() ) : $item->get_name(), $item, $is_visible ) );

		do_action( 'woocommerce_order_item_meta_start', $item_id, $item, $order, false );

		wc_display_item_meta( $item );

		do_action( 'woocommerce_order_item_meta_end', $item_id, $item, $order, false );
		?>
		
		<?php if ($has_nfc_config): ?>
		<!-- Bloc Configuration NFC -->
		<div class="nfc-configuration-block">
			<strong>Configuration personnalisée</strong>
			
			<?php if (isset($nfc_config['color'])): ?>
			<div class="nfc-config-item">
				<span class="nfc-label">Couleur :</span> 
				<span class="nfc-value nfc-color-<?php echo esc_attr(strtolower($nfc_config['color'])); ?>">
					<?php echo esc_html(ucfirst($nfc_config['color'])); ?>
				</span>
			</div>
			<?php endif; ?>
			
			<?php 
			if (isset($nfc_config['user'])):
				$firstName = $nfc_config['user']['firstName'] ?? '';
				$lastName = $nfc_config['user']['lastName'] ?? '';
				$fullName = trim($firstName . ' ' . $lastName);
			?>
			<?php if (!empty($fullName)): ?>
			<div class="nfc-config-item">
				<span class="nfc-label">Nom :</span> 
				<span class="nfc-value"><?php echo esc_html($fullName); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if (!empty($nfc_config['user']['email'])): ?>
			<div class="nfc-config-item">
				<span class="nfc-label">Email :</span> 
				<span class="nfc-value"><?php echo esc_html($nfc_config['user']['email']); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if (!empty($nfc_config['user']['phone'])): ?>
			<div class="nfc-config-item">
				<span class="nfc-label">Téléphone :</span> 
				<span class="nfc-value"><?php echo esc_html($nfc_config['user']['phone']); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if (!empty($nfc_config['user']['company'])): ?>
			<div class="nfc-config-item">
				<span class="nfc-label">Entreprise :</span> 
				<span class="nfc-value"><?php echo esc_html($nfc_config['user']['company']); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if (!empty($nfc_config['user']['position'])): ?>
			<div class="nfc-config-item">
				<span class="nfc-label">Poste :</span> 
				<span class="nfc-value"><?php echo esc_html($nfc_config['user']['position']); ?></span>
			</div>
			<?php endif; ?>
			<?php endif; ?>
			
			<?php if (isset($nfc_config['image']['name']) && !empty($nfc_config['image']['name'])): ?>
			<div class="nfc-config-item">
				<span class="nfc-label">Logo :</span> 
				<span class="nfc-value"><?php echo esc_html($nfc_config['image']['name']); ?></span>
			</div>
			<?php endif; ?>
			
			<?php if (!empty($nfc_screenshots['thumbnail'])): ?>
			<div class="nfc-config-item nfc-screenshot-item">
				<span class="nfc-label">Aperçu :</span>
				<div class="nfc-screenshot-container">
					<img src="<?php echo esc_url($nfc_screenshots['thumbnail']); ?>" 
					     alt="Aperçu configuration" 
					     class="nfc-screenshot-thumb" 
					     <?php if (!empty($nfc_screenshots['full'])): ?>
					     data-full-image="<?php echo esc_url($nfc_screenshots['full']); ?>"
					     <?php endif; ?> />
				</div>
			</div>
			<?php endif; ?>
			
			<?php 
			// Variables pour usage externe si besoin
			// Accessible via JavaScript ou CSS
			?>
			<script type="application/json" class="nfc-config-data">
			<?php echo wp_json_encode([
				'item_id' => $item_id,
				'order_id' => $order->get_id(),
				'config' => $nfc_config,
				'screenshots' => $nfc_screenshots,
				'order_status' => $order->get_status(),
				'is_paid' => $order->is_paid()
			]); ?>
			</script>
		</div>
		<?php endif; ?>
	</td>

	<td class="woocommerce-table__product-total product-total">
		<?php echo wp_kses_post( $order->get_formatted_line_subtotal( $item ) ); ?>
	</td>

</tr>

<?php if ( $show_purchase_note && $purchase_note ) : ?>
<tr class="woocommerce-table__product-purchase-note product-purchase-note">
	<td colspan="2"><?php echo wpautop( do_shortcode( wp_kses_post( $purchase_note ) ) ); ?></td>
</tr>
<?php endif; ?>
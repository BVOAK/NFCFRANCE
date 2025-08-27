<?php
/**
 * Template WooCommerce - Détail d'une commande avec aperçus NFC
 * Chemin: /themes/uicore-pro/woocommerce/myaccount/view-order.php
 * 
 * Ce template affiche le détail d'une commande avec les screenshots
 * des configurations NFC en grand format
 */

defined( 'ABSPATH' ) || exit;

$notes = $order->get_customer_order_notes();
?>
<p>
	<?php
	printf(
		/* translators: 1: order number 2: order date 3: order status */
		esc_html__( 'Order #%1$s was placed on %2$s and is currently %3$s.', 'woocommerce' ),
		'<mark class="order-number">' . $order->get_order_number() . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'<mark class="order-date">' . wc_format_datetime( $order->get_date_created() ) . '</mark>', // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
		'<mark class="order-status">' . esc_html( wc_get_order_status_name( $order->get_status() ) ) . '</mark>'
	);
	?>
</p>

<?php if ( $notes ) : ?>
	<h2><?php esc_html_e( 'Order updates', 'woocommerce' ); ?></h2>
	<ol class="woocommerce-OrderUpdates commentlist notes">
		<?php foreach ( $notes as $note ) : ?>
		<li class="woocommerce-OrderUpdate comment note">
			<div class="woocommerce-OrderUpdate-inner comment_container">
				<div class="woocommerce-OrderUpdate-text comment-text">
					<p class="woocommerce-OrderUpdate-meta meta"><?php echo date_i18n( esc_html__( 'l jS \o\f F Y, h:ia', 'woocommerce' ), strtotime( $note->comment_date ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?></p>
					<div class="woocommerce-OrderUpdate-description description">
						<?php echo wpautop( wptexturize( $note->comment_content ) ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
					</div>
	  			<div class="clear"></div>
	  		</div>
			<div class="clear"></div>
		</li>
		<?php endforeach; ?>
	</ol>
<?php endif; ?>

<?php do_action( 'woocommerce_view_order', $order->get_id() ); ?>

<!-- Section NFC personnalisée pour les aperçus en grand -->
<?php
// Vérifier s'il y a des produits NFC dans cette commande
$has_nfc_items = false;
foreach ( $order->get_items() as $item_id => $item ) {
    if ( $item->get_meta('_nfc_config_complete') ) {
        $has_nfc_items = true;
        break;
    }
}

if ( $has_nfc_items ) : ?>
<div class="nfc-order-overview">
    <h2 style="color: #0040C1; border-bottom: 2px solid #0040C1; padding-bottom: 10px; margin-top: 30px;">
        🎨 Vos cartes personnalisées
    </h2>
    
    <div class="nfc-items-grid">
        <?php
        foreach ( $order->get_items() as $item_id => $item ) {
            $config_data = $item->get_meta('_nfc_config_complete');
            if ( $config_data ) {
                $config = json_decode($config_data, true);
                
                // Générer URL sécurisée pour le client
                $customer_integration = new NFC_Customer_Integration();
                $screenshot_url = $customer_integration->get_customer_screenshot_url($order->get_id(), $item_id, 'full');
                $thumbnail_url = $customer_integration->get_customer_screenshot_url($order->get_id(), $item_id, 'thumb');
                ?>
                
                <div class="nfc-item-showcase">
                    <div class="nfc-item-header">
                        <h3><?php echo esc_html($item->get_name()); ?></h3>
                        <span class="nfc-color-badge nfc-color-<?php echo esc_attr($config['color'] ?? 'blanc'); ?>">
                            <?php echo ucfirst($config['color'] ?? 'Non défini'); ?>
                        </span>
                    </div>
                    
                    <?php if ( $screenshot_url || $thumbnail_url ) : ?>
                    <div class="nfc-screenshot-showcase">
                        <div class="nfc-screenshot-container">
                            <?php $display_url = $screenshot_url ?: $thumbnail_url; ?>
                            <img src="<?php echo esc_url($display_url); ?>" 
                                 alt="Aperçu de votre carte personnalisée" 
                                 class="nfc-screenshot-large"
                                 onclick="nfcOpenLightbox(this.src)">
                            
                            <?php if ( $screenshot_url && $screenshot_url !== $thumbnail_url ) : ?>
                            <div class="nfc-zoom-hint">
                                <span>🔍 Cliquez pour agrandir</span>
                            </div>
                            <?php endif; ?>
                        </div>
                        
                        <div class="nfc-config-summary">
                            <?php if ( isset($config['user']) ) : ?>
                            <div class="nfc-summary-item">
                                <strong>👤 Nom sur la carte :</strong>
                                <span><?php echo esc_html($config['user']['firstName'] . ' ' . $config['user']['lastName']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <?php if ( isset($config['image']['name']) ) : ?>
                            <div class="nfc-summary-item">
                                <strong>🎨 Logo :</strong>
                                <span><?php echo esc_html($config['image']['name']); ?></span>
                            </div>
                            <?php endif; ?>
                            
                            <div class="nfc-summary-item">
                                <strong>📅 Date de commande :</strong>
                                <span><?php echo esc_html(wc_format_datetime($order->get_date_created())); ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Statut de production détaillé -->
                    <div class="nfc-production-timeline">
                        <?php
                        $order_status = $order->get_status();
                        $created_date = $order->get_date_created();
                        $completion_date = $order->get_date_completed();
                        ?>
                        
                        <div class="timeline-step <?php echo $order_status === 'pending' ? 'active' : 'completed'; ?>">
                            <div class="timeline-icon">📋</div>
                            <div class="timeline-content">
                                <h4>Commande reçue</h4>
                                <p><?php echo esc_html(wc_format_datetime($created_date)); ?></p>
                            </div>
                        </div>
                        
                        <div class="timeline-step <?php echo in_array($order_status, ['processing', 'completed']) ? ($order_status === 'processing' ? 'active' : 'completed') : ''; ?>">
                            <div class="timeline-icon">⚙️</div>
                            <div class="timeline-content">
                                <h4>Personnalisation en cours</h4>
                                <p><?php echo $order_status === 'processing' ? 'En cours...' : 'Terminée'; ?></p>
                            </div>
                        </div>
                        
                        <div class="timeline-step <?php echo $order_status === 'completed' ? 'completed' : ''; ?>">
                            <div class="timeline-icon">📦</div>
                            <div class="timeline-content">
                                <h4>Expédition</h4>
                                <p><?php echo $completion_date ? esc_html(wc_format_datetime($completion_date)) : 'En attente'; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <?php
            }
        }
        ?>
    </div>
</div>
<?php endif; ?>

<!-- Lightbox pour agrandissement -->
<div id="nfcLightbox" class="nfc-lightbox" style="display: none;" onclick="nfcCloseLightbox()">
    <div class="nfc-lightbox-content">
        <span class="nfc-lightbox-close" onclick="nfcCloseLightbox()">&times;</span>
        <img id="nfcLightboxImage" src="" alt="Aperçu agrandi">
    </div>
</div>

<style>
/* Styles pour l'aperçu détaillé des commandes NFC */
.nfc-order-overview {
    margin-top: 30px;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 12px;
    border: 1px solid #e9ecef;
}

.nfc-items-grid {
    display: grid;
    gap: 30px;
    margin-top: 20px;
}

.nfc-item-showcase {
    background: white;
    border-radius: 12px;
    padding: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
    border: 1px solid #e9ecef;
}

.nfc-item-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 20px;
    flex-wrap: wrap;
    gap: 10px;
}

.nfc-item-header h3 {
    margin: 0;
    color: #333;
    font-size: 1.3em;
}

.nfc-color-badge {
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 0.9em;
    font-weight: 600;
}

.nfc-color-badge.nfc-color-blanc {
    background: #f8f9fa;
    color: #495057;
    border: 2px solid #dee2e6;
}

.nfc-color-badge.nfc-color-noir {
    background: #343a40;
    color: #ffffff;
    border: 2px solid #343a40;
}

.nfc-screenshot-showcase {
    display: grid;
    grid-template-columns: 1fr 300px;
    gap: 25px;
    align-items: start;
}

.nfc-screenshot-container {
    position: relative;
    text-align: center;
}

.nfc-screenshot-large {
    max-width: 100%;
    height: auto;
    border-radius: 12px;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    cursor: pointer;
    transition: transform 0.3s ease;
}

.nfc-screenshot-large:hover {
    transform: scale(1.02);
}

.nfc-zoom-hint {
    position: absolute;
    bottom: 10px;
    right: 10px;
    background: rgba(0,0,0,0.7);
    color: white;
    padding: 5px 10px;
    border-radius: 15px;
    font-size: 0.8em;
    opacity: 0;
    transition: opacity 0.3s ease;
}

.nfc-screenshot-container:hover .nfc-zoom-hint {
    opacity: 1;
}

.nfc-config-summary {
    background: #f8f9fa;
    padding: 20px;
    border-radius: 8px;
    border-left: 4px solid #0040C1;
}

.nfc-summary-item {
    display: flex;
    flex-direction: column;
    margin-bottom: 15px;
}

.nfc-summary-item:last-child {
    margin-bottom: 0;
}

.nfc-summary-item strong {
    color: #0040C1;
    margin-bottom: 5px;
}

.nfc-summary-item span {
    color: #666;
    font-size: 0.95em;
}

/* Timeline de production */
.nfc-production-timeline {
    display: flex;
    justify-content: space-between;
    margin-top: 25px;
    position: relative;
}

.nfc-production-timeline::before {
    content: '';
    position: absolute;
    top: 25px;
    left: 25px;
    right: 25px;
    height: 2px;
    background: #e9ecef;
    z-index: 1;
}

.timeline-step {
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    flex: 1;
    position: relative;
    z-index: 2;
}

.timeline-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    background: #e9ecef;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5em;
    margin-bottom: 10px;
    transition: all 0.3s ease;
}

.timeline-step.active .timeline-icon {
    background: #ffc107;
    animation: pulse 2s infinite;
}

.timeline-step.completed .timeline-icon {
    background: #28a745;
}

.timeline-content h4 {
    margin: 0 0 5px 0;
    font-size: 0.9em;
    color: #333;
}

.timeline-content p {
    margin: 0;
    font-size: 0.8em;
    color: #666;
}

/* Lightbox */
.nfc-lightbox {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.9);
    z-index: 9999;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nfc-lightbox-content {
    position: relative;
    max-width: 90%;
    max-height: 90%;
}

.nfc-lightbox-close {
    position: absolute;
    top: -40px;
    right: 0;
    color: white;
    font-size: 30px;
    cursor: pointer;
    background: rgba(0,0,0,0.5);
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}

#nfcLightboxImage {
    max-width: 100%;
    max-height: 100%;
    border-radius: 8px;
}

/* Responsive Design */
@media (max-width: 768px) {
    .nfc-screenshot-showcase {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .nfc-production-timeline {
        flex-direction: column;
        gap: 20px;
    }
    
    .nfc-production-timeline::before {
        display: none;
    }
    
    .timeline-step {
        flex-direction: row;
        text-align: left;
        gap: 15px;
    }
    
    .timeline-icon {
        flex-shrink: 0;
        margin-bottom: 0;
    }
    
    .nfc-item-header {
        flex-direction: column;
        align-items: flex-start;
    }
}

@media (max-width: 480px) {
    .nfc-order-overview {
        padding: 15px;
        margin: 20px -15px 0 -15px;
        border-radius: 0;
    }
    
    .nfc-item-showcase {
        padding: 20px 15px;
        margin: 0 -15px;
        border-radius: 0;
    }
}

@keyframes pulse {
    0% { transform: scale(1); }
    50% { transform: scale(1.05); }
    100% { transform: scale(1); }
}
</style>

<script>
// JavaScript pour le lightbox
function nfcOpenLightbox(src) {
    document.getElementById('nfcLightbox').style.display = 'flex';
    document.getElementById('nfcLightboxImage').src = src;
    document.body.style.overflow = 'hidden';
}

function nfcCloseLightbox() {
    document.getElementById('nfcLightbox').style.display = 'none';
    document.body.style.overflow = 'auto';
}

// Fermer avec Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        nfcCloseLightbox();
    }
});
</script>
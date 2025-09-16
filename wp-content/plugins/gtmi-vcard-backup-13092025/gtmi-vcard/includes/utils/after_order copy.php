<?php

add_action('woocommerce_order_status_changed', 'gtmi_vcard_order_change_status', 10, 3);

/**
 *  Woocommerce events
 *
 * woocommerce_new_order: when order is created before payment
 * woocommerce_before_thankyou: after payment just before thanks page
 * woocommerce_thankyou: after
 * woocommerce_order_status_changed: when the status of order is changed
 *
 */

// Check woocommerce plugin is activated
if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    /**
     * Throw when a new order is created and is status changed
     * When the order is paid, the status is processing
     *
     * @param int    $order_id   Order ID.
     * @param string $old_status Order old status.
     * @param string $new_status Order new status.
     */
    function gtmi_vcard_order_change_status($order_id, $old_status, $new_status)
    {
        $order = wc_get_order($order_id);
        if (! $order) {
            return;
        }
        if ('processing' === $new_status || 'completed' === $new_status) {
            error_log("GTMI_VCard: status of order $order_id changed from $old_status $new_status");
        }
    }

    /**
     * Throw when the payment is successfull
     *
     * @param int $order_id Order ID
     */
    function gtmi_vcard_order_payment_success($order_id)
    {
        $order = wc_get_order($order_id);
        if (! $order) {
            return;
        }

        error_log("GTMI_VCard: Order $order_id successfull");
        gtmi_vcard_new($order_id);
    }
    add_action('woocommerce_thankyou', 'gtmi_vcard_order_payment_success', 10, 1);

} else {
    // Warning message when woocommerce is not activated
    function gtmi_vcard_missing_woocommerce(): void
    {
        ?>
        <div class="notice notice-error is-dismissible">
            <p><?php _e('This plugin requires WooCommerce installed and actived.', 'gtmi_vcard'); ?></p>
        </div>
        <?php
    }
    add_action('admin_notices', 'gtmi_vcard_missing_woocommerce');
}

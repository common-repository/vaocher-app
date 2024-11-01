<?php

class VaocherAppWoocommerce
{
    public static function removeWoocommerceWebhooks()
    {
        if (! self::getInstalledVersion()) {
            return;
        }

        // WC_Data_Store is Woocommerce object
        $wooDataStore = WC_Data_Store::load('webhook');
        $wooWebhookIds = $wooDataStore->search_webhooks();

        foreach ($wooWebhookIds as $webhookId) {
            // See https://woocommerce.github.io/code-reference/classes/WC-Webhook.html
            $wooWebhook = new WC_Webhook($webhookId);
            if (strpos($wooWebhook->get_delivery_url(), 'vaocher.app') > 0) {
                $wooWebhook->delete(true);
            }
        }
    }

    public static function getInstalledVersion()
    {
        try {
            if (defined('WC_VERSION')) {
                $version = WC_VERSION;
            } elseif (defined('WOOCOMMERCE_VERSION')) {
                $version = WOOCOMMERCE_VERSION;
            } else {
                $version = self::tryGuessWoocommerceVersionFromFile();
            }

            return $version;
        } catch (exception $e) {
            return null;
        }
    }

    // Add hooks to render the gift voucher redeem HTML...
    public static function registerHooks()
    {
        if (! VaocherAppData::hasApiKey()) {
            return;
        }

        if (VaocherAppData::isWoocommerceEnabled()) {
            $cartPriorityAltered = 100;

            // Compatability with WooCommerce Extended Coupon Features PRO
            if (class_exists('WJECF_AutoCoupon')) {
                $cartPriorityAltered = 20;
            }

            // Compatability with WooCommerce AvaTax
            if (class_exists('WC_AvaTax_Checkout_Handler')) {
                $cartPriorityAltered = 1000;
            }

            // Compatability with Advanced Dynamic Pricing (AlgolPlus)
            add_filter('wdp_calculate_totals_hook_priority', function ($priority) use ($cartPriorityAltered) {
                return $cartPriorityAltered - 1;
            });

            // Store our data into a private form in the DOM
            add_action('woocommerce_before_cart', 'vaocherapp_woocommerce_private_form');
            add_action('woocommerce_before_checkout_form', 'vaocherapp_woocommerce_private_form');

            add_action('woocommerce_cart_totals_before_order_total', 'vaocherapp_woocommerce_cart_apply_gift_voucher');
            add_action('woocommerce_review_order_before_order_total', 'vaocherapp_woocommerce_cart_apply_gift_voucher');

            // Adjust the cart total to take into consideration the gift card balance
            add_action('woocommerce_after_calculate_totals', 'vaocherapp_woocommerce_after_calculate_totals',
                $cartPriorityAltered, 1);

            // Adding the gift card code to the order meta
            add_action('woocommerce_checkout_create_order', 'vaocherapp_woocommerce_checkout_create_order');

            // Remove the gift card
            add_action('woocommerce_cart_emptied', 'vaocherapp_woocommerce_cart_emptied');

            // Deduct the balance from the gift card
            add_action('woocommerce_pre_payment_complete', 'vaocherapp_woocommerce_redeem_gift_voucher');
            add_action('woocommerce_order_status_processing', 'vaocherapp_woocommerce_redeem_gift_voucher');
            add_action('woocommerce_order_status_pre-ordered', 'vaocherapp_woocommerce_redeem_gift_voucher');
            add_action('woocommerce_order_status_completed', 'vaocherapp_woocommerce_redeem_gift_voucher');
            add_action('woocommerce_payment_complete', 'vaocherapp_woocommerce_redeem_gift_voucher');

            // Diagnostics output
            add_action('wp_footer', function () {
                VaocherAppDiagnostics::getInstance()->render();
                VaocherAppLogger::write('Diagnostics', VaocherAppDiagnostics::getInstance()->getMessages());
            });
        }

        add_action('woocommerce_admin_order_totals_after_tax', 'vaocherapp_woocommerce_admin_order_totals_after_tax');
        add_filter('woocommerce_get_order_item_totals', 'vaocherapp_woocommerce_get_order_item_totals', 30, 3);
    }

    /**
     * @return bool
     */
    public static function isActivated()
    {
        return class_exists('woocommerce');
    }

    /**
     * @return string|null
     */
    public static function getCurrency()
    {
        return function_exists('get_woocommerce_currency')
            ? get_woocommerce_currency()
            : null;
    }

    private static function tryGuessWoocommerceVersionFromFile()
    {
        try {
            if (file_exists(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php')) {
                $pluginData = get_file_data(WP_PLUGIN_DIR.'/woocommerce/woocommerce.php', ['Version' => 'Version']);

                if ($pluginData && is_array($pluginData) && isset($pluginData['Version'])) {
                    return $pluginData['Version'];
                }

                if (! function_exists('get_plugins')) {
                    require_once(ABSPATH.'wp-admin/includes/plugin.php');
                }

                $pluginFolder = get_plugins('/'.'woocommerce');
                $pluginFile = 'woocommerce.php';

                if (isset($pluginFolder[$pluginFile]['Version'])) {
                    return $pluginFolder[$pluginFile]['Version'];
                }

                return null;
            }
        } catch (exception $e) {

        }

        return null;
    }
}
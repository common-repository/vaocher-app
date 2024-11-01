<?php

class VaocherAppHandlers
{
    /**
     * Check if the current request is
     *
     * @return bool
     */
    public static function isConnectSuccessRequest()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' &&
            isset($_GET['vaocherapp_connected'], $_GET['vaocherapp_api_key'], $_GET['vaocherapp_account_id']) &&
            $_GET['vaocherapp_connected'] === '1' &&
            $_GET['vaocherapp_api_key'] &&
            $_GET['vaocherapp_account_id']
        ) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public static function isDisconnectingRequest()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' &&
               isset($_POST['disconnect_request']) &&
               $_POST['disconnect_request'] === '1';
    }

    public static function handleDisconnectRequest()
    {
        VaocherAppApi::notifyDisconnectWoocommerce();
        VaocherAppWoocommerce::removeWoocommerceWebhooks();

        VaocherAppData::deleteOption('account_id');
        VaocherAppData::deleteOption('api_key');
        VaocherAppData::deleteOption('woocommerce_enabled');
        // VaocherAppData::deleteOption('woocommerce_apply_to_shipping');
        // VaocherAppData::deleteOption('woocommerce_apply_to_taxes');
    }

    /**
     * @return bool
     */
    public static function isUpdateWoocommerceSettingsRequest()
    {
        return $_SERVER['REQUEST_METHOD'] === 'POST' &&
               isset($_POST['vaocherapp_woocommerce_settings']) &&
               $_POST['vaocherapp_woocommerce_settings'] === '1';
    }

    public static function handleUpdateWoocommerceSettingsRequest()
    {
        VaocherAppData::setWoocommerceAsEnabled($_POST['vaocherapp_woocommerce_enabled'] === '1');

        $enableTestMode = isset($_POST['vaocherapp_woocommerce_test_mode']) && $_POST['vaocherapp_woocommerce_test_mode'] === '1';
        setcookie(
            VaocherAppData::getWoocommerceTestModeCookieKey(),
            $enableTestMode ? '1' : '',
            $enableTestMode ? time() + (60 * 60) : time() - 1,
            '/'
        );

        VaocherAppData::enableDiagnosticsMode(isset($_POST['vaocherapp_woocommerce_diagnostics_mode']) && $_POST['vaocherapp_woocommerce_diagnostics_mode'] === '1');
    }
}

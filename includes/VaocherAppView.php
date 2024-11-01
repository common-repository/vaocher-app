<?php

class VaocherAppView
{
    public static function renderSettingMenuItem()
    {
        return function ($links) {
            // Use unshift to add to the end
            array_unshift(
                $links,
                '<a href="options-general.php?page='.VaocherApp::getInstance()->getSettingPageName().'">Settings</a>'
            );

            return $links;
        };
    }

    public static function renderGlobalNotification()
    {
        return function () {
            // See global vars in https://codex.wordpress.org/Global_Variables
            global $pagenow;

            if ($pagenow !== 'options-general.php') {
                return null;
            }

            if (! isset($_GET['page']) || $_GET['page'] !== VaocherApp::getInstance()->getSettingPageName()) {
                return null;
            }

            if (! VaocherAppData::getAccountId()) {
                echo '<div class="notice notice-warning is-dismissible" id="vaocherapp-nag"><p>'.__('Please <a href="/wp-admin/options-general.php?page='.VaocherApp::getInstance()->getSettingPageName().'">connect/create your VaocherApp account</a> to your WordPress account to sell gift cards online').'</p></div>';
            }
        };
    }

    public static function renderAdminSettingView()
    {
        return function () {
            // Connected and got redirected back
            // There will be an API key, we need to store this and try to push some data back
            if (VaocherAppHandlers::isConnectSuccessRequest()) {
                VaocherAppData::setApiKey(sanitize_text_field($_GET['vaocherapp_api_key']));
                VaocherAppData::setAccountId(sanitize_text_field($_GET['vaocherapp_account_id']));

                echo VaocherAppHelpers::redirectViaJavascript(VaocherAppUrl::toWordpressSettingPage());

                return;
            }

            if (VaocherAppHandlers::isDisconnectingRequest()) {
                VaocherAppHandlers::handleDisconnectRequest();
                echo VaocherAppHelpers::redirectViaJavascript(VaocherAppUrl::toWordpressSettingPage());

                return;
            }

            if (VaocherAppHandlers::isUpdateWoocommerceSettingsRequest()) {
                VaocherAppHandlers::handleUpdateWoocommerceSettingsRequest();
                echo VaocherAppHelpers::redirectViaJavascript(VaocherAppUrl::toWordpressSettingPage());

                return;
            }

            // Already connected
            if (VaocherAppData::getApiKey()) {
                // Every time users open the settings, we should update the connection data back to our server.
                // This helps us to keep track of the current active connections.
                VaocherAppApi::updateIntegrationConnectData();

                $accountInfo = VaocherAppApi::getAccountInfo();
                $woocommerceIsActivated = VaocherAppWoocommerce::isActivated();
                $woocommerceVersion = VaocherAppWoocommerce::getInstalledVersion();
                $woocommerceVersionCompatible = version_compare($woocommerceVersion, '3.0') >= 0;
                $woocommerceEnabled = $woocommerceVersionCompatible && VaocherAppData::isWoocommerceEnabled();
                $woocommerceCanEnableTestMode = current_user_can('administrator');
                $woocommerceIsInTestMode = VaocherAppData::isWoocommerceInTestMode();
                $woocommerceCurrency = VaocherAppWoocommerce::getCurrency();
                $diagnosticsModeEnabled = VaocherAppData::isDiagnosticsModeEnabled();

                echo self::renderView('back/connected', [
                    'accountInfo' => $accountInfo,
                    'woocommerceIsActivated' => $woocommerceIsActivated,
                    'woocommerceVersionCompatible' => $woocommerceVersionCompatible,
                    'woocommerceEnabled' => $woocommerceEnabled,
                    'woocommerceCanEnableTestMode' => $woocommerceCanEnableTestMode,
                    'woocommerceIsInTestMode' => $woocommerceIsInTestMode,
                    'woocommerceCurrency' => $woocommerceCurrency,
                    'diagnosticsModeEnabled' => $diagnosticsModeEnabled,
                ]);

                return;
            }

            // Not connected, build the data and render the instruction
            $connectUrl = VaocherAppUrl::toBackendApp('/settings/integrations/wordpress?'.http_build_query([
                    'connecting' => 1,
                    'redirect_url' => site_url($_SERVER['REQUEST_URI']),
                ]));

            echo self::renderView('back/connecting', [
                'connectUrl' => $connectUrl,
            ]);
        };
    }

    public static function renderView($name, array $data = [])
    {
        $name = ltrim($name, '/');
        if (! VaocherAppHelpers::strEndsWith($name, '.php')) {
            $name .= '.php';
        }

        extract($data);

        ob_start();
        include VAOCHER_APP_BASE_PATH.'views/'.$name;
        $content = ob_get_contents();
        ob_end_clean();

        return $content;
    }

    public static function includePartialView($name, array $data = [])
    {
        return self::renderView('/partials/'.$name, $data);
    }
}

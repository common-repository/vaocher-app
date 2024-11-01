<?php

class VaocherAppApi
{
    public static function updateIntegrationConnectData()
    {
        $response = self::request('/integrations/wordpress', 'POST', [
            'data' => [
                'name' => get_bloginfo('name'),
                'url' => get_bloginfo('wpurl'),
                'php_version' => phpversion(),
                'wordpress_version' => get_bloginfo('version'),
                'woocommerce_version' => VaocherAppWoocommerce::getInstalledVersion(),
                'plugin_version' => VaocherApp::getInstance()->getVersion(),
                'language' => get_bloginfo('language'),
                'ping_url' => get_bloginfo('pingback_url'),
            ],
        ]);

        return $response->success;
    }

    public static function notifyDisconnectWoocommerce()
    {
        return self::request('/integrations/wordpress/disconnect', 'POST');
    }

    /**
     * @return \VaocherAppAccount|null
     */
    public static function getAccountInfo()
    {
        $response = self::request('/accounts/my-account');

        if ($response->success) {
            return new VaocherAppAccount($response->body['account']);
        }

        return null;
    }

    /**
     * Find the voucher using the provided code.
     * Will return the voucher model as array if found, otherwise return null.
     * Note: The result will be cached in the same request. So you are free to call this method multiple times.
     *
     * @param  string  $code
     * @return \VaocherAppVoucher|null
     */
    public static function getGiftVoucherByCode($code)
    {
        if ($code) {
            $code = trim($code);
        }

        // If matched the one that we already cached, use it
        // This saves us from hitting the API for the same code over and over again
        if ($code && VaocherAppStorage::$giftVoucher && $code === VaocherAppStorage::$giftVoucher->code) {
            return VaocherAppStorage::$giftVoucher;
        }

        if ($code) {
            $response = self::request('/vouchers/'.rawurlencode($code));

            if ($response->success) {
                VaocherAppStorage::$giftVoucher = new VaocherAppVoucher($response->body);

                return VaocherAppStorage::$giftVoucher;
            }
        }

        return null;
    }

    /**
     * @param  string  $code
     * @param  float  $amount  This is the dollar amount as float, so "12.34" means "$12.34" dollars
     * @param  int|string|null  $orderId
     * @return \VaocherAppRedeemed|null
     */
    public static function redeemGiftVoucher($code, $amount, $orderId = null)
    {
        $giftVoucher = self::getGiftVoucherByCode($code);

        if (! $giftVoucher) {
            return null;
        }

        // Our system expect amount as smallest unit, so convert that to cents
        $amountAsCents = VaocherAppHelpers::fromDollarsToCents($amount);
        $voucherRemainingBalance = $giftVoucher->remain_balance;

        // Now these 2 values are in the same unit, we can compare them now
        if ($voucherRemainingBalance < $amountAsCents) {
            return null;
        }

        if (! $orderId) {
            $orderId = '(unknown)';
        }

        $reason = 'Redeemed against WordPress Woocommerce order #'.$orderId;

        $response = self::request('/vouchers/'.rawurlencode($code).'/redeem', 'POST', [
            'amount' => $amountAsCents,
            'reason' => $reason,
        ]);

        if ($response->success) {
            return (new VaocherAppRedeemed($response->body))->setRedeemedAmount($amountAsCents);
        }

        return null;
    }

    // /**
    //  * @return array|null
    //  */
    // public static function getAccount()
    // {
    //     $response = self::request('/accounts/my-account');
    //
    //     if ($response->success) {
    //         return $response->body;
    //     }
    //
    //     return null;
    // }

    // // Test curl request to see if its working
    // public static function testCurlRequest()
    // {
    //     if (! function_exists('curl_version')) {
    //         return [
    //             'message' => 'cURL not installed.',
    //             'status' => 'error',
    //         ];
    //     }
    //
    //     // Try to ping our API to see if can get a response
    //     $pingRequest = self::request('/ping');
    //     // Its working, all good
    //     if ($pingRequest->success) {
    //         return true;
    //     }
    //
    //     // Now, pinging our API did not work
    //     // We need to perform other tests to see what is causing the issue
    //     $args = [
    //         'timeout' => 10,
    //         'headers' => [
    //             'accept' => '*/*',
    //             'user-agent' => 'WordPress/VaocherApp-WordPress-Plugin',
    //         ],
    //     ];
    //
    //     // Perform TLS check via https://www.howsmyssl.com/a/check
    //     $tls12Enabled = false;
    //     $tls12Response = new VaocherAppResponse(wp_remote_get('https://www.howsmyssl.com/a/check', $args));
    //
    //     if ($tls12Response->success && isset($tls12Response['tls_version'])) {
    //         if ($tls12Response['tls_version'] === 'TLS 1.12' || $tls12Response['tls_version'] === 'TLS 1.13') {
    //             $tls12Enabled = true;
    //         } else {
    //             preg_match_all('![\d,\.]+!', $tls12Response['tls_version'], $matches);
    //             if (is_array($matches)) {
    //                 $tls12Enabled = (float) $matches[0][0] >= 1.2;
    //             }
    //         }
    //     }
    //
    //     // Must have TLS 1.2
    //     // If not, show error and stop
    //     if (! $tls12Enabled) {
    //         return [
    //             'message' => implode('<br>', [
    //                 'We cannot access the VaocherApp API to validate your API key because it appears that your server is incapable of making outbound cURL requests using TLS 1.2.',
    //                 'Please upgrading your PHP to version 5.5.19 or higher and cURL version 7.34.0 or higher/OpenSSL @ 1.0.1 or higher.<br>',
    //                 'There is a great plugin for testing your WordPress installation\'s capability here: <a href="https://wordpress.org/plugins/tls-1-2-compatibility-test/">https://wordpress.org/plugins/tls-1-2-compatibility-test/</a><br>',
    //                 '----------------------------<br>',
    //                 'Response code: '.$tls12Response->code,
    //                 'Response body: '.$tls12Response->renderableBody,
    //             ]),
    //             'status' => 'error',
    //         ];
    //     }
    //
    //     // If get to here, it means: TLS is working but cannot fire VaocherApp API requests
    //     // Dont know why ?!?
    //     return [
    //         'message' => implode('<br>', [
    //             'Your Wordpress instance is unable to make outbound cUrl requests to any external web service/API, including the VaocherApp API at https://api.vaocher.app',
    //             'How to resolve:',
    //             'Please review any WordPress security plugins and ensure they are configured to allow outbound cUrl requests and also review your webhost\'s security system/firewall settings to ensure that it is configured to allow your WordPress instance to send/receive on port 443.',
    //         ]),
    //         'status' => 'error',
    //     ];
    // }

    /**
     * @param  string  $endpoint
     * @param  string  $method
     * @param  array|null  $data
     * @return \VaocherAppResponse
     */
    public static function request($endpoint, $method = 'GET', $data = null)
    {
        $method = strtoupper($method);
        $url = esc_url_raw(VaocherAppUrl::toApi($endpoint));
        $json = null;

        if ($data !== null) {
            $json = json_encode($data, JSON_FORCE_OBJECT);

            if ($json === null) {
                $json = '{ "error": "Could not serialize data into JSON" }';
            }
        }

        $pluginVersion = VaocherApp::getInstance()->getVersion();
        $woocommerceVersion = VaocherAppWoocommerce::getInstalledVersion();
        $phpVersion = phpversion();
        global $wp_version;

        if ($pluginVersion === null || strlen($pluginVersion) <= 0) {
            $pluginVersion = 'unknown';
        }
        if ($phpVersion === null || strlen($phpVersion) <= 0) {
            $phpVersion = 'unknown';
        }
        if ($wp_version === null || strlen($wp_version) <= 0) {
            $wp_version = 'unknown';
        }
        if ($woocommerceVersion === null || strlen($woocommerceVersion) <= 0) {
            $woocommerceVersion = 'unknown';
        }

        $args = [
            'timeout' => 30,
            'body' => $json,
            'headers' => [
                'content-type' => 'application/json',
                'accept' => 'application/json',
                'user-agent' => 'WordPress/VaocherApp-WordPress-Plugin',
                'Authorization' => 'Bearer '.VaocherAppData::getApiKey(),
                'x-vaocherapp-integration' => 1, // Must set this header key for internal data management
                'x-vaocherapp-test-mode' => VaocherAppData::isWoocommerceInTestMode() ? 1 : 0,
                'x-vaocherapp-wordpress-plugin-version' => $pluginVersion,
                'x-vaocherapp-wordpress-php-version' => $phpVersion,
                'x-vaocherapp-wordpress-version' => $wp_version,
                'x-vaocherapp-woocommerce-version' => $woocommerceVersion,
            ],
        ];

        if ($method === 'GET') {
            $response = wp_remote_get($url, $args);
        } elseif ($method === 'POST') {
            $response = wp_remote_post($url, $args);
        } else {
            $args['method'] = $method;
            $response = wp_remote_request($url, $args);
        }

        if (is_wp_error($response)) {
            $error = $response->get_error_message();

            echo '<div id="message" class="notice notice-error">';
            echo '<p>';
            echo '<strong>';
            echo 'Error integrating with your VaocherApp account.<br>';
            echo esc_html($error);
            echo '<br>';
            if (strpos($error, 'tls') !== false) {
                echo '<br>The VaocherApp plugin requires that your PHP version is 5.6+ and cURL supports TLS1.2.<br>';
                echo 'Please conduct a TLS 1.2 Compatibility Test via <a href="https://wordpress.org/plugins/tls-1-2-compatibility-test/" target="_blank">this plugin</a>';
            }
            echo '</strong>';
            echo '</p>';
            echo '</div>';
        }

        VaocherAppLogger::write('Request response', [
            'method' => $method,
            'endpoint' => $endpoint,
            'arguments' => $args,
            'response' => $response,
            'error' => isset($error) ? $error : null,
        ]);

        return new VaocherAppResponse($response);
    }
}

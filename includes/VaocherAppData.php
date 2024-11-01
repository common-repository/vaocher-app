<?php

class VaocherAppData
{
    const OPTION_PREFIX = 'vaocherapp_';

    public static function hasApiKey()
    {
        if (self::getApiKey()) {
            return true;
        }

        return false;
    }

    public static function getAccountId()
    {
        return self::getOption('account_id');
    }

    public static function setAccountId($value)
    {
        return self::updateOption('account_id', $value);
    }

    public static function getApiKey()
    {
        return self::getOption('api_key');
    }

    public static function setApiKey($value)
    {
        return self::updateOption('api_key', $value);
    }

    public static function setIntegrationVersion($version)
    {
        return self::updateOption('integration_version', $version);
    }

    public static function getIntegrationVersion()
    {
        return self::getOption('integration_version');
    }

    public static function isWoocommerceEnabled()
    {
        return self::getOption('woocommerce_enabled');
    }

    public static function setWoocommerceAsEnabled($value)
    {
        return self::updateOption('woocommerce_enabled', $value);
    }

    // public static function getWoocommerceApplyToShipping()
    // {
    //     return self::getOption('woocommerce_apply_to_shipping');
    // }
    //
    // public static function setWoocommerceApplyToShipping($value)
    // {
    //     return self::updateOption('woocommerce_apply_to_shipping', $value);
    // }
    //
    // public static function getWoocommerceApplyToTaxes()
    // {
    //     return self::getOption('woocommerce_apply_to_taxes');
    // }
    //
    // public static function setWoocommerceApplyToTaxes($value)
    // {
    //     return self::updateOption('woocommerce_apply_to_taxes', $value);
    // }

    public static function isWoocommerceInTestMode()
    {
        return current_user_can('administrator') && self::isWoocommerceTestModeCookieSet();
    }

    /**
     * Get the cookie key to indicate test mode.
     *
     * @return string
     */
    public static function getWoocommerceTestModeCookieKey()
    {
        return self::OPTION_PREFIX.'test_mode';
    }

    public static function isWoocommerceTestModeCookieSet()
    {
        $testModeKey = self::getWoocommerceTestModeCookieKey();

        return isset($_COOKIE[$testModeKey]) && $_COOKIE[$testModeKey] === '1';
    }

    public static function isDiagnosticsModeEnabled()
    {
        $diagnosticsModeKey = self::OPTION_PREFIX.'diagnostics_mode';

        return isset($_COOKIE[$diagnosticsModeKey]) && $_COOKIE[$diagnosticsModeKey] === '1';
    }

    public static function enableDiagnosticsMode($enabled = true)
    {
        $diagnosticsModeKey = self::OPTION_PREFIX.'diagnostics_mode';

        if ($enabled) {
            setcookie($diagnosticsModeKey, '1', time() + (60 * 60), '/');
        } else {
            setcookie($diagnosticsModeKey, '', time() - 1, '/');
        }
    }

    public static function getOption($key, $default = false)
    {
        return get_option(self::OPTION_PREFIX.$key, $default);
    }

    public static function updateOption($key, $value)
    {
        return update_option(self::OPTION_PREFIX.$key, $value);
    }

    public static function deleteOption($key)
    {
        delete_option(self::OPTION_PREFIX.$key);
    }
}
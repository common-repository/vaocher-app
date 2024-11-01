<?php

class VaocherAppUrl
{
    public static function toWordpressSettingPage()
    {
        return site_url('/wp-admin/options-general.php?page='.VaocherApp::getInstance()->getSettingPageName());
    }

    public static function toBackendApp($path = null)
    {
        $url = self::isProduction()
            ? 'https://vaocher.app'
            : 'http://localhost:3000';

        return self::concatUrlAndPath($url, $path);
    }

    public static function toLandingPage($path = null)
    {
        $url = 'https://vaocherapp.com';

        return self::concatUrlAndPath($url, $path);
    }

    public static function toApi($path = null)
    {
        $url = self::isProduction()
            ? 'https://api.vaocher.app/v1'
            : 'http://localhost:8000/v1';

        return self::concatUrlAndPath($url, $path);
    }

    public static function embedScript()
    {
        $url = self::isProduction()
            ? 'https://vaocher.app/resources/embed/embed.js'
            : 'http://localhost:3000/resources/embed/embed.js';

        return $url.'?'.time();
    }

    private static function isProduction()
    {
        return VaocherApp::getInstance()->isEnvProduction();
    }

    private static function normalizePath($path = null)
    {
        if ($path) {
            $path = ltrim($path, '/');
        }

        return $path;
    }

    private static function concatUrlAndPath($url, $path = null)
    {
        if ($path) {
            $url .= '/';
            $url .= self::normalizePath($path);
        }

        return $url;
    }
}

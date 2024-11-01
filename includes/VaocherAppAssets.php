<?php

class VaocherAppAssets
{
    public static function load($path)
    {
        $path = ltrim($path, '/');

        return plugins_url(VaocherApp::getInstance()->getPluginName().'/'.$path);
    }
}
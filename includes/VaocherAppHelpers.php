<?php

class VaocherAppHelpers
{
    /**
     * @param  string  $haystack
     * @param  string  $needle
     * @return bool
     */
    public static function strStartsWith($haystack, $needle)
    {
        $length = strlen($needle);

        return substr($haystack, 0, $length) === $needle;
    }

    /**
     * @param  string  $haystack
     * @param  string  $needle
     * @return bool
     */
    public static function strEndsWith($haystack, $needle)
    {
        $length = strlen($needle);

        if (! $length) {
            return true;
        }

        return substr($haystack, -$length) === $needle;
    }

    /**
     * Render a JS code to perform a redirect.
     * This is useful if your cannot use PHP to redirect due to headers already been sent.
     *
     * @param  string  $url
     * @return string
     */
    public static function redirectViaJavascript($url)
    {
        ob_start();
        ?>
        <script type="text/javascript">
            window.location.replace('<?php echo esc_js($url) ?>')
        </script>
        <?php
        return ob_get_clean();
    }

    /**
     * @param  float|int  $dollars
     * @return int
     */
    public static function fromDollarsToCents($dollars)
    {
        return round($dollars, 2, PHP_ROUND_HALF_DOWN) * 100;
    }

    /**
     * @param  float|int  $cents
     * @return float
     */
    public static function fromCentsToDollars($cents)
    {
        return round($cents / 100, 2, PHP_ROUND_HALF_DOWN);
    }

    /**
     * Generate an external URL and add "ref" into it.
     *
     * @param  string  $url
     * @return string
     */
    public static function externalUrl($url)
    {
        return sprintf('%s?ref=%s', $url, 'vaocherapp-wordpress-plugin');
    }

    public static function dd()
    {
        var_dump(func_get_args());
        die();
    }

    // /**
    //  * @param  array  $data
    //  * @param  string  $key
    //  * @param  null  $default
    //  * @return mixed|null
    //  */
    // public static function arrayGet($data, $key, $default = null)
    // {
    //     $value = $default;
    //
    //     if (is_array($data) && array_key_exists($key, $data)) {
    //         $value = $data[$key];
    //     } elseif (is_object($data) && property_exists($data, $key)) {
    //         $value = $data->$key;
    //     } else {
    //         $segments = explode('.', $key);
    //         foreach ($segments as $segment) {
    //             if (is_array($data) && array_key_exists($segment, $data)) {
    //                 $value = $data = $data[$segment];
    //             } elseif (is_object($data) && property_exists($data, $segment)) {
    //                 $value = $data = $data->$segment;
    //             } else {
    //                 $value = $default;
    //                 break;
    //             }
    //         }
    //     }
    //
    //     return $value;
    // }
}

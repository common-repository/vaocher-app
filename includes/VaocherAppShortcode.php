<?php

class VaocherAppShortcode
{
    // This will be [vaocherapp]
    const SHORTCODE_NAME = 'vaocherapp';

    public static function register()
    {
        add_shortcode(static::SHORTCODE_NAME, self::handleVaocherapp());
    }

    public static function unregister()
    {
        remove_shortcode(static::SHORTCODE_NAME);
    }

    // Render the [vaocherapp] shortcode
    public static function handleVaocherapp()
    {
        return function ($params) {
            $attributes = shortcode_atts([
                'site-id' => '',
                'items' => '',
                'embed-mode' => 'full',
                'test-mode' => '0',
                'hide-navigation-menu' => '',
                'host' => '',
            ], $params);

            if (! $attributes['site-id']) {
                $attributes['site-id'] = VaocherAppData::getAccountId();
            }

            // "account" is the UUID, must be exact 36 chars
            if (strlen($attributes['site-id']) !== 36) {
                return '<p>Notice to site admin: Please connect your VaocherApp account to WordPress in Settings -> VaocherApp</p>';
            }

            ob_start();
            ?>
            <div
                class="vaocher-form-container"
                data-platform="Wordpress"
                <?php foreach ($attributes as $attrKey => $attrValue) : ?>
                    <?php if ($attrKey === 'items' && empty($attrValue)) : ?>
                        <?php continue; ?>
                    <?php elseif ($attrKey === 'hide-navigation-menu' && empty($attrValue)) : ?>
                        <?php continue; ?>
                    <?php elseif ($attrKey === 'host' && VaocherApp::getInstance()->isEnvProduction()) : ?>
                        <?php continue; ?>
                    <?php endif; ?>
                    data-<?php echo esc_attr($attrKey) ?>="<?php echo esc_attr($attrValue) ?>"
                <?php endforeach; ?>
            ></div>
            <script type="text/javascript">!function (e) {
                    var t = e.createElement('script');
                    t.setAttribute('src', '<?php echo VaocherAppUrl::embedScript(); ?>'), e.head.appendChild(t)
                }(document);</script>
            <?php
            return ob_get_clean();
        };
    }
}
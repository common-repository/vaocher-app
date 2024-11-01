<?php
/**
 * @var \VaocherAppAccount $accountInfo
 * @var bool $woocommerceIsActivated
 * @var bool $woocommerceVersionCompatible
 * @var bool $woocommerceEnabled
 * @var bool $woocommerceCanEnableTestMode
 * @var bool $woocommerceIsInTestMode
 * @var string $woocommerceCurrency
 * @var bool $diagnosticsModeEnabled
 */
?>
<h3>
    <?php echo __('WooCommerce Integration', 'vaocher-app'); ?>
    <?php if (! $woocommerceIsActivated || ! $woocommerceVersionCompatible): ?>
        <small class="va--text--danger">(<?php echo __('Not available', 'vaocher-app'); ?>)</small>
    <?php elseif ($woocommerceEnabled): ?>
        <small class="va--text--success">(<?php echo __('Enabled', 'vaocher-app'); ?>)</small>
    <?php else: ?>
        <small class="va--text--warning">(<?php echo __('Disabled', 'vaocher-app'); ?>)</small>
    <?php endif; ?>
</h3>

<p>
    <?php echo __('Enable VaocherApp & WooCommerce integration to allow your customers to redeem gift vouchers in WooCommerce cart. This means that when you sell a gift voucher, your customers can use it directly in your WordPress WooCommerce shopping cart.', 'vaocher-app'); ?>
    <a href="<?php echo VaocherAppHelpers::externalUrl('https://www.vaocherapp.com/docs/wordpress-integration') ?>" target="_blank"><?php echo __('Learn more', 'vaocher-app'); ?>...</a>
</p>

<?php if (! $woocommerceIsActivated): ?>
    <p class="va--text--danger">
        <span class="dashicons dashicons-warning"></span>
        <?php echo __('You need to have the WooCommerce plugin installed and activated in order to enable this integration.', 'vaocher-app'); ?>
    </p>
<?php elseif (! $woocommerceVersionCompatible): ?>
    <p class="va--text--danger">
        <span class="dashicons dashicons-warning"></span>
        <?php echo __('You need to install WooCommerce plugin v3 or above in order to enable this integration.', 'vaocher-app'); ?>
    </p>
<?php elseif (strtolower($accountInfo->currency) !== strtolower($woocommerceCurrency)): ?>
    <p class="va--text--danger">
        <span class="dashicons dashicons-warning"></span>
        <?php echo sprintf(
            __('Your WooCommerce currency (%s) needs to match your VaocherApp account currency (%s) in order to enable this integration.', 'vaocher-app'),
            __($woocommerceCurrency),
            __($accountInfo->currency)
        ); ?>
    </p>
<?php else: ?>
<form
    class="form-table"
    method="POST"
    action="<?php echo $_SERVER['REQUEST_URI']; ?>"
>
    <input
        type="hidden"
        name="vaocherapp_woocommerce_settings"
        value="1"
    >

    <p>
        <input
            type="checkbox"
            <?php echo $woocommerceEnabled ? 'checked' : ''; ?>
            name="vaocherapp_woocommerce_enabled"
            id="vaocherapp_woocommerce_enabled_input"
            value="1"
        />
        <label
            for="vaocherapp_woocommerce_enabled_input">
            <?php echo __('Enable WooCommerce integration', 'vaocher-app'); ?>
        </label>
    </p>

    <?php if ($woocommerceCanEnableTestMode): ?>
    <div
        id="vaocherapp-woocommerce-sub-settings-section"
        style="display: <?php echo $woocommerceEnabled ? 'block' : 'none'; ?>;"
    >
        <hr style="margin: 32px 0;" />

        <h4 class="va--subheading">
            <?php echo __('Test Mode', 'vaocher-app'); ?>
        </h4>
        <p>
            <?php echo sprintf(
                __('To avoid WordPress plugin and theme conflicts (%s), it\'s highly recommended that you enable test mode and place a test order in your VaocherApp, and then try to redeem that test voucher in your WordPress shopping cart to make sure everything works as expected.', 'vaocher-app'),
                '<a href="https://woocommerce.com/document/how-to-test-for-conflicts/" target="_blank">'.__('read more', 'vaocher-app').'<span style="font-size: 13px; line-height: 12px; height: 13px; width: 13px; vertical-align: middle; text-decoration: none; margin-left: 2px;" class="dashicons dashicons-external"></span></a>'
            ); ?>
            <a
                href="<?php echo VaocherAppUrl::toLandingPage('/docs/how-to-enable-test-mode-and-place-test-orders/'); ?>"
                target="_blank"
            ><?php echo __('Learn more', 'vaocher-app'); ?>...</a>
        </p>
        <p>
            <input
                type="checkbox"
                value="1"
                <?php echo $woocommerceIsInTestMode ? 'checked' : ''; ?>
                name="vaocherapp_woocommerce_test_mode"
                id="vaocherapp_woocommerce_test_mode_input"
            />
            <label for="vaocherapp_woocommerce_test_mode_input"><?php echo __('Enable test mode', 'vaocher-app'); ?></label>
        </p>

        <p class="va--section-helper">
            <?php if ($woocommerceIsInTestMode): ?>
                <?php echo __('Note: Test mode is only enabled for you, whilst you are logged in as a WordPress admin and will reset automatically in 1 hour.', 'vaocher-app'); ?>
            <?php else: ?>
                <?php echo __('Note: Test mode only applies to you for 1 hour.', 'vaocher-app'); ?>
            <?php endif; ?>
        </p>

        <?php if ($woocommerceIsInTestMode): ?>
            <p>
                <span style="color: darkorange">
                    <span class="dashicons dashicons-warning"></span>
                    <?php echo __('You have enabled test mode. You can now use a test gift voucher in your WooCommerce cart to experience the redemption process.', 'vaocher-app'); ?>
                </span>
            </p>
        <?php endif; ?>

        <hr style="margin: 32px 0;" />

        <h4 class="va--subheading">
            <?php echo __('Diagnostics Mode', 'vaocher-app'); ?>
        </h4>
        <p><?php echo sprintf(
                __('If you are experiencing issues (e.g. gift voucher balance is not applied to your cart), enable "Diagnostics Mode" and follow <a href="%s" target="_blank">this instruction</a>', 'vaocher-app'),
                VaocherAppHelpers::externalUrl('https://www.vaocherapp.com/docs/wordpress-integration')
            ); ?>.</p>
        <p>
            <input
                type="checkbox"
                value="1"
                <?php echo $diagnosticsModeEnabled ? 'checked' : ''; ?>
                name="vaocherapp_woocommerce_diagnostics_mode"
                id="vaocherapp_woocommerce_diagnostics_mode_input"
            />
            <label for="vaocherapp_woocommerce_diagnostics_mode_input"><?php echo __('Enable diagnostics mode', 'vaocher-app'); ?></label>
        </p>
        <p class="va--section-helper">
            <?php if ($diagnosticsModeEnabled): ?>
                <?php echo __('Note: Diagnostics mode is only enabled for you, whilst you are logged in and will reset automatically in 1 hour.', 'vaocher-app'); ?>
            <?php else: ?>
                <?php echo __('Note: Diagnostics mode only applies to you for 1 hour.', 'vaocher-app'); ?>
            <?php endif; ?>
        </p>
    </div>
    <?php endif; ?>

    <p style="margin-top: 2rem;">
        <button
            class="button"
            type="submit"
        >
            <?php _e('Save changes') ?>
        </button>
    </p>
</form>
<?php endif; ?>

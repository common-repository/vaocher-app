<?php
/**
 * @var \VaocherAppAccount $accountInfo
 */
?>
<div class="notice notice-warning" style="margin-top: 16px; margin-bottom: 16px;">
    <p>
        <?php echo sprintf(
            __('You\'ve successfully connected your %s VaocherApp account to WordPress site. However, you need to complete the account setup first before start selling gift vouchers online.', 'vaocher-app'),
            '<span style="font-weight: bold;">'.$accountInfo->name.'</span>'
        ); ?>
    </p>
    <p>
        <a
            href="<?php echo VaocherAppUrl::toBackendApp('/setup') ?>"
            target="_blank"
            class="button button-primary"
            style="text-transform: uppercase;"
        >
            <?php echo __('Continue account setup', 'vaocher-app'); ?>
            <span
                style="font-size: 15px; line-height: 12px; height: 15px; width: 15px; vertical-align: middle;"
                class="dashicons dashicons-external"
            ></span>
        </a>
    </p>
</div>
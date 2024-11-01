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
<div class="wrap">
    <div style="text-align: center;">
        <?php echo VaocherAppView::includePartialView('va_logo') ?>
    </div>

    <div class="va--section">
        <h1 style="text-transform: capitalize;"><?php echo __('VaocherApp account connected', 'vaocher-app'); ?></h1>

        <?php if (! $accountInfo->onboarding['is_completed']) : ?>
            <?php echo VaocherAppView::includePartialView('onboarding_warning', [
                'accountInfo' => $accountInfo,
            ]) ?>
        <?php endif; ?>
    </div>

    <div class="va--section">
        <?php echo VaocherAppView::includePartialView('woocommerce_settings', [
            'accountInfo' => $accountInfo,
            'woocommerceIsActivated' => $woocommerceIsActivated,
            'woocommerceVersionCompatible' => $woocommerceVersionCompatible,
            'woocommerceEnabled' => $woocommerceEnabled,
            'woocommerceCanEnableTestMode' => $woocommerceCanEnableTestMode,
            'woocommerceIsInTestMode' => $woocommerceIsInTestMode,
            'woocommerceCurrency' => $woocommerceCurrency,
            'diagnosticsModeEnabled' => $diagnosticsModeEnabled,
        ]) ?>
    </div>

    <div class="va--section">
        <?php echo VaocherAppView::includePartialView('disconnect', [
            'accountInfo' => $accountInfo,
        ]) ?>
    </div>

    <?php echo VaocherAppView::includePartialView('help_center') ?>
</div>
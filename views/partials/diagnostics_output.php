<?php
/**
 * @var string $wordpressVersion
 * @var string $woocommerceVersion
 * @var bool $woocommerceIsEnabled
 * @var array $messages
 */
?>
<div style="border: 5px solid #fdcb6e; color: #fea500; font-size: 14px; line-height: 1.6; padding: 2rem; background-color: #ffeaa7;">
    <h6 style="color: #fea500; font-size: 18px; font-weight: bold; text-decoration: underline; margin: 0 0 12px 0; padding: 0;">
        <?php echo __('VaocherApp Diagnostics', 'vaocher-app'); ?>
    </h6>
    <div>
        VaocherApp plugin version: <?php echo VaocherApp::getInstance()->getVersion(); ?><br>
        WordPress version: <?php echo esc_html($wordpressVersion); ?><br>
        PHP version: <?php echo esc_html(phpversion()); ?><br>
        WooCommerce version: <?php echo esc_html($woocommerceVersion); ?><br>
        <?php if ($woocommerceIsEnabled) : ?>
            WooCommerce integration enabled<br>
            WooCommerce test mode: <?php echo VaocherAppData::isWoocommerceInTestMode() ? 'Yes' : 'No'; ?><br>
        <?php else : ?>
            WooCommerce integration NOT enabled<br>
        <?php endif; ?>

        <?php if ($messages) : ?>
            <hr style="background-color: #fea500; margin: 16px 0;">
            <?php foreach ($messages as $message) : ?>
                <?php echo esc_html($message); ?><br>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php
/**
 * @var string $connectUrl
 */
?>
<div class="wrap">
    <div style="text-align: center;">
        <?php echo VaocherAppView::includePartialView('va_logo') ?>
    </div>

    <div class="va--section">
        <h1><?php echo __('Connect to VaocherApp', 'vaocher-app'); ?></h1>

        <p><?php echo __('In order to sell gift vouchers on your WordPress website, you need to have a VaocherApp account connected to your Wordpress website.', 'vaocher-app'); ?></p>

        <br>

        <div class="va--layout-cols-2">
            <div class="va--layout-col">
                <div class="va--section--panel va--connecting-panel">
                    <h3><?php echo __('Don\'t have an account yet?', 'vaocher-app'); ?></h3>
                    <a
                        href="<?php echo VaocherAppUrl::toBackendApp('/register') ?>"
                        target="_blank"
                        style="display: inline-block;"
                    >
                        <button
                            type="button"
                            class="button"
                        >
                            <?php echo __('Create New Account', 'vaocher-app'); ?>
                        </button>
                    </a>
                </div>
            </div>
            <div class="va--layout-col">
                <div class="va--section--panel va--connecting-panel">
                    <h3><?php echo __('Already have an account?', 'vaocher-app'); ?></h3>
                    <a
                        href="<?php echo $connectUrl; ?>"
                        style="display: inline-block;"
                    >
                        <button
                            type="button"
                            class="button button-primary"
                        >
                            <?php echo __('Connect to VaocherApp', 'vaocher-app'); ?>
                        </button>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <?php echo VaocherAppView::includePartialView('help_center') ?>
</div>
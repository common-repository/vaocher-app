<?php
/**
 * @var \VaocherAppAccount $accountInfo
 */
?>
<h3><?php echo __('Disconnect VaocherApp', 'vaocher-app'); ?></h3>

<p>
    <?php echo sprintf(
        __('This will disconnect your VaocherApp account (%s) from WordPress, meaning you will no longer be able to sell gift cards online.', 'vaocher-app'),
        $accountInfo->name
    ); ?>
</p>

<form
    id="vaocherapp-disconnect-form"
    class="form-table"
    method="POST"
    action="<?php echo $_SERVER['REQUEST_URI']; ?>"
    onsubmit="if (confirm('<?php echo __('Disconnect VaocherApp from wordpress? This means you will no longer be able to sell gift vouchers on your website.', 'vaocher-app'); ?>')) { return true; } else { return false; }"
>
    <input
        type="hidden"
        name="disconnect_request"
        value="1"
    />
    <p>
        <button
            class="button"
            type="submit"
            value=""
            style="color: #dc3232; border-color: #dc3232"
        >
            <?php echo __('Disconnect', 'vaocher-app'); ?>
        </button>
    </p>
</form>
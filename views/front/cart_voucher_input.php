<?php
/**
 * @var string $ariaLabel
 * @var string $enteredCode
 * @var string $appliedCode
 * @var string $errorMessage
 * @var \VaocherAppVoucher $appliedGiftVoucher
 * @var bool $showApplyButton
 * @var bool $showApplyInput
 */
?>
<tr class="cart-subtotal vaocherapp-cart-subtotal">
    <th class="vaocherapp-cart-subtotal-th">
        <div style="display: flex;">
            <div style="white-space: nowrap;">
                <?php echo __('Gift voucher', 'vaocher-app') ?>
            </div>

            <?php if (! empty($appliedCode)) : ?>
                <div style="margin-right: 4px;">:</div>
                <div
                    class="vaocherapp-cart-subtotal-th-balance-title"
                    style="text-transform: uppercase;"
                >
                    <?php echo $appliedCode ?>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($appliedCode) : ?>
            <div
                class="vaocherapp-cart-subtotal-th-balance-container"
                style="font-size: 12px; font-weight: 300;"
            >
                <div class="vaocherapp-cart-subtotal-th-balance-value">
                    <?php echo __('Balance', 'vaocher-app') ?>: <?php echo wc_price($appliedGiftVoucher->remain_balance / 100) ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if (VaocherAppData::isWoocommerceInTestMode()) : ?>
            <div style="color: #ffb837;">(TEST MODE)</div>
        <?php endif; ?>
    </th>
    <td
        data-title="<?php echo $ariaLabel ?>"
        class="vaocherapp-cart-subtotal-td"
    >
        <?php if (! $appliedCode) : ?>
            <a
                href="#"
                class="vaocherapp-cart-subtotal-td-apply-voucher"
                style="<?php echo $showApplyButton ? 'display: inline-block;' : 'display: none;'; ?>"
                onclick="this.style.display='none'; document.getElementById('vaocherapp-apply-voucher-form').style.display='flex'; document.getElementById('gift_voucher_code_input').focus(); return false;"
            ><?php echo __('Apply gift voucher', 'vaocher-app') ?></a>
            <div
                id="vaocherapp-apply-voucher-form"
                class="vaocherapp-cart-subtotal-td-form"
                style="<?php echo $showApplyInput ? 'display: flex;' : 'display: none;'; ?>"
            >
                <input
                    class="vaocherapp-cart-subtotal-td-form-input input-text"
                    type="text"
                    id="gift_voucher_code_input"
                    value="<?php echo $enteredCode ?>"
                    placeholder="<?php echo __('Gift voucher code', 'vaocher-app') ?>"
                    onkeypress="return vaocherapp_code_keypress()"
                    style="width: 100%; margin: 0; min-width: 100px;"
                >
                <button
                    class="vaocherapp-cart-subtotal-td-form-button"
                    type="button"
                    class="button"
                    name="vaocherapp_giftcard_button"
                    value="<?php echo __('Apply gift card', 'vaocher-app') ?>"
                    style="white-space: nowrap; margin: 0;"
                    onclick="vaocherapp_submit_code()"
                ><?php echo __('Apply', 'vaocher-app') ?></button>
            </div>

            <?php if (isset($errorMessage) && $errorMessage) : ?>
                <ul
                    class="woocommerce-error vaocherapp-cart-subtotal-error"
                    style="margin-top: 16px; margin-bottom: 16px; font-size: 14px;"
                    role="alert"
                >
                    <li><?php echo esc_html($errorMessage); ?></li>
                </ul>
            <?php endif; ?>


        <?php else : ?>
            <div
                class="woocommerce-price-amount amount vaocherapp-cart-subtotal-td-applied-balance"
                style="font-weight: normal;"
            >
                <div>-<?php echo wc_price(VaocherAppStorage::$appliedGiftVoucherBalance) ?> [<a
                        href="#"
                        onclick="vaocherapp_set_code(''); return false;"
                    ><?php echo __('Remove', 'vaocher-app') ?></a>]
                </div>
            </div>
        <?php endif; ?>
    </td>
</tr>
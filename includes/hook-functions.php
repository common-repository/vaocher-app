<?php

function vaocherapp_plugin_activated()
{

}

function vaocherapp_plugin_deactivated()
{

}

function vaocherapp_plugin_uninstalled()
{
    VaocherAppHandlers::handleDisconnectRequest();
    VaocherAppShortcode::unregister();
}

function vaocherapp_woocommerce_private_form()
{
    ?>
    <form id="vaocherapp_apply_remove_code" style="display: none;" method="POST">
        <input
            type="hidden"
            name="vaocherapp_gift_voucher_code"
            id="vaocherapp_gift_card_code_input"
            value="<?php echo VaocherAppStorage::getGiftVoucherCode() ?>"
        >
    </form>
    <script>
        function vaocherapp_set_code(code) {
            var form = document.getElementById('vaocherapp_apply_remove_code');
            var input = document.getElementById('vaocherapp_gift_card_code_input');
            input.value = code;
            form.submit();
        }

        function vaocherapp_submit_code() {
            vaocherapp_set_code(document.getElementById('gift_voucher_code_input').value);
        }

        function vaocherapp_code_keypress(e) {
            var characterCode;

            if (e && e.which) {
                characterCode = e.which;
            } else {
                characterCode = event.keyCode;
            }

            if (characterCode == 13) {
                vaocherapp_submit_code();
                return false;
            } else {
                return true;
            }
        }
    </script>
    <?php
}

/**
 * Render the HTML to cart page so the users can enter gift voucher code.
 * This hook can be reused in the payment page as well.
 */
function vaocherapp_woocommerce_cart_apply_gift_voucher()
{
    $enteredCode = $_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vaocherapp_gift_voucher_code'])
        ? sanitize_text_field($_POST['vaocherapp_gift_voucher_code'])
        : null;
    $appliedCode = VaocherAppStorage::getGiftVoucherCode();
    $appliedGiftVoucher = VaocherAppApi::getGiftVoucherByCode($appliedCode);
    $showApplyButton = true;
    $showApplyInput = false;
    $errorMessage = null;

    if ($appliedCode && (! $appliedGiftVoucher || $appliedGiftVoucher->isExpired() || $appliedGiftVoucher->remain_balance <= 0)) {
        $appliedCode = null;
        $showApplyButton = false;
        $showApplyInput = true;

        if (! $appliedGiftVoucher) {
            $errorMessage = __('Gift voucher is no longer valid', 'vaocher-app');
        } elseif ($appliedGiftVoucher->isExpired()) {
            $errorMessage = __('Gift voucher is no longer valid', 'vaocher-app');
        } elseif ($appliedGiftVoucher->remain_balance <= 0) {
            $errorMessage = __('Gift voucher has no remaining balance', 'vaocher-app');
        }

        VaocherAppStorage::setGiftVoucherCode(null);
    } elseif ($enteredCode && ! $appliedCode) {
        $showApplyButton = false;
        $showApplyInput = true;
        $errorMessage = __('Gift voucher not found', 'vaocher-app');
        $appliedCode = null;
        VaocherAppStorage::setGiftVoucherCode(null);
    }

    $ariaLabel = __('Gift voucher', 'vaocher-app');

    if (! empty($appliedCode)) {
        $ariaLabel .= ' ('.$appliedCode.')';
    }

    echo VaocherAppView::renderView('front/cart_voucher_input', [
        'enteredCode' => $enteredCode,
        'appliedCode' => $appliedCode,
        'appliedGiftVoucher' => $appliedGiftVoucher,
        'ariaLabel' => $ariaLabel,
        'errorMessage' => $errorMessage,
        'showApplyButton' => $showApplyButton,
        'showApplyInput' => $showApplyInput,
    ]);

    if (! VaocherAppData::isWoocommerceInTestMode() && VaocherAppData::isWoocommerceTestModeCookieSet()) {
        echo VaocherAppView::renderView('front/cart_login_required_warning');
    }
}

/**
 * @param  \WC_Cart  $cart
 */
function vaocherapp_woocommerce_after_calculate_totals($cart)
{
    $debug = VaocherAppData::isDiagnosticsModeEnabled();
    $vaDiagnostics = VaocherAppDiagnostics::getInstance();
    $vaDiagnostics->setMessage('Handling "vaocherapp_woocommerce_after_calculate_totals" hook');

    // We dont support applying gift voucher to recurring cart, so stop
    if (property_exists($cart, 'recurring_cart_key')) {
        $vaDiagnostics->setMessage('&#8226; recurring_cart_key property exists, exiting.');

        return;
    }

    if ($debug) {
        $vaDiagnostics->setMessage('&#8226; Incoming cart sub-total: '.$cart->get_subtotal());
        $vaDiagnostics->setMessage('&#8226; Incoming cart shipping: '.$cart->get_shipping_total());
        $vaDiagnostics->setMessage('&#8226; Incoming cart taxes: '.$cart->get_total_tax());
        $vaDiagnostics->setMessage('&#8226; Incoming cart total: '.$cart->total);
    }

    if (is_cart() || is_checkout()) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['vaocherapp_gift_voucher_code'])) {
            $vaGiftVoucherCode = sanitize_text_field($_POST['vaocherapp_gift_voucher_code']);

            if ($vaGiftVoucherCode) {
                $vaDiagnostics->setMessage('&#8226; Setting gift voucher code to: '.$vaGiftVoucherCode);
            } else {
                $vaDiagnostics->setMessage('&#8226; Clearing gift voucher code');
            }

            VaocherAppStorage::setGiftVoucherCode($vaGiftVoucherCode);
        }
    }

    $enteredVoucherCode = VaocherAppStorage::getGiftVoucherCode();

    if (! $enteredVoucherCode) {
        $vaDiagnostics->setMessage('&#8226; No gift voucher code entered, exiting.');

        return;
    }

    $vaDiagnostics->setMessage('&#8226; Gift voucher code: '.$enteredVoucherCode);

    $enteredGiftVoucher = VaocherAppApi::getGiftVoucherByCode($enteredVoucherCode);

    if (! $enteredGiftVoucher) {
        $vaDiagnostics->setMessage('&#8226; Gift voucher not found in VaocherApp system, exiting.');

        return;
    }

    if ($enteredGiftVoucher->remain_balance <= 0) {
        $vaDiagnostics->setMessage('&#8226; Gift voucher has no balance, exiting.');

        return;
    }

    // If cart gets re-submitted, just re-use the pre-computed data
    if (property_exists($cart, 'vaocherapp_new_calculated_total')) {
        if ($debug) {
            $vaDiagnostics->setMessage('&#8226; Replaying compute request…');
            $vaDiagnostics->setMessage('&#8226; Previous incoming cart total: '.$cart->vaocherapp_previous_incoming_cart_total);
            $vaDiagnostics->setMessage('&#8226; Previous applied balance: '.$cart->vaocherapp_applied_gift_voucher_balance);
            $vaDiagnostics->setMessage('&#8226; Previous adjusted cart total: '.$cart->vaocherapp_new_calculated_total);
        }

        if (abs($cart->vaocherapp_new_calculated_total - $cart->total) < 0.00001) {
            $vaDiagnostics->setMessage('&#8226; Cart totals are the same: '.$cart->total.', exiting.');

            return;
        }

        $vaDiagnostics->setMessage('&#8226; Cart totals differ by: '.($cart->vaocherapp_new_calculated_total - $cart->total));
    }

    // Convert to dollar unit to line up with woocommerce currency
    $giftVoucherRemainingBalance = $enteredGiftVoucher->getRemainingBalanceAsDollars();
    $appliedVoucherBalance = min($cart->total, $giftVoucherRemainingBalance);

    $vaDiagnostics->setMessage('&#8226; Cart total: '.$cart->total);
    $vaDiagnostics->setMessage('&#8226; Gift voucher balance: '.$giftVoucherRemainingBalance);

    if ($cart->total < $appliedVoucherBalance) {
        $appliedVoucherBalance = $cart->total;
    }

    $afterAppliedVoucherBalanceCartTotal = $cart->total - $appliedVoucherBalance;

    $vaDiagnostics->setMessage('&#8226; Applied balance: '.$appliedVoucherBalance);
    $vaDiagnostics->setMessage('&#8226; New cart total: '.$afterAppliedVoucherBalanceCartTotal);

    // Remember these attributes to avoid re-applying the balance again if the cart/checkout page gets re-submitted
    $cart->vaocherapp_previous_incoming_cart_total = $cart->total;
    $cart->vaocherapp_new_calculated_total = $afterAppliedVoucherBalanceCartTotal;
    $cart->vaocherapp_applied_gift_voucher_balance = $appliedVoucherBalance;

    // Then update the cart total
    $cart->set_total($afterAppliedVoucherBalanceCartTotal);
    // WC uses session to remember the data, after updated the total, we need to update the sessions data again
    try {
        $cart->set_session();
    } catch (exception $e) {
        if ($debug) {
            $vaDiagnostics->setMessage('&#8226; Exception calling cart->set_session()');
            $vaDiagnostics->setMessage('&#8226; '.$e->getMessage());
        }
    }

    VaocherAppStorage::$appliedGiftVoucherBalance = $appliedVoucherBalance;

    $vaDiagnostics->setMessage('Finished handling "vaocherapp_woocommerce_after_calculate_totals"');
}

/**
 * @param $order
 */
function vaocherapp_woocommerce_checkout_create_order($order)
{
    $code = VaocherAppStorage::getGiftVoucherCode();
    $appliedBalance = VaocherAppStorage::$appliedGiftVoucherBalance;

    if ($code && $appliedBalance > 0) {
        $order->add_meta_data(VaocherApp::ORDER_META_VOUCHER_CODE, $code);
        $order->add_meta_data(VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE, $appliedBalance);
        $order->add_meta_data(VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE, 0);

        VaocherAppLogger::write('Added meta data into order', [
            VaocherApp::ORDER_META_VOUCHER_CODE => $code,
            VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE => $appliedBalance,
        ]);
    }
}

/**
 * Remove the gift card code from the checkout as soon as the order is placed.
 */
function vaocherapp_woocommerce_cart_emptied()
{
    VaocherAppStorage::setGiftVoucherCode(null);
}

/**
 * Deduct the balance from the gift voucher once 'payment has been made' (or for Cash On Delivery type payments, when it's set to 'processing').
 * This method is safe to repeatedly call.
 *
 * @param $orderId
 */
function vaocherapp_woocommerce_redeem_gift_voucher($orderId)
{
    $order = wc_get_order($orderId);

    if ($order->meta_exists(VaocherApp::ORDER_META_VOUCHER_CODE)) {
        $code = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_CODE);
        $appliedBalance = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE);
        $redeemedBalance = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE);
        $outstandingBalance = $appliedBalance - $redeemedBalance;

        VaocherAppLogger::write('Checking vaocherapp_woocommerce_redeem_gift_voucher', [
            VaocherApp::ORDER_META_VOUCHER_CODE => $code,
            VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE => $appliedBalance,
            VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE => $redeemedBalance,
            'outstanding_balance' => $outstandingBalance,
        ]);

        if ($outstandingBalance > 0) {
            VaocherAppLogger::write('Has outstanding balance, firing redeem request to VaocherApp', [
                'amount' => $outstandingBalance,
            ]);

            $justRedeemedValue = VaocherAppApi::redeemGiftVoucher($code, $outstandingBalance, $orderId);

            VaocherAppLogger::write('Redeemed voucher result', [
                'redeemed_result' => $justRedeemedValue,
            ]);

            if ($justRedeemedValue instanceof VaocherAppRedeemed && $justRedeemedValue->getRedeemedAmount() > 0) {
                VaocherAppLogger::write('Updating order meta after redeemed voucher', [
                    'just_redeemed_amount' => $justRedeemedValue->getRedeemedAmountAsDollars(),
                    VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE => $justRedeemedValue->getRedeemedAmountAsDollars() + $redeemedBalance,
                ]);

                $order->update_meta_data(VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE, $justRedeemedValue->getRedeemedAmountAsDollars() + $redeemedBalance);
                $order->add_order_note(sprintf(
                    '%s redeemed from gift voucher "%s"',
                    wc_price($justRedeemedValue->getRedeemedAmountAsDollars(), ['currency' => $order->get_currency()]),
                    strtoupper($code)
                ));
                $order->save();
            }

            vaocherapp_woocommerce_possibly_hold_order($orderId);
        }
    }
}

/**
 * If we've had an exceptional reason to put the order on hold.
 * This only really occurs when there either:
 * 1) Not enough balance on the gift card after all (i.e. bad actor)
 * 2) There was a transient API call failure
 *
 * @param $orderId
 */
function vaocherapp_woocommerce_possibly_hold_order($orderId)
{
    $order = wc_get_order($orderId);

    if ($order->meta_exists(VaocherApp::ORDER_META_VOUCHER_CODE)) {
        $code = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_CODE);
        $appliedBalance = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE);
        $redeemedBalance = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE);
        $outstandingBalance = $appliedBalance - $redeemedBalance;

        VaocherAppLogger::write('Checking vaocherapp_woocommerce_possibly_hold_order', [
            VaocherApp::ORDER_META_VOUCHER_CODE => $code,
            VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE => $appliedBalance,
            VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE => $redeemedBalance,
            'outstanding_balance' => $outstandingBalance,
        ]);

        if ($outstandingBalance > 0) {
            if ($redeemedBalance > 0) {
                $order->add_order_note(sprintf(
                    'Gift voucher "%s" was not redeemed correctly. Only %s was redeemed off the gift voucher. Please redeem a further %s from the gift voucher in VaocherApp',
                    strtoupper($code),
                    wc_price($redeemedBalance, ['currency' => $order->get_currency()]),
                    wc_price($outstandingBalance, ['currency' => $order->get_currency()])
                ));
                $order->save();
            } else {
                $order->add_order_note(sprintf(
                    'Gift voucher "%s" was not redeemed as the gift voucher does not have enough remaining balance at the point of payment.',
                    strtoupper($code)
                ));
                $order->save();
            }

            $status = $order->get_status();

            if ($status !== 'on-hold' && $status !== 'completed') {
                $order->set_status('on-hold');
                $order->save();
            }
        }
    }
}

/**
 * Rendering the gift card that has been used in an order in the admin order details page.
 * This is the admin view when viewing an order. It is not translated.
 *
 * @param $orderId
 */
function vaocherapp_woocommerce_admin_order_totals_after_tax($orderId)
{
    $order = wc_get_order($orderId);

    if ($order->meta_exists(VaocherApp::ORDER_META_VOUCHER_CODE)) {
        $code = trim($order->get_meta(VaocherApp::ORDER_META_VOUCHER_CODE));
        $requestedBalance = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE);
        $outstandingBalance = $requestedBalance;

        if ($order->meta_exists(VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE)) {
            $redeemedBalance = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_REDEEMED_BALANCE);
            $outstandingBalance = $requestedBalance - $redeemedBalance;
        }

        if ($requestedBalance > 0) {
            ?>
            <tr>
                <td class="label">
                    Gift voucher (<a
                        href="<?php echo VaocherAppUrl::toBackendApp('/o?c='.rawurlencode($code)); ?>"
                        target="_blank"
                        style="text-transform: uppercase"><?php echo $code; ?></a>):
                </td>
                <td width="1%"></td>
                <td class="total">
                    <?php echo wc_price(
                        -1 * $requestedBalance,
                        ['currency' => $order->get_currency()]
                    ); ?>
                </td>
            </tr>
            <?php
            if ($outstandingBalance > 0 && $order->get_status() === 'on-hold') {
                ?>
                <tr>
                    <td colspan="3" style="color: red;">
                        Please ensure
                        <?php echo wc_price($outstandingBalance, ['currency' => $order->get_currency()]); ?>
                        has been redeemed from the gift voucher in VaocherApp
                    </td>
                </tr>
                <?php
            }
        }
    }
}

/**
 * Rendering the gift card that has been used in an order in the customers view post payment & emails.
 * This is the customers view when viewing an order in My Account and in the emails & receipt
 *
 * @param $totalRows
 * @param $order
 * @return mixed
 */
function vaocherapp_woocommerce_get_order_item_totals($totalRows, $order)
{
    if ($order->meta_exists(VaocherApp::ORDER_META_VOUCHER_CODE)) {
        $code = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_CODE);
        $appliedBalance = $order->get_meta(VaocherApp::ORDER_META_VOUCHER_APPLIED_BALANCE);

        if ($appliedBalance > 0) {
            // Set last total row in a variable and remove it.
            $grandTotal = $totalRows['order_total'];
            unset($totalRows['order_total']);

            $totalRows['vaocherapp'] = [
                'label' => __('Gift voucher', 'vaocher-app').' ('.strtoupper($code).'):',
                'value' => wc_price(-1 * $appliedBalance, ['currency' => $order->get_currency()]),
            ];

            // Set back last total row
            $totalRows['order_total'] = $grandTotal;
        }
    }

    return $totalRows;
}

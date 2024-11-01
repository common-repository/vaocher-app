<?php

class VaocherAppStorage
{
    const SESSION_KEY = 'va_gift_voucher_code';

    /**
     * This is the gift voucher object (as array) from our API.
     *
     * @var \VaocherAppVoucher|null
     */
    public static $giftVoucher;

    public static $appliedGiftVoucherBalance = 0;

    public static function getGiftVoucherCode()
    {
        if (WC()->session) {
            return WC()->session->get(static::SESSION_KEY);
        }

        return null;
    }

    /**
     * @param  string|null  $code
     */
    public static function setGiftVoucherCode($code)
    {
        if (WC()->session) {
            if (! $code) {
                WC()->session->__unset(static::SESSION_KEY);
            } elseif (VaocherAppApi::getGiftVoucherByCode($code)) {
                WC()->session->set(static::SESSION_KEY, $code);
            }
        }
    }
}
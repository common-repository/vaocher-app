<?php

/**
 * This is more like a value object than a model.
 * This holds the data state after successfully redeem a voucher.
 */
class VaocherAppRedeemed extends VaocherAppModelAbstract
{
    /**
     * This amount is in smallest unit, which is "cents".
     * E.g. 10000 means $100
     *
     * @var int
     */
    protected $redeemedAmount;

    /**
     * @return int
     */
    public function getRedeemedAmount()
    {
        return $this->redeemedAmount;
    }

    /**
     * @param  int  $redeemedAmount
     * @return $this
     */
    public function setRedeemedAmount($redeemedAmount)
    {
        $this->redeemedAmount = $redeemedAmount;
        return $this;
    }

    /**
     * Because the redeemed amount property is in smallest unit, we need to convert that to dollars to line up with Woocommerce currency.
     *
     * @return float
     */
    public function getRedeemedAmountAsDollars()
    {
        return VaocherAppHelpers::fromCentsToDollars($this->getRedeemedAmount());
    }
}

<?php

/**
 * @property string $id
 * @property string $code
 * @property int $remain_balance
 * @property string|null $expiry
 * @property string $status
 * @property string $created_at
 * @property string $updated_at
 */
class VaocherAppVoucher extends VaocherAppModelAbstract
{
    /**
     * @return bool
     */
    public function isExpired()
    {
        // Expiry is nullable, and when its null, it means never expire
        if (! $this->expiry) {
            return false;
        }

        try {
            return (new DateTime($this->expiry))->getTimestamp() <= time();
        } catch (Exception $exception) {
            // TODO: Handle exception

            return false;
        }
    }

    /**
     * @return float
     */
    public function getRemainingBalanceAsDollars()
    {
        return VaocherAppHelpers::fromCentsToDollars($this->remain_balance);
    }
}
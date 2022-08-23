<?php

namespace App\Models\user;

class UserStat
{
    private int $shares;
    private float $balance;
    private float $withdrawals;
    private int $refId;
    private float $refCom;

    /**
     * @return int
     */
    public function getShares(): int
    {
        return $this->shares;
    }

    /**
     * @param int $shares
     */
    public function setShares(int $shares): void
    {
        $this->shares = $shares;
    }

    /**
     * @return float
     */
    public function getBalance(): float
    {
        return $this->balance;
    }

    /**
     * @param float $balance
     */
    public function setBalance(float $balance): void
    {
        $this->balance = $balance;
    }

    /**
     * @return float
     */
    public function getWithdrawals(): float
    {
        return $this->withdrawals;
    }

    /**
     * @param float $withdrawals
     */
    public function setWithdrawals(float $withdrawals): void
    {
        $this->withdrawals = $withdrawals;
    }

    /**
     * @return int
     */
    public function getRefId(): int
    {
        return $this->refId;
    }

    /**
     * @param int $refId
     */
    public function setRefId(int $refId): void
    {
        $this->refId = $refId;
    }

    /**
     * @return float
     */
    public function getRefCom(): float
    {
        return $this->refCom;
    }

    /**
     * @param float $refCom
     */
    public function setRefCom(float $refCom): void
    {
        $this->refCom = $refCom;
    }

}
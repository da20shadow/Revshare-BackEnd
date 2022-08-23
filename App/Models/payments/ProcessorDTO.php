<?php

namespace App\Models\payments;

class ProcessorDTO
{
    private int $id;
    private string $name;
    private int $min_deposit;
    private int $min_withdrawal;
    private float $fees;
    private string $wallet;

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return int
     */
    public function getMinDeposit(): int
    {
        return $this->min_deposit;
    }

    /**
     * @param int $min_deposit
     */
    public function setMinDeposit(int $min_deposit): void
    {
        $this->min_deposit = $min_deposit;
    }

    /**
     * @return int
     */
    public function getMinWithdrawal(): int
    {
        return $this->min_withdrawal;
    }

    /**
     * @param int $min_withdrawal
     */
    public function setMinWithdrawal(int $min_withdrawal): void
    {
        $this->min_withdrawal = $min_withdrawal;
    }

    /**
     * @return float
     */
    public function getFees(): float
    {
        return $this->fees;
    }

    /**
     * @param float $fee
     */
    public function setFees(float $fee): void
    {
        $this->fees = $fee;
    }

    /**
     * @return string
     */
    public function getWallet(): string
    {
        return $this->wallet;
    }

    /**
     * @param string $wallet
     */
    public function setWallet(string $wallet): void
    {
        $this->wallet = $wallet;
    }

}

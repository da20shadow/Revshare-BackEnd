<?php

namespace App\Models\payments;

class DepositDTO
{
    private int $id;
    private int $amount;
    private float $coins;
    private int $status;
    private string $processor;
    private string $wallet;
    private int $user_id;
    private mixed $date;
    private int $total;
    private mixed $total_amount;
    private string $username;

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @param string $username
     */
    public function setUsername(string $username): void
    {
        $this->username = $username;
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

    /**
     * @return float
     */
    public function getCoins(): float
    {
        return $this->coins;
    }

    /**
     * @param float $coins
     */
    public function setCoins(float $coins): void
    {
        $this->coins = $coins;
    }

    /**
     * @return float
     */
    public function getTotalAmount(): mixed
    {
        return $this->total_amount;
    }

    /**
     * @param float $total_amount
     */
    public function setTotalAmount(mixed $total_amount): void
    {
        $this->total_amount = $total_amount;
    }

    /**
     * @return int
     */
    public function getTotal(): int
    {
        return $this->total;
    }

    /**
     * @param int $total
     */
    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

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
     * @return int
     */
    public function getAmount(): int
    {
        return $this->amount;
    }

    /**
     * @param int $amount
     */
    public function setAmount(int $amount): void
    {
        $this->amount = $amount;
    }

    /**
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * @param int $status
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }

    /**
     * @return string
     */
    public function getProcessor(): string
    {
        return $this->processor;
    }

    /**
     * @param string $processor
     */
    public function setProcessor(string $processor): void
    {
        $this->processor = $processor;
    }

    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     */
    public function setUserId(int $user_id): void
    {
        $this->user_id = $user_id;
    }

    /**
     * @return mixed
     */
    public function getDate(): mixed
    {
        return $this->date;
    }

    /**
     * @param mixed $date
     */
    public function setDate(mixed $date): void
    {
        $this->date = $date;
    }


}
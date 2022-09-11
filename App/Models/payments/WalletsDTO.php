<?php

namespace App\Models\payments;

class WalletsDTO
{
    private int $id;
    private string $address;
    private int $user_id;
    private int $processor_id;

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
    public function getAddress(): string
    {
        return $this->address;
    }

    /**
     * @param string $address
     */
    public function setAddress(string $address): void
    {
        $this->address = $address;
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
     * @return int
     */
    public function getProcessorId(): int
    {
        return $this->processor_id;
    }

    /**
     * @param int $processor_id
     */
    public function setProcessorId(int $processor_id): void
    {
        $this->processor_id = $processor_id;
    }


}
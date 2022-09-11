<?php

namespace App\Models\shares;

class SharesStatDTO
{
    private int $total;
    private int $orders;
    private int $hold;

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
    public function getOrders(): int
    {
        return $this->orders;
    }

    /**
     * @param int $orders
     */
    public function setOrders(int $orders): void
    {
        $this->orders = $orders;
    }

    /**
     * @return int
     */
    public function getHold(): int
    {
        return $this->hold;
    }

    /**
     * @param int $hold
     */
    public function setHold(int $hold): void
    {
        $this->hold = $hold;
    }


}
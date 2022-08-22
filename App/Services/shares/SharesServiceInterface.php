<?php

namespace App\Services\shares;

interface SharesServiceInterface
{
    public function publish ($sharesData,$userId);
    public function update ($orderInfo,$userId);
    public function cancel ($orderId,$userId);
    public function buy ($orderOwnerId,$quantity,$orderId,$userId);
    public function getAll ();
    public function getOrdersByUserId($userId);
}
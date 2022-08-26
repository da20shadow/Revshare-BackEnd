<?php

namespace App\Repositories\shares;

interface SharesRepositoryInterface
{
    public function insert($quantity,$price,$userId);
    public function buy($orderOwnerId,$referId,$quantity,$costs,$orderId,$userId,$buyAll);
    public function update($price,$order_id,$userId);
    public function getAll ();
    public function getOrderId($orderId);
    public function getOrdersByUserId($userId);
    public function delete($orderId,$userId,$sharesInOrder);
}
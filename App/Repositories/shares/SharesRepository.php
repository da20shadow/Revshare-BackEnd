<?php

namespace App\Repositories\shares;

use App\Models\shares\SharesDTO;
use Database\DBConnector;
use Database\PDODatabase;

class SharesRepository implements SharesRepositoryInterface
{
    private PDODatabase $db;

    public function __construct()
    {
        $this->db = DBConnector::create();
    }

    public function insert($quantity, $price, $userId): array|string
    {
        try {
            $this->db->query("
                UPDATE users 
                SET shares = shares - :quantity
                WHERE user_id = :user_id;
                INSERT INTO marketplace (quantity, price, user_id)
                VALUES (:quantity, :price, :user_id);
            
            ")->execute(array(
                ':quantity' => $quantity,
                ':price' => $price,
                ':user_id' => $userId
            ));
        } catch (\PDOException $exception) {
            return $exception->getMessage();
        }

        return [
            'Quantity' => $quantity,
            'Price' => $price,
            'UserId' => $userId
        ];
    }

    public function buy($orderOwnerId,$referId,$quantity, $costs, $orderId, $userId, $buyAll): bool
    {
        $profitForOwner = $costs;
        $profitForRefer = $costs * 0.1;
        $costs = $costs * 1.1;

        if ($buyAll) {
            try {
                $this->db->query("
                UPDATE users 
                SET shares = shares + :quantity, 
                    balance = balance - :costs, 
                    ref_com = ref_com + :profitForRefer
                WHERE user_id = :user_id;
                UPDATE users SET balance = balance + :profitForOwner WHERE user_id = :orderOwnerId;
                UPDATE users SET balance = balance + :profitForRefer WHERE user_id = :referId;
                DELETE FROM marketplace WHERE order_id = :order_id;
                ")->execute(array(
                    ':quantity' => $quantity,
                    ':costs' => $costs,
                    ':user_id' => $userId,
                    ':order_id' => $orderId,
                    ':profitForOwner' => $profitForOwner,
                    ':profitForRefer' => $profitForRefer,
                    ':referId' => $referId,
                    ':orderOwnerId' => $orderOwnerId,
                ));
                return true;
            }catch (\PDOException $exception){
                echo json_encode([
                    'message'=>$exception->getMessage()
                ]);
                return false;
            }
        }
        try {
            $this->db->query("
                UPDATE users 
                SET shares = shares + :quantity, 
                    balance = balance - :costs, 
                    ref_com = ref_com + :profitForRefer
                WHERE user_id = :user_id;
                UPDATE users SET balance = balance + :profitForOwner WHERE user_id = :orderOwnerId;
                UPDATE users SET balance = balance + :profitForRefer WHERE user_id = :referId;
                UPDATE marketplace SET quantity = quantity - :quantity
                WHERE order_id = :order_id;
                ")->execute(array(
                    ':quantity' => $quantity,
                    ':costs' => $costs,
                    ':user_id' => $userId,
                    ':order_id' => $orderId,
                    ':profitForOwner' => $profitForOwner,
                    ':profitForRefer' => $profitForRefer,
                    ':referId' => $referId,
                    ':orderOwnerId' => $orderOwnerId,
            ));
            return true;
        }catch (\PDOException $exception){
            echo json_encode([
                'message'=>$exception->getMessage()
            ]);
            return false;
        }
    }


    /** UPDATE */
    public function update($price,$order_id, $userId): bool
    {
        try {
            $this->db->query("
                UPDATE marketplace 
                SET price = :price 
                WHERE order_id = :order_id AND user_id = :user_id
            ")->execute(array(
                ':price' => $price,
                ':order_id' => $order_id,
                ':user_id' => $userId
            ));
            return true;
        }catch (\PDOException $exception){
            return false;
        }
    }

    /** ----------------DELETE---------------- */
    public function delete($orderId, $userId,$sharesInOrder): bool
    {
        try {
            $this->db->query("
                DELETE FROM marketplace
                WHERE order_id = :order_id AND user_id = :user_id;
                UPDATE users 
                SET balance = balance - 1, shares = shares + :shares 
                WHERE user_id = :user_id;
            ")->execute(array(
                'order_id' => $orderId,
                'user_id' => $userId,
                'shares' => $sharesInOrder
            ));
            return true;
        }catch (\PDOException $exception){
            return false;
        }
    }

    /** ----------------------GET---------------------- */
    public function getAll(): ?\Generator
    {
        $shares = null;
        try {
            $shares = $this->db->query("
                SELECT order_id, 
                       quantity,
                       price,
                       user_id,
                       date_published
                FROM marketplace ORDER BY price
            ")->execute()
                ->fetch(SharesDTO::class);
        } catch (\PDOException $exception) {
            //TODO: handle exception
        }
        return $shares;
    }

    public function getOrderId($orderId)
    {
        $order = null;
        try {
            $order = $this->db->query("
                SELECT order_id,quantity,price,user_id
                FROM marketplace
                WHERE order_id = :order_id
            ")->execute(array(
                ':order_id' => $orderId
            ))->fetch(SharesDTO::class)->current();
        }catch (\PDOException $exception){
            //TODO process the errors
        }
        return $order;
    }

    public function getOrdersByUserId($userId): ?\Generator
    {
        $orders = null;
        try {
            $orders = $this->db->query("
                SELECT order_id,quantity,price,user_id,date_published
                FROM marketplace
                WHERE user_id = :user_id
            ")->execute(array(
                ':user_id' => $userId
            ))->fetch(SharesDTO::class);
        } catch (\PDOException $exception) {
            //TODO: handle error
        }
        return $orders;
    }

}
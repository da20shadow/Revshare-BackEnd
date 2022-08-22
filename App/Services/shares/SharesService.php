<?php

namespace App\Services\shares;

use App\Repositories\shares\SharesRepository;
use App\Repositories\shares\SharesRepositoryInterface;
use App\Repositories\user\UserRepository;
use App\Repositories\user\UserRepositoryInterface;

class SharesService implements SharesServiceInterface
{
    private SharesRepositoryInterface $sharesRepository;
    private UserRepositoryInterface $userRepository;

    public function __construct()
    {
        $this->sharesRepository = new SharesRepository();
        $this->userRepository = new UserRepository();
    }

    /** ----------------------POST---------------------- */

    /** --------PUBLISH SHARES Order-------- */
    public function publish($sharesData,$userId)
    {
        if (!isset($sharesData['quantity']) || !isset($sharesData['price'])){
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid Request!'
            ]);
            return;
        }

        $quantity = $sharesData['quantity'];
        $price = $sharesData['price'];

        if (!is_numeric($quantity) || !is_numeric($price)){
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid Quantity or Price!'
            ]);
            return;
        }
        if ($quantity < 1 || $price < 0.01 || $price > 99){
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid Quantity or Price!'
            ]);
            return;
        }

        $userStat = $this->userRepository->getUserAccountStat($userId);

        if ($userStat->getShares() < $quantity){
            http_response_code(403);
            echo json_encode([
                'message' => 'You have no such quantity!'
            ]);
            return;
        }

        $result = $this->sharesRepository->insert($quantity,$price,$userId);
        echo json_encode($result);

    }
    /** --------Buy Shares From Order-------- */
    public function buy($orderOwnerId,$quantity,$orderId,$userId)
    {
        if (!isset($quantity) || !isset($orderId) || !isset($orderOwnerId)){
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid Request Quantity or order ID!'
            ]);
            return;
        }

        $orderFromDb = $this->sharesRepository->getOrderId($orderId);

        if (null === $orderFromDb){
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid Request Quantity or order ID!'
            ]);
            return;
        }

        if ($orderFromDb->getUserId() == $userId){
            http_response_code(403);
            echo json_encode([
                'message' => 'You can not buy your own order!'
            ]);
            return;
        }

        if ($orderFromDb->getQuantity() < $quantity){
            http_response_code(403);
            echo json_encode([
                'message' => 'There is no such quantity!'
            ]);
            return;
        }

        $userFromDb = $this->userRepository->getUserAccountStat($userId);

        if (null === $userFromDb){
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid User ID!'
            ]);
            return;
        }
        $costs = ($quantity * $orderFromDb->getPrice());
        if ($costs * 1.1 > $userFromDb->getBalance()){
            http_response_code(403);
            echo json_encode([
                'message' => 'Not Enough Balance To Purchase Shares!'
            ]);
            return;
        }

        $buyAll = false;
        if ($quantity == $orderFromDb->getQuantity()){
            $buyAll = true;
        }

        $referId = $userFromDb->getRefId();
        $purchase = $this->sharesRepository->buy($orderOwnerId,$referId,$quantity,$costs, $orderId,$userId,$buyAll);

        if ($purchase){
            http_response_code(200);
            echo json_encode([
                'Quantity' => $quantity,
                'OrderID' => $orderId,
                'UserId' => $userId
            ]);
        }else {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur! Please, try again or contact us!'
            ]);
        }

    }


    /** ----------------------PATCH---------------------- */

    public function update($orderInfo,$userId)
    {
        $result = $this->sharesRepository->update($orderInfo['price'],$orderInfo['orderId'],$userId);
        if (!$result){
            http_response_code(403);
            echo json_encode(['message' => 'Error! Please, try again!']);
            return;
        }

        http_response_code(200);
        echo json_encode(['message' => 'Successfully Updated Order Price!']);
    }

    /** ----------------------DELETE---------------------- */

    public function cancel($orderId,$userId)
    {
        // TODO: Implement cancel() method.
    }

    /** ----------------------GET---------------------- */

    /** -----------GET All----------- */
    public function getAll()
    {
        $sharesGenerator = $this->sharesRepository->getAll();

        if (null === $sharesGenerator)
        {
            http_response_code(403);
            echo json_encode(['message' => 'No Shares in Marketplace!'],JSON_PRETTY_PRINT);
            return;
        }

        $sharesInfo = $this->generateSharesList($sharesGenerator);

        if (count($sharesInfo['shares']) === 0)
        {
            http_response_code(403);
            echo json_encode(['message' => 'No Shares in Marketplace!'],JSON_PRETTY_PRINT);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'shares' => $sharesInfo['shares'],
            'total'=> $sharesInfo['total']
        ],JSON_PRETTY_PRINT);

    }

    /** GET Orders by user ID */
    public function getOrdersByUserId($userId)
    {
        $userOrders= $this->sharesRepository->getOrdersByUserId($userId);

        if (null === $userOrders)
        {
            http_response_code(403);
            echo json_encode(['message' => 'No Pending Orders!'],JSON_PRETTY_PRINT);
            return;
        }

        $ordersInfo = $this->generateSharesList($userOrders);

        if (count($ordersInfo['shares']) === 0)
        {
            http_response_code(403);
            echo json_encode(['message' => 'No Pending Orders!'],JSON_PRETTY_PRINT);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'orders' => $ordersInfo['shares'],
            'total'=> $ordersInfo['total']
        ],JSON_PRETTY_PRINT);
    }


    /** ----------------DELETE---------------- */

    /** CANCEL order ID */
    public function cancelOrder($orderId,$user_id){
        if (!is_numeric($orderId)){
            http_response_code(403);
            echo json_encode(['message'=>'Invalid order ID!']);
            return;
        }

        $orderFromDb = $this->sharesRepository->getOrderId($orderId);

        if (null === $orderFromDb){
            http_response_code(403);
            echo json_encode(['message'=>'Invalid order ID!']);
            return;
        }

        if ($orderFromDb->getUserId() != $user_id){
            http_response_code(403);
            echo json_encode(['message'=>'Invalid order ID!']);
            return;
        }

        $userStat = $this->userRepository->getUserAccountStat($user_id);
        if ($userStat->getBalance() < 1){
            http_response_code(403);
            echo json_encode(['message'=>'Not enough money to cancel the order!']);
            return;
        }

        $sharesInOrder = $orderFromDb->getQuantity();

        $result = $this->sharesRepository->delete($orderId,$user_id,$sharesInOrder);
        if (!$result){
            http_response_code(403);
            echo json_encode(['message'=>'Error! Please, try again!']);
        }

        http_response_code(200);
        echo json_encode(['message'=>'Successfully Canceled Order!','canceled'=>$sharesInOrder]);
    }

    /** ----------------------VALIDATORS & GENERATORS---------------------- */

    /** ------------SHARES GENERATOR------------ */
    private function generateSharesList($sharesGenerator): array
    {
        $shares = [];
        $total = 0;
        foreach ($sharesGenerator as $share) {

            $total += $share->getQuantity();
            array_push($shares, [
                'id' => $share->getOrderId(),
                'quantity' => $share->getQuantity(),
                'price' => $share->getPrice(),
                'userId' => $share->getUserId(),
                'date_published' => $share->getDatePublished()
            ]);
        }
        return ['shares'=>$shares,'total'=>$total];
    }

}
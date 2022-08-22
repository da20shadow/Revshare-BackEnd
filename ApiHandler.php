<?php

spl_autoload_register();
use App\Services\user\UserService;

class ApiHandler
{
    /** ---------------- USER Requests --------------- */

    /** --> USER POST <-- */
    public function processUserPOSTRequest($userInputs,UserService $userService)
    {
        /** LOGIN User */
        if (count($userInputs) == 2) {
            $userService->login($userInputs);
        } /** CREATE User */
        else {
            $userService->register($userInputs);
        }
    }

    /** --> USER PATCH <-- */
    public function processUserPATCHRequest($userInputs, UserService $userService)
    {
        //TODO: update user info
        http_response_code(200);
        echo json_encode([
            'message' => 'Update user Not Done Yet!',
            'Your Input' => $userInputs
        ]);
    }

    /** --> USER DELETE <-- */
    public function processUserDELETERequest($userInputs, UserService $userService)
    {
        //TODO: DELETE user
        http_response_code(200);
        echo json_encode([
            'message' => 'Delete user Not Done Yet!',
            'Your Input' => $userInputs
        ]);
    }

    /** --> USER GET <-- */
    public function processUserGETRequest($token, UserService $userService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo){
            return;
        }
        $user_id = $userInfo['id'];
        $userService->getUserAccountStat($user_id);
    }

    public function processGetOrdersByUserId($token,$sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo){
            return;
        }
        $user_id = $userInfo['id'];
        $sharesService->getOrdersByUserId($user_id);
    }

    public function processGETUserReferralsRequest($token,$userService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo){
            return;
        }
        $user_id = $userInfo['id'];
        $userService->getUserReferrals($user_id);
    }

    /** ---------------- Shares Requests --------------- */

    /** -----POST----- */
    public function processSharesPOSTRequest($sharesInfo,$token,$sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo){
            return;
        }
        $user_id = $userInfo['id'];
        $sharesService->publish($sharesInfo,$user_id);

    }

    public function processBuySharesRequest($inputData,$orderId,$token,$sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo){
            http_response_code(403);
            return;
        }
        $user_id = $userInfo['id'];

        if (!isset($inputData['orderOwnerId']) || !isset($inputData['quantity'])){
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid owner ID or quantity'
            ]);
            return;
        }

        $orderOwnerId = $inputData['orderOwnerId'];
        $quantity = $inputData['quantity'];
        $sharesService->buy($orderOwnerId,$quantity,$orderId,$user_id);
    }

    /** ----------------------PATCH---------------------- */
    public function processSharesPATCHRequest($inputData,$token,$sharesService)
    {
        if (!isset($inputData['orderId']) || !isset($inputData['price'])){
            http_response_code(403);
            echo json_encode(['message'=>'Error Invalid Order ID!']);
        }

        $userInfo = $this->validateToken($token);

        if (null === $userInfo){
            return;
        }
        $user_id = $userInfo['id'];

        $sharesService->update($inputData,$user_id);
    }

    /** ----------------------GET---------------------- */
    public function processSharesGETRequest($token,$sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo){
            return;
        }
        $sharesService->getAll();
    }


    /** ----------------------DELETE---------------------- */
    public function processSharesDELETERequest($inputData,$token,$sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo){
            return;
        }

        if (!isset($inputData['orderId'])){
            http_response_code(403);
            echo json_encode(['message' => 'Invalid Order ID']);
            return;
        }
        $user_id = $userInfo['id'];
        $orderId = $inputData['orderId'];

        $sharesService->cancelOrder($orderId,$user_id);
    }

    /** --------------TOKEN VALIDATION-------------- */
    private function validateToken($token): ? array
    {
        if (!isset($token)) {
            http_response_code(403);
            echo json_encode(['message' => 'Invalid Token!'], JSON_PRETTY_PRINT);
            return null;
        }

        $userInfo = null;
        try {
            $userInfo = AuthValidator::verifyToken($token);
        } catch (Exception $e) {
            //TODO: log the error
            $error = $e->getMessage();
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid or Expired Token!',
                'Error' => $error],
                JSON_PRETTY_PRINT);
        }
        return $userInfo;
    }
}
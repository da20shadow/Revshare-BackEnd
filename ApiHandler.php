<?php

spl_autoload_register();

use App\Services\user\UserService;

class ApiHandler
{
    /** ---------------- USER REQUESTS --------------- */

    /** --> USER POST <-- */
    public function processUserPOSTRequest($userInputs, UserService $userService)
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
    public function processUserPATCHRequest($userInputs, $token, UserService $userService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];

        $userService->update($userInputs,$user_id);

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
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];
        $userService->getUserAccountStat($user_id);
    }

    public function processGetOrdersByUserId($token, $sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];
        $sharesService->getOrdersByUserId($user_id);
    }

    public function processGETUserReferralsRequest($token, $userService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];
        $userService->getUserReferrals($user_id);
    }

    /** ---------------- SHARES REQUESTS START --------------- */

    /** -----POST----- */
    public function processSharesPOSTRequest($sharesInfo, $token, $sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];
        $sharesService->publish($sharesInfo, $user_id);

    }

    public function processBuySharesRequest($inputData, $orderId, $token, $sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            http_response_code(403);
            return;
        }
        $user_id = $userInfo['id'];

        if (!isset($inputData['orderOwnerId']) || !isset($inputData['quantity'])) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid owner ID or quantity'
            ]);
            return;
        }

        $orderOwnerId = $inputData['orderOwnerId'];
        $quantity = $inputData['quantity'];
        $sharesService->buy($orderOwnerId, $quantity, $orderId, $user_id);
    }

    public function processShareDividendsRequest($token,$inputData,$sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            http_response_code(403);
            return;
        }
        $role = $userInfo['role'];
        if ($role !== 1){
            http_response_code(403);
            echo json_encode(['message' => 'You have no rights!']);
            return;
        }
        $sharesService->shareDividends($inputData);
    }

    /** ----------------------PATCH---------------------- */
    public function processSharesPATCHRequest($inputData, $token, $sharesService)
    {
        if (!isset($inputData['orderId']) || !isset($inputData['price'])) {
            http_response_code(403);
            echo json_encode(['message' => 'Error Invalid Order ID!']);
        }

        $userInfo = $this->validateToken($token);

        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];

        $sharesService->update($inputData, $user_id);
    }

    /** ----------------------GET---------------------- */
    public function processSharesGETRequest($token, $sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $sharesService->getAll();
    }

    /** Get dividends history */
    public function processGetSharedDividends($sharesService)
    {
        $sharesService->getDividendsHistory();
    }
    /** Get shares stat */
    public function processGetSharesStat($sharesService){
        $sharesService->getSharesStat();
    }

    /** ----------------------DELETE---------------------- */
    public function processSharesDELETERequest($inputData, $token, $sharesService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }

        if (!isset($inputData['orderId'])) {
            http_response_code(403);
            echo json_encode(['message' => 'Invalid Order ID']);
            return;
        }
        $user_id = $userInfo['id'];
        $orderId = $inputData['orderId'];

        $sharesService->cancelOrder($orderId, $user_id);
    }

    /** ---------------- SHARES REQUESTS END --------------- */


    /** -----------------------------PAYMENTS REQUESTS START----------------------------- */


    /** --------------------POST-------------------- */

    /** Deposit Request */
    public function processDepositRequest($token,$inputData,$paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];
        $paymentsService->deposit($inputData,$user_id);
    }

    /** Withdrawal request */
    public function processWithdrawalRequest($token, $inputData, $paymentsService)
    {
        if (!isset($inputData['amount']) && !isset($inputData['processor'])) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid amount or processor!'
            ]);
        }

        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }

        $user_id = $userInfo['id'];
        $amount = $inputData['amount'];
        $processor = $inputData['processor'];

        $paymentsService->withdrawalRequest($amount, $processor, $user_id);

    }

    /** --------------------UPDATE-------------------- */

    public function processUpdateWallets($token, $inputData, $paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];

        $paymentsService->updateWallets($inputData, $user_id);
    }

    public function processUpdateDepositRequest($token,$inputData,$paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $role = $userInfo['role'];

        if ($role !== 1){
            http_response_code(403);
            echo json_encode([
                'message' => 'You have no rights!'
            ]);
            return;
        }
        $paymentsService->updateDepositStatus($inputData);
    }

    /** --------------------DELETE-------------------- */

    public function processDELETEDepositRequest($deposit_id,$token,$paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $role = $userInfo['role'];

        if ($role !== 1){
            http_response_code(403);
            echo json_encode([
                'message' => 'You have no rights!'
            ]);
            return;
        }

        $paymentsService->deleteDepositById($deposit_id);
    }

    /** --------------------GET-------------------- */
    public function processPaymentGETRequest($token, $paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }

        $paymentsService->getProcessors(true);

    }

    /** Get user wallets */
    public function processGetUserWallets($token, $paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];

        $paymentsService->getUserWallets($user_id);
    }
    /** GET Pending Deposits */
    public function processGetPendingDeposits($token,$offset,$paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $role = $userInfo['role'];
        if ($role !== 1){
            http_response_code(403);
            echo json_encode([
                'message' => 'You have no rights!'
            ]);
            return;
        }
        $paymentsService->getPendingDeposits($offset);
    }
    /** GET Pending Withdrawals */
    public function processGetPendingWithdrawals($token,$offset,$paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $role = $userInfo['role'];
        if ($role !== 1){
            http_response_code(403);
            echo json_encode([
                'message' => 'You have no rights!'
            ]);
            return;
        }
        $paymentsService->getPendingWithdrawals($offset);
    }
    /** Get deposit history */
    public function processGetDepositHistory($token,$offset,$paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];
        $paymentsService->getDepositHistory($offset,$user_id);
    }
    /** Get withdrawal history */
    public function processGetWithdrawalHistory($token,$offset,$paymentsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $user_id = $userInfo['id'];
        $paymentsService->getWithdrawalHistory($offset,$user_id);
    }

    /** -----------------------------PAYMENTS REQUESTS END----------------------------- */


    /** -----------------------------NEWS REQUESTS END----------------------------- */

    public function processGetNews($newsService)
    {
        $newsService->getNews();
    }

    public function processPublishNews($token,$inputData,$newsService)
    {
        $userInfo = $this->validateToken($token);
        if (null === $userInfo) {
            return;
        }
        $role = $userInfo['role'];

        if ($role !== 1){
            http_response_code(403);
            echo json_encode([
                'message'=>'You have no rights!'
            ]);
            return;
        }
        $newsService->publishNews($inputData);

    }

    /** -----------------------------NEWS REQUESTS END----------------------------- */




    /** --------------TOKEN VALIDATION-------------- */
    private function validateToken($token): ?array
    {
        if (!isset($token)) {
            http_response_code(401);
            echo json_encode(['message' => 'Invalid Token!'], JSON_PRETTY_PRINT);
            return null;
        }

        $userInfo = null;
        try {
            $userInfo = AuthValidator::verifyToken($token);
        } catch (Exception $e) {
            //TODO: log the error
            $error = $e->getMessage();
            http_response_code(401);
            echo json_encode([
                'message' => 'Invalid or Expired Token!',
                'Error' => $error],
                JSON_PRETTY_PRINT);
        }
        return $userInfo;
    }
}
<?php

namespace App\Services\payments;

interface PaymentServiceInterface
{
    public function deposit($amount,$user_id);
    public function withdrawalRequest($amount,$user_id);
    public function updateWithdrawalRequestStatus($status,$request_id);
    public function updateDepositStatus($status,$deposit_id);
    public function cancelWithdrawalRequest($request_id,$user_id);
    public function getProcessors();
    public function getUserWithdrawalRequests($user_id);
    public function getDepositsByUserId($user_id);
}
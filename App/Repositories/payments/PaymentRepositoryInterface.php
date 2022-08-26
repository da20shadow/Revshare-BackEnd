<?php

namespace App\Repositories\payments;

interface PaymentRepositoryInterface
{
    public function insertDeposit($amount,$user_id);
    public function insertWithdrawal($amount,$user_id);
    public function updateWithdrawalRequestStatus($status,$request_id);
    public function updateDepositStatus($status,$deposit_id);
    public function deleteRequest($request_id,$user_id);
    public function getPaymentProcessors();
    public function getDepositHistoryByUserId($user_id);
    public function getWithdrawalHistoryByUserId($user_id);
}
<?php

namespace App\Repositories\payments;

interface PaymentRepositoryInterface
{
    public function insertDeposit($amount,$coins,$wallet,$processor,$user_id);
    public function insertWithdrawal($amount,$processor,$user_wallet,$user_id);
    public function updateWithdrawalRequestStatus($status,$request_id);
    public function updateDepositStatus($amount,$deposit_id,$user_id);
    public function deleteRequest($request_id,$user_id);
    public function getPaymentProcessors();
    public function getDepositHistoryByUserId($offset,$user_id);
    public function getWithdrawalHistoryByUserId($offset,$user_id);
}
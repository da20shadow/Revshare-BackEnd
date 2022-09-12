<?php

namespace App\Services\payments;

interface PaymentServiceInterface
{
    public function deposit($depositInfo,$user_id);
    public function withdrawalRequest($amount,$processor,$user_id);
    public function updateWithdrawalRequestStatus($status,$request_id);
    public function updateDepositStatus($depositInfo);
    public function cancelWithdrawalRequest($request_id,$user_id);
    public function getProcessors($printThem);
    public function getUserWithdrawalRequests($user_id);
    public function getDepositHistory($offset,$user_id);
}
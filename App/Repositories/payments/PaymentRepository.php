<?php

namespace App\Repositories\payments;

use App\Models\payments\ProcessorDTO;
use Database\DBConnector;
use Database\PDODatabase;
use Generator;

class PaymentRepository implements PaymentRepositoryInterface
{
    private PDODatabase $db;

    public function __construct()
    {
        $this->db = DBConnector::create();
    }


    /** -------------------POST------------------- */

    /** Add Deposit */
    public function insertDeposit($amount, $user_id)
    {
        // TODO: Implement insertDeposit() method.
    }

    /** Add Withdrawal */
    public function insertWithdrawal($amount, $user_id)
    {
        // TODO: Implement insertWithdrawal() method.
    }


    /** -------------------UPDATE------------------- */

    /** Update Request Status */
    public function updateWithdrawalRequestStatus($status,$request_id)
    {
     //TODO:
    }

    /** Update Deposit Status */
    public function updateDepositStatus($status,$deposit_id)
    {
        //TODO:
    }


    /** -------------------DELETE------------------- */

    /** Cancel Request */
    public function deleteRequest($request_id, $user_id)
    {
        // TODO: Implement deleteRequest() method.
    }


    /** ---------------------GET--------------------- */
    public function getPaymentProcessors(): ?Generator
    {
        $processors = null;
        try {
            $processors = $this->db->query("
                SELECT id,name,min_deposit,min_withdrawal,fees,wallet
                FROM payment_processors
            ")->execute()
                ->fetch(ProcessorDTO::class);
        }catch (\PDOException $exception) {
            //TODO: log the error
        }
        return $processors;
    }

    public function getDepositHistoryByUserId($user_id)
    {
        // TODO: Implement getDepositHistoryByUserId() method.
    }

    public function getWithdrawalHistoryByUserId($user_id)
    {
        // TODO: Implement getWithdrawalHistoryByUserId() method.
    }
}
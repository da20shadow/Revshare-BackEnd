<?php

namespace App\Repositories\payments;

use App\Models\payments\DepositDTO;
use App\Models\payments\ProcessorDTO;
use App\Models\payments\WalletsDTO;
use App\Models\payments\WithdrawalDTO;
use Database\DBConnector;
use Database\PDODatabase;
use Generator;
use JetBrains\PhpStorm\ArrayShape;

class PaymentRepository implements PaymentRepositoryInterface
{
    private PDODatabase $db;

    public function __construct()
    {
        $this->db = DBConnector::create();
    }


    /** -------------------POST------------------- */

    /** Add Deposit */
    public function insertDeposit($amount, $coins, $wallet, $processor, $user_id): bool
    {
        try {
            $this->db->query("
                INSERT INTO deposits (amount,coins,wallet, payment_processor, user_id) 
                VALUES (:amount, :coins, :wallet, :payment_processor, :user_id)
            ")->execute(array(
                ':amount' => $amount,
                ':coins' => $coins,
                ':wallet' => $wallet,
                ':payment_processor' => $processor,
                ':user_id' => $user_id
            ));
            return true;
        } catch (\PDOException $exception) {
            echo json_encode([
                'message' => $exception->getMessage()
            ]);
            return false;
        }
    }

    /** Add Withdrawal */
    public function insertWithdrawal($amount, $processor, $user_wallet, $user_id): bool
    {
        try {
            $this->db->query("
                INSERT INTO withdrawals (amount,payment_processor,wallet,user_id) 
                VALUES (:amount,:payment_processor,:wallet,:user_id);
                UPDATE users SET balance = balance - :amount 
                WHERE user_id = :user_id;
            ")->execute(array(
                ':amount' => $amount,
                ':payment_processor' => $processor,
                ':wallet' => $user_wallet,
                ':user_id' => $user_id
            ));
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    /** Add wallet address */
    public function addWallet($theProcessorId, $theWalletAddress, $user_id): bool
    {
        try {
            $this->db->query("
            INSERT INTO user_wallets 
            (address, user_id, processor_id) 
            VALUES (:address,:user_id,:processor_id)
        ")->execute(array(
                ':address' => $theWalletAddress,
                ':user_id' => $user_id,
                ':processor_id' => $theProcessorId
            ));
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    /** -------------------UPDATE------------------- */

    /** Update Request Status */
    public function updateWithdrawalRequestStatus($status, $request_id)
    {
        //TODO: when update the status also update the total_withdrawals in users by user_id

    }

    /** Update Deposit Status */
    public function updateDepositStatus($amount, $deposit_id, $user_id): bool
    {
        //TODO: update deposit status and user balance
        try {
            $this->db->query("
                UPDATE deposits SET status = 2 WHERE id = :deposit_id;
                UPDATE users SET balance = balance + :amount WHERE user_id = :user_id;
            ")->execute(array(
                ':deposit_id' => $deposit_id,
                ':user_id' => $user_id,
                ':amount' => $amount,
            ));
            return true;
        } catch (\PDOException $exception) {
            echo json_encode([
                'Error' => $exception->getMessage()
            ]);
            return false;
        }
    }

    /** Update wallet */
    public function updateWallet($processorId, $wallet, $user_id): bool
    {
        try {
            $this->db->query("
            UPDATE user_wallets 
            SET address = :address
            WHERE user_id = :user_id AND processor_id = :processor_id
          ")->execute(array(
                ':address' => $wallet,
                ':user_id' => $user_id,
                ':processor_id' => $processorId
            ));
            return true;
        } catch (\PDOException $exception) {
            return false;
        }
    }

    /** -------------------DELETE------------------- */

    /** Cancel Withdrawal Request */
    public function deleteRequest($request_id, $user_id)
    {
        // TODO: Implement deleteRequest() method.
    }

    /** Delete deposit by id */
    public function deleteDepositById($deposit_id): bool
    {
        try {
            $this->db->query("
            DELETE FROM deposits WHERE id = :deposit_id;
        ")->execute(array(
                ':deposit_id' => $deposit_id
            ));
            return true;
        } catch (\PDOException $exception) {
            echo json_encode([
                'Error' => $exception->getMessage()
            ]);
        }
        return false;
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
        } catch (\PDOException $exception) {
            //TODO: log the error
        }
        return $processors;
    }

    public function getPendingDeposits($offset): ?Generator
    {
        //SELECT Orders.OrderID, Customers.CustomerName, Orders.OrderDate
        //FROM Orders
        //INNER JOIN Customers ON Orders.CustomerID=Customers.CustomerID;
        $deposits = null;
        try {
            $deposits = $this->db->query("
                SELECT deposits.id,
                       deposits.amount,
                       deposits.coins,
                       deposits.status,
                       deposits.wallet,
                       deposits.payment_processor AS processor,
                       deposits.user_id,
                       deposits.deposit_date AS date,
                       u.username AS username
                FROM deposits
                INNER JOIN users u ON deposits.user_id = u.user_id
                WHERE status = 1 ORDER BY deposit_date
                LIMIT 50 OFFSET $offset;
            ")->execute()
                ->fetch(DepositDTO::class);
        } catch (\PDOException $PDOException) {
            //TODO: log the errors
            echo json_encode([
                'message' => $PDOException->getMessage()
            ]);
        }
        return $deposits;
    }

    public function getPendingWithdrawals($offset): ?Generator
    {
        $withdrawal = null;
        try {
            $withdrawal = $this->db->query("
                SELECT withdrawals.id,
                       withdrawals.amount,
                       withdrawals.coins,
                       withdrawals.status,
                       withdrawals.wallet,
                       withdrawals.payment_processor AS processor,
                       withdrawals.user_id,
                       withdrawals.request_date AS date,
                       u.username AS username
                FROM withdrawals
                INNER JOIN users u ON withdrawals.user_id = u.user_id
                WHERE status = 1 ORDER BY request_date
                LIMIT 50 OFFSET $offset;
            ")->execute()
                ->fetch(WithdrawalDTO::class);
        } catch (\PDOException $PDOException) {
            //TODO: log the errors
            echo json_encode([
                'message' => $PDOException->getMessage()
            ]);
        }
        return $withdrawal;
    }

    public function getDepositHistoryByUserId($offset, $user_id): ?Generator
    {
        $deposits = null;
        try {
            $deposits = $this->db->query("
                 SELECT deposits.id,
                       deposits.amount,
                       deposits.coins,
                       deposits.status,
                       deposits.wallet,
                       deposits.payment_processor AS processor,
                       deposits.user_id,
                       deposits.deposit_date AS date,
                       u.username AS username
                FROM deposits
                INNER JOIN users u ON deposits.user_id = u.user_id
                WHERE deposits.user_id = :user_id ORDER BY deposit_date DESC
                LIMIT 10 OFFSET $offset;
            ")->execute(array(
                ':user_id' => $user_id,
            ))->fetch(DepositDTO::class);
        } catch (\PDOException $PDOException) {
            //TODO: log the errors
            echo json_encode([
                'message' => $PDOException->getMessage()
            ]);
        }
        return $deposits;
    }

    public function getTotalDeposits($user_id): int|float
    {
        $totalInfo = null;
        try {
            $totalInfo = $this->db->query("
                SELECT COUNT(id) AS total FROM deposits WHERE user_id = :user_id
            ")->execute(array(
                ':user_id' => $user_id
            ))->fetch(DepositDTO::class)->current();
        } catch (\PDOException $exception) {
            return 0;
        }
        if ($totalInfo === null) {
            return 0;
        }
        return $totalInfo->getTotal();
    }

    public function getTotalAmountOfDeposits($user_id): int|float
    {
        $totalInfo = null;
        try {
            $totalInfo = $this->db->query("
                SELECT SUM(amount) AS total_amount FROM deposits WHERE user_id = :user_id
            ")->execute(array(
                ':user_id' => $user_id
            ))->fetch(DepositDTO::class)->current();
        } catch (\PDOException $exception) {
            return 0;
        }
        if ($totalInfo->getTotalAmount() === null) {
            return 0;
        }
        return $totalInfo->getTotalAmount();
    }

    public function getWithdrawalHistoryByUserId($offset, $user_id): ?Generator
    {
        $withdrawals = null;
        try {
            $withdrawals = $this->db->query("
                SELECT withdrawals.id,
                       withdrawals.amount,
                       withdrawals.coins,
                       withdrawals.status,
                       withdrawals.wallet,
                       withdrawals.payment_processor AS processor,
                       withdrawals.user_id,
                       withdrawals.request_date AS date,
                       u.username AS username
                FROM withdrawals
                INNER JOIN users u ON withdrawals.user_id = u.user_id
                WHERE withdrawals.user_id = :user_id ORDER BY request_date DESC
                LIMIT 10 OFFSET $offset;
            ")->execute(array(
                ':user_id' => $user_id,
            ))->fetch(WithdrawalDTO::class);
        } catch (\PDOException $PDOException) {
            //TODO: log the errors
            echo json_encode([
                'message' => $PDOException->getMessage()
            ]);
        }
        return $withdrawals;
    }

    public function getTotalWithdrawals($user_id)
    {
        $totalInfo = null;
        try {
            $totalInfo = $this->db->query("
                SELECT COUNT(id) AS total FROM withdrawals WHERE user_id = :user_id
            ")->execute(array(
                ':user_id' => $user_id
            ))->fetch(WithdrawalDTO::class)->current();
        } catch (\PDOException $exception) {

        }
        return $totalInfo->getTotal();
    }

    /** Get deposit by ID */
    public function getDepositById($deposit_id)
    {
        $deposit = null;
        try {
            $deposit = $this->db->query("
                SELECT id,
                       amount,
                       coins,
                       status,
                       wallet,
                       payment_processor AS processor,
                       user_id
                FROM deposits
                WHERE id = :deposit_id
            ")->execute(array(
                ':deposit_id' => $deposit_id
            ))->fetch(DepositDTO::class)->current();
        } catch (\PDOException $exception) {
            echo json_encode([
                'Error' => $exception->getMessage()
            ]);
        }
        return $deposit;
    }

    /** Get user wallets */
    public function getUserWallets($user_id): ?Generator
    {
        $walletsGenerator = null;
        try {
            $walletsGenerator = $this->db->query("
            SELECT id, address, user_id ,processor_id
            FROM user_wallets 
            WHERE user_id = :user_id;
        ")->execute(array(
                ':user_id' => $user_id
            ))->fetch(WalletsDTO::class);

        } catch (\PDOException $exception) {
            echo json_encode(['message' => $exception->getMessage()]);
            return null;
        }
        return $walletsGenerator;
    }
}
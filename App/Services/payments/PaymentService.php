<?php

namespace App\Services\payments;

use App\Repositories\payments\PaymentRepository;
use App\Repositories\user\UserRepository;

class PaymentService implements PaymentServiceInterface
{
    private PaymentRepository $paymentRepository;
    private UserRepository $userRepository;

    public function __construct()
    {
        $this->paymentRepository = new PaymentRepository();
        $this->userRepository = new UserRepository();
    }


    /** --------------------POST-------------------- */

    /** Add Deposit */
    public function deposit($depositInfo, $user_id)
    {
        if (!isset($depositInfo['amount']) || !isset($depositInfo['processor'])
            || !isset($depositInfo['coins']) || !isset($depositInfo['wallet'])) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid amount or payment processor'
            ]);
            return;
        }

        $amount = $depositInfo['amount'];
        $coins = $depositInfo['coins'];
        $wallet = $depositInfo['wallet'];

        if (!is_numeric($amount) || $amount < 1 || !is_numeric($coins) || $coins <= 0) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid amount'
            ]);
            return;
        }

        $processorsFromDb = $this->getProcessors(false);

        $processor = $depositInfo['processor'];
        $isProcessorValid = false;
        foreach ($processorsFromDb as $paymentProcessor) {

            if ($paymentProcessor['name'] === $processor) {
                $isProcessorValid = true;
            }
        }

        if (!$isProcessorValid) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Invalid payment processor!'
            ]);
            return;
        }

        $result = $this->paymentRepository->insertDeposit($amount, $coins, $wallet, $processor, $user_id);

        if (!$result) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur please, try again!'
            ]);
            return;
        }
        http_response_code(201);
        echo json_encode([
            'message' => 'Successfully added deposit!'
        ]);
    }

    /** Request Withdrawal */
    public function withdrawalRequest($amount, $processor, $user_id)
    {
        if (!is_numeric($amount)) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid amount!'
            ]);
        }

        $paymentProcessorsFromDB = $this->getProcessors(false);

        if (null === $paymentProcessorsFromDB) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid Payment Processor Request!'
            ]);
            return;
        }

        $processorExist = false;
        $minimum = false;
        foreach ($paymentProcessorsFromDB as $dbProcessor) {
            if ($dbProcessor['name'] === $processor) {
                $processorExist = true;
                $minimum = $dbProcessor['min_withdrawal'];
            }
        }

        if (!$processorExist) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid Payment Processor!'
            ]);
            return;
        }

        $userStat = $this->userRepository->getUserAccountStat($user_id);

        if (!$minimum || $minimum > $userStat->getBalance()) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! The minimum withdrawal amount for ' . $processor . ' is $' . $minimum
            ]);
            return;
        }

        if ($userStat->getBalance() < $amount) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Not enough money in your account balance!'
            ]);
            return;
        }

        //TODO: get the user wallet address where to send the money

        $user_wallet = $this->userRepository->getUserWalletByProcessor($processor, $user_id);

        if (null === $user_wallet || false === $user_wallet) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Please, add ' . $processor . ' wallet address first!'
            ]);
            return;
        }

        $result = $this->paymentRepository->insertWithdrawal($amount, $processor, $user_wallet, $user_id);

        if (!$result) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Please, try again or contact our support!'
            ]);
            return;
        }

        http_response_code(201);
        echo json_encode([
            'message' => 'Successfully requested $' . $amount . ' to ' . $processor . '!'
        ]);
    }


    /** --------------------UPDATE-------------------- */

    /** Update Withdrawal */
    public function updateWithdrawalRequestStatus($status, $request_id)
    {
        //TODO:
    }

    /** Update Deposit */
    public function updateDepositStatus($depositInfo)
    {
        if (!isset($depositInfo['amount']) || !isset($depositInfo['deposit_id'])) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid amount or deposit ID'
            ]);
            return;
        }
        $amount = $depositInfo['amount'];
        $deposit_id = $depositInfo['deposit_id'];

        $depositFromDB = $this->paymentRepository->getDepositById($deposit_id);
        if (null === $depositFromDB || $depositFromDB->getUserId() === null) {
            return;
        }
        $user_id = $depositFromDB->getUserId();

        $result = $this->paymentRepository->updateDepositStatus($amount, $deposit_id, $user_id);

        if ($result === false) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Please, try again!'
            ]);
            return;
        }
        http_response_code(200);
        echo json_encode([
            'message' => 'Successfully Updated Status!'
        ]);

    }

    /** Update wallets */
    public function updateWallets($inputData, $user_id)
    {
        $processorsFromDb = $this->getProcessors(false);
        $userWalletsFromDb = $this->getUserWallets($user_id, true);

        $success = false;
        foreach ($processorsFromDb as $processor) {

            $processorName = strtolower($processor['name']);

            $walletFromDb = [];
            foreach ($userWalletsFromDb as $wallet) {

                if ($wallet['processor_id'] === $processor['id']) {
                    $walletFromDb = $wallet;
                }

            }

            if (isset($inputData[$processorName])
                && $inputData[$processorName] !== '' && isset($walletFromDb['address'])
                && $walletFromDb['address'] !== $inputData[$processorName]
                && $walletFromDb['processor_id'] === $processor['id']) {

                $theProcessorId = $processor['id'];
                $theWalletAddress = $inputData[$processorName];

                $success = $this->paymentRepository->updateWallet($theProcessorId, $theWalletAddress, $user_id);

            } else if (isset($inputData[$processorName])
                && !isset($walletFromDb['address'])
                && $inputData[$processorName] !== '') {

                $theProcessorId = $processor['id'];
                $theWalletAddress = $inputData[$processorName];

                $success = $this->paymentRepository->addWallet($theProcessorId, $theWalletAddress, $user_id);

            }
        }
        if (!$success) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur! Please, try again!'
            ]);
            return;
        }
        http_response_code(201);
        echo json_encode([
            'message' => 'Successfully updated wallets!'
        ]);
    }

    /** --------------------DELETE-------------------- */

    /** Cancel Withdrawal */
    public function cancelWithdrawalRequest($request_id, $user_id)
    {
        // TODO: Implement cancelWithdrawalRequest() method.
    }

    public function deleteDepositById($deposit_id)
    {
        $result = $this->paymentRepository->deleteDepositById($deposit_id);
        if ($result === false) {
            http_response_code(403);
            return;
        }
        http_response_code(200);
        echo json_encode([
            'message' => 'Successfully Deleted!'
        ]);
    }

    /** --------------------GET-------------------- */

    /** Get Available Payment Processors */
    public function getProcessors($printThem): ?array
    {
        $processorsGenerator = $this->paymentRepository->getPaymentProcessors();

        if (null === $processorsGenerator) {
            http_response_code(403);
            echo json_encode(['message' => 'No Shares in Marketplace!'], JSON_PRETTY_PRINT);
            return null;
        }

        $processors = $this->generateProcessorsList($processorsGenerator);

        if ($printThem) {
            http_response_code(200);
            echo json_encode([
                'processors' => $processors
            ]);
        }

        return $processors;
    }

    /** Get User Withdrawals */
    public function getUserWithdrawalRequests($user_id)
    {
        // TODO: Implement getUserWithdrawalRequests() method.
    }

    public function getPendingDeposits($offset)
    {
        $depositsGenerator = $this->paymentRepository->getPendingDeposits($offset);
        if (null === $depositsGenerator) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur!'
            ]);
            return;
        }
        $deposits = $this->generateDepositsList($depositsGenerator);
        if ($deposits === []) {
            http_response_code(403);
            echo json_encode([
                'message' => 'No Pending Deposits!'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'deposits' => $deposits
        ]);

    }

    /** Get pending withdrawals */
    public function getPendingWithdrawals($offset)
    {
        $withdrawalsGenerator = $this->paymentRepository->getPendingWithdrawals($offset);
        if (null === $withdrawalsGenerator) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur!'
            ]);
            return;
        }
        $withdrawals = $this->generateWithdrawalsList($withdrawalsGenerator);
        if ($withdrawals === []) {
            http_response_code(403);
            echo json_encode([
                'message' => 'No Pending Deposits!'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'withdrawals' => $withdrawals
        ]);

    }

    /** Get User Deposits */
    public function getDepositHistory($offset, $user_id)
    {
        $depositsGenerator = $this->paymentRepository->getDepositHistoryByUserId($offset, $user_id);

        if (null === $depositsGenerator) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error occur please, try again!'
            ]);
            return;
        }

        $deposits = $this->generateDepositsList($depositsGenerator);
        $total = $this->paymentRepository->getTotalDeposits($user_id);
        $total_amount = $this->paymentRepository->getTotalAmountOfDeposits($user_id);

        if ($deposits === [] || $total === null || $total_amount === null) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! No deposits data!'
            ]);
            return;
        }

        http_response_code(200);
        echo json_encode([
            'deposits' => $deposits,
            'total' => $total,
            'total_amount' => $total_amount
        ]);

    }

    /** Get User Withdrawals */
    public function getWithdrawalHistory($offset, $user_id)
    {
        $withdrawalsGenerator = $this->paymentRepository->getWithdrawalHistoryByUserId($offset, $user_id);

        if (null === $withdrawalsGenerator) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error occur please, try again!'
            ]);
            return;
        }

        $withdrawals = $this->generateWithdrawalsList($withdrawalsGenerator);
        $total = $this->paymentRepository->getTotalWithdrawals($user_id);
        http_response_code(200);
        echo json_encode([
            'withdrawals' => $withdrawals,
            'total' => $total,
        ]);

    }

    /** GET user wallets */
    public function getUserWallets($user_id, $returnThem = false): ?array
    {
        $generatorWallets = $this->paymentRepository->getUserWallets($user_id);

        if (null === $generatorWallets) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Invalid request!'
            ]);
            return null;
        }

        $wallets = [];
        foreach ($generatorWallets as $wallet) {
            array_push($wallets, [
                'id' => $wallet->getId(),
                'address' => $wallet->getAddress(),
                'user_id' => $wallet->getUserId(),
                'processor_id' => $wallet->getProcessorId()
            ]);
        }

        if ($returnThem) {
            return $wallets;
        } else {
            http_response_code(200);
            echo json_encode([
                'wallets' => $wallets
            ]);
        }
        return null;
    }


    /** ------------------------GENERATORS------------------------ */
    private function generateProcessorsList($processorsGenerator): array
    {
        $processors = [];
        foreach ($processorsGenerator as $processor) {

            array_push($processors, [
                'id' => $processor->getId(),
                'name' => $processor->getName(),
                'min_deposit' => $processor->getMinDeposit(),
                'min_withdrawal' => $processor->getMinWithdrawal(),
                'fees' => $processor->getFees(),
                'wallet' => $processor->getWallet()
            ]);
        }
        return $processors;
    }

    private function generateDepositsList($depositsGenerator): array
    {
        $deposits = [];
        foreach ($depositsGenerator as $deposit) {
            array_push($deposits, [
                'id' => $deposit->getId(),
                'amount' => $deposit->getAmount(),
                'status' => $deposit->getStatus(),
                'wallet' => $deposit->getWallet(),
                'coins' => $deposit->getCoins(),
                'processor' => $deposit->getProcessor(),
                'user_id' => $deposit->getUserId(),
                'date' => $deposit->getDate(),
                'username' => $deposit->getUsername(),
            ]);
        }
        return $deposits;
    }

    private function generateWithdrawalsList($withdrawalsGenerator): array
    {
        $withdrawals = [];
        foreach ($withdrawalsGenerator as $withdrawal) {
            array_push($withdrawals, [
                'id' => $withdrawal->getId(),
                'amount' => $withdrawal->getAmount(),
                'coins' => $withdrawal->getCoins(),
                'status' => $withdrawal->getStatus(),
                'wallet' => $withdrawal->getWallet(),
                'processor' => $withdrawal->getProcessor(),
                'user_id' => $withdrawal->getUserId(),
                'username' => $withdrawal->getUsername(),
                'date' => $withdrawal->getDate(),
            ]);
        }
        return $withdrawals;
    }
}
<?php

namespace App\Services\payments;

use App\Repositories\payments\PaymentRepository;

class PaymentService implements PaymentServiceInterface
{
    private PaymentRepository $paymentRepository;

    public function __construct()
    {
        $this->paymentRepository = new PaymentRepository();
    }


    /** --------------------POST-------------------- */

    /** Add Deposit */
    public function deposit($amount, $user_id)
    {
        // TODO: Implement deposit() method.
    }

    /** Add Withdrawal */
    public function withdrawalRequest($amount, $user_id)
    {
        // TODO: Implement withdrawalRequest() method.
    }


    /** --------------------UPDATE-------------------- */

    /** Update Withdrawal */
    public function updateWithdrawalRequestStatus($status,$request_id)
    {
     //TODO:
    }

    /** Update Deposit */
    public function updateDepositStatus($status,$deposit_id)
    {

    }


    /** --------------------DELETE-------------------- */

    /** Cancel Withdrawal */
    public function cancelWithdrawalRequest($request_id, $user_id)
    {
        // TODO: Implement cancelWithdrawalRequest() method.
    }


    /** --------------------GET-------------------- */

    /** Get Available Payment Processors */
    public function getProcessors()
    {
        $processorsGenerator = $this->paymentRepository->getPaymentProcessors();

        if (null === $processorsGenerator)
        {
            http_response_code(403);
            echo json_encode(['message' => 'No Shares in Marketplace!'],JSON_PRETTY_PRINT);
            return;
        }
        $processors = $this->generateProcessorsList($processorsGenerator);
        http_response_code(200);
        echo json_encode([
            'processors' => $processors
        ]);
    }

    /** Get User Withdrawals */
    public function getUserWithdrawalRequests($user_id)
    {
        // TODO: Implement getUserWithdrawalRequests() method.
    }

    /** Get User Deposits */
    public function getDepositsByUserId($user_id)
    {
        // TODO: Implement getDepositsByUserId() method.
    }

    /** GENERATOR */
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
}
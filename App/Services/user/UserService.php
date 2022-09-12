<?php

namespace App\Services\user;

use App\Models\user\UserDTO;
use App\Repositories\user\UserRepository;
use App\Services\encryption\EncryptionService;
use AuthValidator;
use Exception;

class UserService implements UserServiceInterface
{
    private UserRepository $userRepository;
    private EncryptionService $encryptionService;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->encryptionService = new EncryptionService();
    }

    /** --------------------POST-------------------- */
    public function register($userInputs)
    {
        if (!isset($userInputs['username']) || !isset($userInputs['email'])
            || !isset($userInputs['password']) || !isset($userInputs['re_password'])) {
            http_response_code(403);
            echo json_encode([
                'message' => 'All fields are required!'
            ]);
            return;
        }

        if ($userInputs['password'] != $userInputs['re_password']) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Password and re-password does not match!!'
            ]);
            return;
        }

        $username = $userInputs['username'];
        $email = $userInputs['email'];
        $password = $userInputs['password'];

        $userFromDb = $this->userRepository->getUserByUsername($username);
        if ($userFromDb instanceof UserDTO) {
            http_response_code(403);
            echo json_encode([
                'message' => 'This username already registered!'
            ]);
            return;
        }

        $userFromDb = $this->userRepository->getUserByEmail($email);
        if ($userFromDb instanceof UserDTO) {
            http_response_code(403);
            echo json_encode([
                'message' => 'This email already registered!'
            ]);
            return;
        }

        $user = new UserDTO();
        try {
            $user->setUsername($username);
            $user->setEmail($email);
            $user->setPassword($password);

        } catch (Exception $exception) {
            http_response_code(403);
            echo json_encode([
                'message' => $exception->getMessage()
            ]);
            return;
        }

        $refer_id = 1;
        if (isset($userInputs['refer_id']) && is_numeric($userInputs['refer_id'])
            && $userInputs['refer_id'] > 0) {
            $refer_id = $userInputs['refer_id'];
        }
        $result = $this->userRepository->insert($user, $refer_id);

        if (!$result) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur, try again latter!'
            ]);
        }
        http_response_code(201);
        echo json_encode([
            'message' => 'Successfully Registered!'
        ]);

    }

    public function login($userInputs)
    {
        if (!isset($userInputs['email']) || !isset($userInputs['password'])) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Empty Email or Password!'
            ], JSON_PRETTY_PRINT);
        }
        $password = $userInputs['password'];

        $userFromDb = $this->userRepository->getUserByEmail($userInputs['email']);

        if (null === $userFromDb) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Wrong Email or Password!'
            ], JSON_PRETTY_PRINT);
            return;
        }

        if (false === $this->encryptionService->verify($password, $userFromDb->getPassword())) {

            http_response_code(403);
            echo json_encode([
                'message' => 'Wrong Email or Password!'
            ], JSON_PRETTY_PRINT);
            return;
        }

        try {
            $token = AuthValidator::createToken($userFromDb);

            http_response_code(200);
            echo json_encode($token, JSON_PRETTY_PRINT);

        } catch (Exception $e) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! ' . $e->getMessage()
            ], JSON_PRETTY_PRINT);
        }
    }

    public function update($userInputs,$user_id)
    {
        $userFromDB = $this->userRepository->getUserById($user_id);

        if (!$userFromDB instanceof UserDTO){
            http_response_code(403);
            echo json_encode([
                'message' => 'Such user not exist!'
            ]);
            return;
        }

        if (isset($userInputs['password']) && $userInputs['password'] != ''
        && isset($userInputs['newPassword']) && $userInputs['newPassword'] != ''){

            if (false === $this->encryptionService->verify($userInputs['password'], $userFromDB->getPassword())) {

                http_response_code(403);
                echo json_encode([
                    'message' => 'Wrong Password!'
                ], JSON_PRETTY_PRINT);
                return;
            }

            try {
                $userFromDB->setPassword($userInputs['newPassword']);
            }catch (Exception $exception){
                http_response_code(403);
                echo json_encode([
                    'message' => $exception->getMessage()
                ]);
                return;
            }
            $result = $this->userRepository->update($userFromDB,'password');
            if (!$result){
                return;
            }
            http_response_code(200);
            echo json_encode([
                'message' => 'Successfully updated password!'
            ]);

        }
        else if (isset($userInputs['email']) && $userInputs['email'] != ''
            && isset($userInputs['newEmail']) && $userInputs['newEmail'] != ''){

            if ($userInputs['email'] !== $userFromDB->getEmail()){
                http_response_code(403);
                echo json_encode([
                    'message' => 'Wrong Email!'
                ]);
                return;
            }

            try {
                $userFromDB->setEmail($userInputs['newEmail']);
            }catch (Exception $exception){
                http_response_code(403);
                echo json_encode([
                    'message' => $exception->getMessage()
                ]);
                return;
            }
            $result = $this->userRepository->update($userFromDB,'email');
            if (!$result){
                return;
            }
            http_response_code(200);
            echo json_encode([
                'message' => 'Successfully updated email!'
            ]);

        }else {

            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur! Please, try again or contact us!'
            ]);

        }
    }

    public function delete($userInputs)
    {
        // TODO: Implement delete() method.
    }

    /** --------------------GET-------------------- */

    public function getUserById($user_id)
    {
        // TODO: Implement getUserById() method.
    }

    public function getUserByUsername($username)
    {
        // TODO: Implement getUserByUsername() method.
    }

    public function getUserByEmail($email)
    {
        // TODO: Implement getUserByEmail() method.
    }

    public function getUserReferrals($user_id)
    {
        $referralsGenerator = $this->userRepository->getUserReferrals($user_id);
        if (null === $referralsGenerator) {
            http_response_code(403);
            echo json_encode([
                'message' => 'Error! Please, try again or contact us!'
            ]);
            return;
        }
        $referrals = $this->generateUsersList($referralsGenerator);
        http_response_code(200);
        echo json_encode([
            'referrals' => $referrals['referrals'],
            'total' => $referrals['total'],
            'total_commission' => $referrals['total_commission']
        ]);
    }

    public function getUserAccountStat($user_id)
    {
        $userStat = $this->userRepository->getUserAccountStat($user_id);

        if (null === $userStat) {
            http_response_code(403);
            echo json_encode([
                'message' => 'An Error Occur! Please, try again or contact the support!'],
                JSON_PRETTY_PRINT);
            return;
        }

        http_response_code(200);
        echo json_encode([
            "balance" => $userStat->getBalance(),
            "shares" => $userStat->getShares(),
            "withdrawals" => $userStat->getWithdrawals(),
            "refId" => $userStat->getRefId(),
            "refCom" => $userStat->getRefCom()
        ], JSON_PRETTY_PRINT);
    }

    public function createUserDTO($userInputs)
    {
        // TODO: Implement createUserDTO() method.
    }

    /** ------------Users GENERATOR------------ */
    private function generateUsersList($referralsGenerator): array
    {
        $users = [];
        $total = 0;
        $total_commission = 0;
        foreach ($referralsGenerator as $user) {

            $total++;
            $total_commission += $user->getRefCom();
            array_push($users, [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'refCom' => $user->getRefCom()
            ]);
        }
        return ['referrals' => $users, 'total' => $total, 'total_commission' => $total_commission];
    }
}
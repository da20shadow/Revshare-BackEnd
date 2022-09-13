<?php

namespace App\Repositories\user;

use App\Models\user\UserDTO;
use App\Models\user\UserStat;
use Database\DBConnector;
use Database\PDODatabase;
use PDOException;

class UserRepository implements UserRepositoryInterface
{
    private PDODatabase $db;

    public function __construct()
    {
        $this->db = DBConnector::create();
    }

    /** ------------------ CREATE ------------------- */
    public function insert(UserDTO $userDTO, $refer_id): bool
    {
        try {
            $this->db->query("
                INSERT INTO users
                (username,email,password,ref_id)
                VALUES (:username,:email,:password,:ref_id)
            ")->execute(array(
                ':username' => $userDTO->getUsername(),
                ':email' => $userDTO->getEmail(),
                ':password' => $userDTO->getPassword(),
                'ref_id' => $refer_id
            ));
            return true;
        } catch (PDOException $exception) {
            //TODO log the error
            $err = $exception->getMessage();
            return false;
        }
    }

    /** ------------------UPDATE ------------------- */
    public function update(UserDTO $userDTO,$update): bool
    {
       if ($update === 'password'){
           try {
               $this->db->query("
                    UPDATE users
                    SET password = :password 
                    WHERE user_id = :user_id
               ")->execute(array(
                   ':password' => $userDTO->getPassword(),
                   ':user_id' => $userDTO->getId()
               ));
               return true;
           }catch (PDOException $exception){
               http_response_code(403);
               echo json_encode([
                   'message' => $exception->getMessage()
               ]);
               return false;
           }

       }else if ($update === 'email'){
           try {
               $this->db->query("
                    UPDATE users
                    SET email = :email 
                    WHERE user_id = :user_id
               ")->execute(array(
                   ':email' => $userDTO->getEmail(),
                   ':user_id' => $userDTO->getId()
               ));
               return true;
           }catch (PDOException $exception){
               http_response_code(403);
               echo json_encode([
                   'message' => $exception->getMessage()
               ]);
               return false;
           }
       }
       return false;
    }

    public function login(UserDTO $userDTO): ?UserDTO
    {
        return $this->db->query("
                SELECT user_id AS id,
                       username,
                       email,
                       password
                FROM users
                WHERE username = :username AND password = :password
            ")->execute(array(
            ':username' => $userDTO->getUsername(),
            ':password' => $userDTO->getPassword()
        ))->fetch(UserDTO::class)
            ->current();
    }

    /** -----------------------GET----------------------- */
    public function getUserById($user_id)
    {
        try {
            return $this->db->query("
                SELECT user_id AS id,
                       username,
                       email,
                       password,
                       role,
                       ref_id AS refId,
                       ref_com AS refCom
                FROM users
                WHERE user_id = :user_id
            ")->execute(array(
                ":user_id" => $user_id
            ))->fetch(UserDTO::class)
                ->current();
        } catch (PDOException $e) {
            return 'Error! ' . $e->getMessage();
        }
    }

    public function getUserByUsername($username)
    {
        try {
            return $this->db
                ->query("
                SELECT user_id AS id,
                       username,
                       email,
                       password,
                       role,
                       ref_id AS refId,
                       ref_com AS refCom
                FROM users
                WHERE username = :username")
                ->execute(array(
                    ":username" => $username))
                ->fetch(UserDTO::class)
                ->current();
        } catch (PDOException $e) {
            return 'Error! ' . $e->getMessage();
        }
    }

    public function getUserByEmail($email)
    {
        try {
            return $this->db->query("
                SELECT user_id AS id,
                       username,
                       email,
                       password,
                       role,
                       ref_id AS refId,
                       ref_com AS refCom
                FROM users
                WHERE email = :email
            ")->execute(array(
                ":email" => $email
            ))->fetch(UserDTO::class)
                ->current();
        } catch (PDOException $e) {
            return 'Error! ' . $e->getMessage();
        }
    }

    public function getUserReferrals($user_id): ?\Generator
    {
        $referrals = null;
        try {
            $referrals = $this->db->query("
                SELECT user_id AS id,username,ref_com AS refCom
                FROM users WHERE ref_id = :ref_id
            ")->execute(array(
                ':ref_id' => $user_id
            ))->fetch(UserDTO::class);
        } catch (PDOException $exception) {

        }
        return $referrals;
    }

    public function getUserAccountStat($user_id)
    {
        //TODO Complete this getting account statistics!
        $userStat = null;
        try {
            $userStat = $this->db->query("
            SELECT balance,
                   shares,
                   withdrawals,
                   ref_id AS refId, 
                   ref_com as refCom
            FROM users 
            WHERE user_id = :user_id
        ")->execute(array(
                ":user_id" => $user_id
            ))
                ->fetch(UserStat::class)
                ->current();

        } catch (PDOException $exception) {
            //TODO: handle the error
        }
        return $userStat;
    }

    /** Get user wallet address by processor */
    public function getUserWalletByProcessor($processor, $user_id): string|bool|null
    {
        $processorId = null;
        $stmt = $this->db->getPDO()->prepare("
                SELECT id FROM payment_processors
                WHERE name = :processor
            ");
        $stmt->bindParam(':processor', $processor);
        $stmt->execute();
        $result = $stmt->fetch();
        $processorId = $result['id'];

        if (!$processorId){
            return false;
        }

        $stmt2 = $this->db->getPDO()->prepare("
                SELECT address FROM user_wallets 
                WHERE processor_id = :processor_id AND user_id = :user_id
            ");

        $stmt2->execute(array(
            ':processor_id' => $processorId,
            ':user_id' => $user_id
        ));
        $result = $stmt2->fetch();
        if (null === $result || !isset($result['address'])){
            return false;
        }
        return $result['address'];

    }

    /** --------------------DELETE------------------- */
    public function delete(UserDTO $userDTO)
    {
        // TODO: Implement delete() method.
    }
}
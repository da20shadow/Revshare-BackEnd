<?php

namespace App\Models\user;

use App\Services\encryption\EncryptionService;
use App\Services\validator\InputValidator;
use Exception;

class UserDTO
{
    private int $id;
    private string $username;
    private string $email;
    private string $password;
    private int $refId;
    private int $role;
    private float $refCom;

    /**
     * @return int
     */
    public function getRole(): int
    {
        return $this->role;
    }

    /**
     * @param int $role
     */
    public function setRole(int $role): void
    {
        $this->role = $role;
    }

    /**
     * @return float
     */
    public function getRefCom(): float
    {
        return $this->refCom;
    }

    /**
     * @param float $refCom
     */
    public function setRefCom(float $refCom): void
    {
        $this->refCom = $refCom;
    }
//    private string $role; TODO: implement roles

    /**
     * @throws Exception
     */
    public static function create($id, $username, $email, $password): UserDTO
    {
        return (new UserDTO())
            ->setId($id)
            ->setUsername($username)
            ->setEmail($email)
            ->setPassword($password);
    }

    /**
     * @return int
     */
    public function getRefId(): int
    {
        return $this->refId;
    }

    /**
     * @param int $refId
     */
    public function setRefId(int $refId): void
    {
        $this->refId = $refId;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getUsername(): string
    {
        return $this->username;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setId(int $id): UserDTO
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function setUsername($username): UserDTO
    {

        if (!isset($username)){
            throw new Exception('Username can not be empty!');
        }

        $username = InputValidator::validateStringInput($username);

        if (strlen($username) < 3 || strlen($username) > 45){
            throw new Exception('Username must be between 3 - 45 characters!');
        }

        if (!preg_match("/^[\w]+$/",$username)){
            throw new Exception('Invalid chars in username! Allowed (a-zA-Z0-9_)');
        }

        $this->username = $username;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function setEmail(string $email): UserDTO
    {

        if (!isset($email)){
            throw new Exception('Email can not be empty!');
        }

        $email = InputValidator::validateStringInput($email);

        if (!filter_var($email,FILTER_VALIDATE_EMAIL)){
            throw new Exception('Invalid Email!');
        }

        if (strlen($email) < 5 || strlen($email) > 245){
            throw new Exception('Invalid Email!');
        }

        $this->email = $email;
        return $this;
    }

    /**
     * @throws Exception
     */
    public function setPassword(string $password): UserDTO
    {

        if (!isset($password)){
            throw new Exception('Password can not be empty!');
        }

        $password = InputValidator::validateStringInput($password);

        if (strlen($password) < 8 || strlen($password) > 245){
            throw new Exception('Password must be between 8 and 45 characters!');
        }

        $password = EncryptionService::hash($password);

        $this->password = $password;
        return $this;
    }

}
<?php

namespace App\Repositories\user;

use App\Models\user\UserDTO;

interface UserRepositoryInterface
{
    public function insert(UserDTO $userDTO,$refer_id);
    public function update(UserDTO $userDTO);
    public function getUserReferrals($user_id);
    public function getUserById($user_id);
    public function getUserByUsername($username);
    public function getUserByEmail($email);
    public function getUserAccountStat($user_id);
    public function delete(UserDTO $userDTO);
}
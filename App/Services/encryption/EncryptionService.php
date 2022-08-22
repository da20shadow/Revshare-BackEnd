<?php

namespace App\Services\encryption;

class EncryptionService
{
    public static function hash(string $password): string
    {
        return password_hash($password,PASSWORD_ARGON2I);
    }

    public static function verify(string $password, string $hash): bool
    {
        return password_verify($password,$hash);
    }
}
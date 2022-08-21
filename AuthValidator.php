<?php
//TODO: CHANGE the ROOT Path before upload ot hosting
require_once($_SERVER['DOCUMENT_ROOT'] . '/Revshare-BackEnd/vendor/autoload.php');

use App\Models\user\UserDTO;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class AuthValidator
{
    private static string $key = "r#4^&1sl45df345jj45sd32rfn#$%TaeSDF34T%W#FSfsdgsdfgaSDF#$%@$#%$%^$#sfasfdfasdf";

    /**
     * @throws Exception
     */
    public static function createToken(UserDTO $userDTO): array
    {
        $rand = substr(md5(microtime()),rand(0,26),5);

        $iat = time();
        $exp = $iat + 60 * 60; // Expiration 1 hour
        $payload = [
            'iss' => 'http://localhost:8090/Revshare-BackEnd/', //API
            'aud' => 'http://localhost:3000/', //Front End
            'iat' => $iat, //Issued time
            'exp' => $exp,
            'id' => $userDTO->getId(),
            'username' => $userDTO->getUsername(),
            'email' => $userDTO->getEmail(),
        ];
        $jwt = JWT::encode($payload, self::$key, 'HS256');

        return [
            'id' => $userDTO->getId(),
            'email' => $userDTO->getEmail(),
            'username' => $userDTO->getUsername(),
            'token' => $jwt,
            'expires' => $exp,
        ];
    }

    private static function decode($token): stdClass
    {
        return JWT::decode($token, new Key(self::$key, 'HS256'));
    }

    /**
     * @throws Exception
     */
    public static function verifyToken($token): array
    {
        $decodedToken = null;
        try {
            $decodedToken = self::decode($token);
        }catch (Exception $exception){
            throw new Exception($exception->getMessage());
        }

        if (!$decodedToken instanceof stdClass) {
            throw new Exception('Invalid Token!');
        }
        if (!isset($decodedToken->id)) {
            throw new Exception('Invalid ID Token!');
        }
        if (!isset($decodedToken->username)) {
            throw new Exception('Invalid Username Token!');
        }
        if (!isset($decodedToken->email)) {
            throw new Exception('Invalid Email Token!');
        }

        return [
            'id' => $decodedToken->id,
            'username' => $decodedToken->username,
            'email' => $decodedToken->email
        ];
    }
}
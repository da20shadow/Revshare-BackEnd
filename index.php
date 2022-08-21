<?php

use App\Services\shares\SharesService;
use App\Services\user\UserService;

spl_autoload_register();
header('Content-Type: application/json');

// Allow from any origin
if (isset($_SERVER['HTTP_ORIGIN'])) {
    header("Access-Control-Allow-Origin: {$_SERVER['HTTP_ORIGIN']}");
    header('Access-Control-Allow-Credentials: true');
    header('Access-Control-Max-Age: 86400');    // cache for 1 day
}
// Access-Control headers are received during OPTIONS requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_METHOD']))
        header("Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS");

    if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']))
        header("Access-Control-Allow-Headers: {$_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS']}");

    exit(0);
}

$url = str_replace('Revshare-BackEnd/', '', $_SERVER['REQUEST_URI']);
$headers = getallheaders();
$inputData = json_decode(file_get_contents('php://input'), true);
$token = $headers['Authorization'] ?? '';
$apiHandler = new ApiHandler();

/** ---------------- USER API Requests ------------- */
// api url -> /users/
if (preg_match("/^\/users[\/]?$/", $url))
{
    $userService = new UserService();

    /** LOGIN / REGISTER */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($inputData))
    {
        $apiHandler->processUserPOSTRequest($inputData,$userService);
    }
    /** UPDATE user */
    elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' && isset($inputData))
    {   //TODO
        $apiHandler->processUserPATCHRequest($inputData,$userService);
    }
    /** GET user */
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($token))
    {   //TODO return account stat
        $apiHandler->processUserGETRequest($token,$userService);
    }
    /** DELETE User */
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($inputData))
    {   //TODO
        $apiHandler->processUserDELETERequest($inputData,$userService);
    }
}
else if(preg_match("/^\/users\/referrals\/?$/",$url)){
    $userService = new UserService();
    if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($token))
    {
        $apiHandler->processGETUserReferralsRequest($token,$userService);
    }
}

else if (preg_match("/^\/shares[\/]?$/", $url))
{
    $sharesService = new SharesService();

    /** Publish order */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($inputData))
    {
        $apiHandler->processSharesPOSTRequest($inputData,$token,$sharesService);
    }
    /** UPDATE order */
    elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' && isset($inputData))
    {   //TODO
        $apiHandler->processSharesPATCHRequest($inputData,$sharesService);
    }
    /** GET user */
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($token))
    {   //TODO return account stat
        $apiHandler->processSharesGETRequest($token,$sharesService);
    }
    /** DELETE User */
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($inputData))
    {   //TODO
        $apiHandler->processSharesDELETERequest($inputData,$sharesService);
    }
}
else if (preg_match("/^\/shares\/\d+$/", $url))
{
    $sharesService = new SharesService();
    $orderId = str_replace('/shares/','',$url);

    /** Publish order */
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($inputData))
    {
        $apiHandler->processBuySharesRequest($inputData,$orderId,$token,$sharesService);
    }
    /** UPDATE order */
    elseif ($_SERVER['REQUEST_METHOD'] === 'PATCH' && isset($inputData))
    {   //TODO
        $apiHandler->processSharesPATCHRequest($inputData,$sharesService);
    }
    /** GET user */
    elseif ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($token))
    {   //TODO return account stat
        $apiHandler->processSharesGETRequest($token,$sharesService);
    }
    /** DELETE User */
    elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE' && isset($inputData))
    {   //TODO
        $apiHandler->processSharesDELETERequest($inputData,$sharesService);
    }
}
else if (preg_match("/^\/user\/shares\/$/",$url)){
    $sharesService = new SharesService();
    $apiHandler->processGetOrdersByUserId($token,$sharesService);
}

else{
    http_response_code(404);
    echo json_encode(['message' => 'Error! Invalid API Request!']);
}
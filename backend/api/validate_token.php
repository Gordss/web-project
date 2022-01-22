<?php

    require_once 'Storage.php';
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);

    if(isset($_GET['token']))
    {
        $token = $_GET['token'];
    }

    $tokenIsValid = Storage::getInstance()->verifyToken($token);

    if ($tokenIsValid) {
        sendResponse("Ok", FALSE, 200);
    }
    else {
        sendResponse("Not authorised!", TRUE, 401);
    }

?>
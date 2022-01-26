<?php
    require_once "./config.php";
    require_once 'Storage.php';
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);

    if (!isset($post['username']) || !isset($post['password']) || empty($post['username'] || empty($post['password']))) {
        sendResponse("Both username and password have to be set", TRUE, 401);
    }

    session_start();
    $username = $post['username'];
    $password = $post['password'];
    $userIsValid = Storage::getInstance()->verifyUserCredentials($username, $password);
    
    if ($userIsValid) {
        $_SESSION['username'] = $username;
        sendResponse("Ok", FALSE, 200);
    }
    else {
        sendResponse("An invalid combination of username and password was entered", TRUE, 401);
    }
?>
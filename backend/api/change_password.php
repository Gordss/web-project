<?php

    require_once 'Storage.php';
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);

    if (!isset($post['email']) || !isset($post['password']) || empty($post['email'] || empty($post['password']))) {
        sendResponse("Both email and password have to be set", TRUE, 401);
    }

    $email = $post['email'];
    $password = $post['password'];

    $userIsValid = Storage::getInstance()->verifyEmail($email);
    
    if ($userIsValid) {

        $password = password_hash($password, PASSWORD_DEFAULT);
        $PasswordChanged = Storage::getInstance()->changePassword($email, $password);
        sendResponse("Ok", FALSE, 200);
    }
    else {
        sendResponse("An invalid combination of email and username was entered", TRUE, 401);
    }    

?>
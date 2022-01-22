<?php

    require_once 'Storage.php';
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);


    if (!isset($post['email']) || !isset($post['password']) || empty($post['email'] || empty($post['password']))) {
        sendResponse("Both email and password have to be set", TRUE, 401);
    }

    $email = $post['email'];
    $password = $post['password'];

    if (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,}$#", $password)) {
        sendResponse('Password must be at least 8 characters in length and contain at least one number, one upper case letter, one lower case letter and one special character.', TRUE, 401);
    }

    $userIsValid = Storage::getInstance()->verifyEmail($email);
    
    if(!$userIsValid)
    {
        sendResponse("An invalid email was entered", TRUE, 401);
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    $PasswordChanged = Storage::getInstance()->changePassword($email, $password);

    if($PasswordChanged)
    {
        sendResponse("Ok", FALSE, 200);
    }
    else {
        sendResponse("Error while changing password", TRUE, 401);
    }   

?>
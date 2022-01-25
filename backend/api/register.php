<?php

    require_once "./config.php";
    require "Storage.php";
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);

    if (!$post || !isset($post['email'])|| !isset($post['username']) || !isset($post['password']) || empty($post['email']) || empty($post['username']) || empty($post['password'])) {
        sendResponse('Both username and password have to be set', TRUE, 401);
    }

    $email = $post['email'];
    $username = $post['username'];
    $password = $post['password'];

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)){
        sendResponse('Invalid email', TRUE, 401);
    }

    $minUsernameSize = Config::$MIN_USERNAME_SIZE;
    if (strlen($username) < $minUsernameSize) {
        sendResponse("Username must be at least $minUsernameSize characters in length", TRUE, 401);
    }

    if (!preg_match("#^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&_])[A-Za-z\d@$!%*?&_]{8,}$#", $password)) {
        sendResponse('Password must be at least 8 characters in length and contain at least one number, one upper case letter, one lower case letter and one special character.', TRUE, 401);
    }

    $uniqueEmail = Storage::getInstance()->isUniqueEmail($email);
    if(!$uniqueEmail)
    {
        sendResponse('Already registered with this email', TRUE, 401);
    }

    $uniqueUsername = Storage::getInstance()->isUniqueUsername($username);
    if(!$uniqueUsername)
    {
        sendResponse('Username is already used. Try different username', TRUE, 401);
    }

    $password = password_hash($password, PASSWORD_DEFAULT);
    $token = bin2hex(random_bytes(Config::$USER_TOKEN_SIZE));
    $error = Storage::getInstance()->registerUser($email, $username, $password, $token);
    if (!empty($error)) {
        sendResponse('This username is already taken', TRUE, 401);
    }
    else {
        sendResponse('Ok', FALSE, 200);
    }

?>
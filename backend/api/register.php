<?php

    require "Storage.php";
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);

    if (!$post || !isset($post['username']) || !isset($post['password']) || empty($post['username']) || empty($post['password'])) {
        sendResponse('Both username and password have to be set', TRUE, 401);
    }

    $username = $post['username'];
    $password = $post['password'];

    if (strlen($username) < 8) {
        sendResponse('Username must be at least 8 characters in length', TRUE, 401);
    }

    if (!preg_match("#.*^(?=.{8,20})(?=.*[a-z])(?=.*[A-Z])(?=.*[0-9])(?=.*\W).*$#", $password)) {
        sendResponse('Password must be at least 8 characters in length and contain at least one number, one upper case letter, one lower case letter and one special character.', TRUE, 401);
    }

    $error = Storage::getInstance()->registerUser($username, $password);
    if (!empty($error)) {
        sendResponse('This username is already taken', TRUE, 401);
    }
    else {
        sendResponse('Ok', FALSE, 200);
    }

?>
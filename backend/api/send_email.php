<?php

    require_once './../../vendor/autoload.php';
    require_once './config.php';
    require_once 'Storage.php';
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);

    if (!isset($post['email']) || !isset($post['username']) || empty($post['email'] || empty($post['username']))) {
        sendResponse("Both email and username have to be set", TRUE, 401);
    }

    $email = $post['email'];
    $username = $post['username'];

    $userIsValid = Storage::getInstance()->verifyEmailandUsername($email, $username);
    $token = Storage::getInstance()->getToken($email);
    
    if ($userIsValid) {
        //send email
        $transport = (new Swift_SmtpTransport('smtp.gmail.com', 587, 'tls'))
            ->setUsername(Config::$FORGOT_PASSWORD_USERNAME)
            ->setPassword(Config::$FORGOT_PASSWORD_PASSWORD);

        $mailer = new Swift_Mailer($transport);

        $prefixUrl = Config::$FORGOT_PASSWORD_URL;
        $message = (new Swift_Message('Change your password'))
            ->setFrom(Config::$FORGOT_PASSWORD_USERNAME)
            ->setTo($email)
            ->setBody("Click on the link to change your password: $prefixUrl" . $token . " .");
        
        $result = $mailer->send($message);

        if($result)
        {
            sendResponse("Ok", FALSE, 200);
        }

    }
    else {
        sendResponse("An invalid combination of email and username was entered", TRUE, 401);
    }    

?>
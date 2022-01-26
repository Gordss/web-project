<?php
    require_once "./config.php";
    require_once './../utils/send_response.php';

    $post = json_decode(file_get_contents("php://input"), true);

    $nolibrary = Config::$REMOVE_FORGOT_PASSWORD_FUNCTION;
    header("No-library: $nolibrary");

?>
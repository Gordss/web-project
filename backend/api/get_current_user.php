<?php
    session_start();

    http_response_code(200);
    if (isset($_SESSION['username']))
    {
        exit(json_encode(["logged" => TRUE, "username" => $_SESSION['username']]));
    }
    else
    {
        exit(json_encode(["logged" => FALSE]));
    }
?>
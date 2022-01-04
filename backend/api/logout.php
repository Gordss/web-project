<?php

    session_start();
    if (session_destroy()) {
        http_response_code(200);
        exit(json_encode(["success" => "Ok"]));
    }
    else
    {
        http_response_code(500);
        exit();
    }
?>
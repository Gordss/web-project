<?php

    function sendResponse($msg, $isError, $status)
    {
        http_response_code($status);
        if ($isError) {
            exit(json_encode(["error" => $msg]));
        }
        else {
            exit(json_encode(["success" => $msg]));
        }
    }

?>
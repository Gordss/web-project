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

    function respondWithBadRequest($reason)
    {
        http_response_code(400);
        exit(json_encode(["error" => $reason]));
    }
    
    function respondWithNotFound($reason)
    {
        http_response_code(404);
        exit(json_encode(["error" => $reason]));
    }
    
    function respondWithInternalServerError($reason)
    {
        http_response_code(500);
        echo 'Internal server error';
        Logger::log('Responding with 500. Reason: ' . $reason);
        exit(json_encode(["error" => $reason]));
    }

?>
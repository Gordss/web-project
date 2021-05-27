<?php

require "Storage.php";

if (!isset($_FILES['file'])) {
    respondWithBadRequest('No file uploaded');
}
authenticateUser();
verifyFileType();
try {
    $storage = Storage::getInstance();
    $archive = $storage->insertArchive($_FILES["file"]["tmp_name"], $_FILES['file']['name'], 1);
    echo $archive->toCSV();
} catch (Exception $e) {
    respondWithInternalServerError($e->getMessage());
}

function verifyFileType()
{
    $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
    foreach ($accepted_types as $mime_type) {
        if ($mime_type == $_FILES["file"]["type"]) {
            return;
        }
    }
    respondWithBadRequest('The uploaded file is not a zip archive');
}

function respondWithBadRequest($reason)
{
    http_response_code(400);
    echo $reason;
    die;
}

function respondWithInternalServerError($reason)
{
    http_response_code(500);
    echo 'Internal server error';
    error_log($reason, 3, 'errors.log');
    die;
}

function authenticateUser()
{
    session_start();
    if (!isset($_SESSION['username'])) {
        http_response_code(401);
        header('Location: login.php');
        die;
    }
}

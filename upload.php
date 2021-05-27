<?php

require "Storage.php";

if (!isset($_FILES['file'])) {
    respondWithBadRequest('No file uploaded');
}
$username = authenticateUser();
verifyFileType();
try {
    $storage = Storage::getInstance();
    $archive = $storage->insertArchive($_FILES["file"]["tmp_name"], $_FILES['file']['name'], $username);
    $options = [
        'include-name' => isset($_POST['include-name']) && $_POST['include-name'] === 'on',
        'include-parent-name' => isset($_POST['include-parent-name']) && $_POST['include-parent-name'] === 'on',
        'include-content-length' => isset($_POST['include-content-length']) && $_POST['include-content-length'] === 'on',
        'include-type' => isset($_POST['include-type']) && $_POST['include-type'] === 'on',
        'include-md5_sum' => isset($_POST['include-md5_sum']) && $_POST['include-md5_sum'] === 'on',

        'include-header' => isset($_POST['include-header']) && $_POST['include-header'] === 'on'
    ];
    echo $archive->toCSV($options);
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
    error_log($reason . '\n', 3, 'errors.log');
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
    return $_SESSION['username'];
}

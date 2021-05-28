<?php

require "Storage.php";

if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gets a previously uploaded archive's CSV representation
    if (!isset($_GET['id'])) {
        respondWithBadRequest('Missing ID query parameter');
    }
    $id = $_GET['id'];
    $archiveCSV = Storage::getInstance()->getArchiveCSV($id);
    if (!$archiveCSV) {
        respondWithNotFound("Archive with ID $id not found");
    }
    echo $archiveCSV;
    die;
}

const DEFAULT_OPTIONS = array(
    'included-fields' => array('name', 'parent-name', 'content-length', 'type', 'md5_sum'),
    'include-header' => true,
    'delimiter' => ','
);

if (!isset($_FILES['file'])) {
    respondWithBadRequest('No file uploaded');
}
$username = authenticateUser();
verifyFileType();
try {
    $storage = Storage::getInstance();
    $archive = $storage->insertArchive($_FILES["file"]["tmp_name"], $_FILES['file']['name'], $username);

    $options = parseOptions();
    echo $archive->toCSV($options);

    $appliedOptionsJSON = json_encode($options);
    header("X-Applied-Options: $appliedOptionsJSON");
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

function parseOptions(): array
{
    if (!isset($_POST['options'])) {
        return DEFAULT_OPTIONS;
    }
    $options = json_decode($_POST['options'], true);
    if (!$options) {
        return DEFAULT_OPTIONS;
    }
    foreach (DEFAULT_OPTIONS as $key => $value) {
        if (!array_key_exists($key, $options)) {
            $options[$key] = $value;
        }
    }
    return $options;
}

function respondWithBadRequest($reason)
{
    http_response_code(400);
    echo $reason;
    die;
}

function respondWithNotFound($reason)
{
    http_response_code(404);
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
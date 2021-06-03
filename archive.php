<?php

require "Storage.php";

$username = authenticateUser();

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

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') { // Deletes an archive from the DB
    $parsedQueryString = '';
    parse_str($_SERVER['QUERY_STRING'], $parsedQueryString);
    if (!array_key_exists('id', $parsedQueryString) || empty($parsedQueryString['id'])) {
        respondWithBadRequest('Missing ID query parameter');
    }
    $id = $parsedQueryString['id'];

    $success = Storage::getInstance()->deleteArchive($id);
    if (!$success) {
        respondWithNotFound("Archive with ID $id not found");
    }
    http_response_code(204);
    die;
}

// Otherwise, upload an archive and return its CSV representation

const DEFAULT_OPTIONS = array(
    'included-fields' => array('id', 'parent_id','name', 'parent-name', 'content-length', 'type', 'md5_sum','is_leaf'),
    'include-header' => true,
    'uppercase' => false,
    'delimiter' => ','
);
const MAX_FILE_BYTES_SIZE = 2097152;

if (!isset($_FILES['file'])) {
    respondWithBadRequest('No file uploaded');
}

if ($_FILES['file']['size'] > MAX_FILE_BYTES_SIZE) {
    respondWithBadRequest("The size of the uploaded archive must not exceed " . MAX_FILE_BYTES_SIZE . ' bytes.');
}

verifyFileType();
try {
    $storage = Storage::getInstance();
    $archive = $storage->insertArchive($_FILES["file"]["tmp_name"], $_FILES['file']['name'], $username);

    $options = parseOptions();
    verifyOptions($options);

    $appliedOptionsJSON = json_encode($options);
    header("X-Applied-Options: $appliedOptionsJSON");
    $csv = $archive->toCSV($options);
    echo $csv;


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

//you cannot choose to include parent_id field without inclusing is field
function verifyOptions($options)
{
    $includedFields = $options['included-fields'];
    if (!in_array('id', $includedFields) && in_array('parent_id', $includedFields)) {
        respondWithBadRequest('Chosen conversion options are invalid. Field "parent_id" can only be included if field "id" is included.');
    }
}

function authenticateUser()
{
    session_start();
    if (!isset($_SESSION['username'])) {
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

    for ($i = 0; $i < sizeof($options['included-fields']); $i++) {
        if (!in_array($options['included-fields'][$i], DEFAULT_OPTIONS['included-fields'])) {
            array_splice($options['included-fields'], $i, 1);
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
    Logger::log('Responding with 500. Reason: ' . $reason);
    die;
}
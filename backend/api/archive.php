<?php

require "./Storage.php";
require "./../utils/send_response.php";

$username = authenticateUser();

if ($_SERVER['REQUEST_METHOD'] === 'GET') { // Gets a previously uploaded archive's CSV representation
    if (!isset($_GET['id'])) {
        respondWithBadRequest('Missing ID query parameter');
    }

    $id = $_GET['id'];

    // archive.php?id=???&options=true     | returns conversion's options
    if (isset($_GET['options']) && $_GET['options'] == "true") {
        $options = Storage::getInstance()->getOptions($id);
        sendResponse($options, false, 200);
    }

    // archive.php?id=???&servername=true     | returns conversion's servername, and filename
    if (isset($_GET['servername']) && $_GET['servername'] == "true") {
        $options = Storage::getInstance()->getSourceData($id);
        sendResponse($options, false, 200);
    }

    // archive.php?id=???  | return the archive only
    $archiveCSV = Storage::getInstance()->getConversionCSV($id);
    if (!$archiveCSV) {
        respondWithNotFound("Archive with ID $id not found");
    }
    echo $archiveCSV;
    die;
}

if ($_SERVER['REQUEST_METHOD'] === 'DELETE') { // Deletes an archive from the DB
    $parsedQueryString = '';
    parse_str($_SERVER['QUERY_STRING'], $parsedQueryString);
    if (!array_key_exists('id', (array)$parsedQueryString) || empty($parsedQueryString['id'])) {
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
// POST request is handled here

const DEFAULT_OPTIONS = array(
    'input-data' => 'upload',
    'input-config' => 'textarea',
    'included-fields' => array('id', 'parent-id','name', 'parent-name', 'content-length', 'type', 'md5-sum','is-leaf', 'css', 'url'),
    'include-header' => true,
    'uppercase' => false,
    'delimiter' => ',',
    'is-leaf-numeric' => false,
    'url-prefix' => 'http://localhost/download.php?file=',
    'url-suffix' => '&force_download=true',
    'url-field-urlencoded' => 'id'
);
const MAX_FILE_BYTES_SIZE = 524288000;

if (!isset($_FILES['file'])) {
    respondWithBadRequest('No file uploaded');
}

if ($_FILES['file']['size'] > MAX_FILE_BYTES_SIZE) {
    respondWithBadRequest("The size of the uploaded archive must not exceed " . MAX_FILE_BYTES_SIZE . ' bytes.');
}

try {
    $storage = Storage::getInstance();
    $options = parseOptions();
    
    if(!array_key_exists('input-data', $options)) {
        respondWithBadRequest('The input-data field is missing');
    }

    switch ($options['input-data']) {
        case 'upload':
            $filename = $_FILES['file']['name'];
            $filenameTmp = $_FILES['file']['tmp_name'];
            $filetype = $_FILES['file']['type'];
            break;
        case 'history':
            $fileMeta = Storage::getInstance()->getSourceData($options['history-meta']);
            $filename = $fileMeta["ServerName"];
            $filenameTmp = $fileMeta["SourcePath"];
            $filetype = mime_content_type($filenameTmp);
            break;
        default:
            $opts = array(
                'http'=>array(
                'method'=>"GET",
                'header'=>"Accept-language: en\r\n" .
                            "Cookie: foo=bar\r\n"
                )
            );

            $dir = '../files/';
            $filename = explode('/', $options['input-data']);
            $filename = end($filename);
            $extension = explode('.', $filename);
            $extension = end($extension);
            $filenameTmp = $dir . $filename;
            
            $context = stream_context_create($opts);
            $downloadedFile = file_get_contents($options['input-data'], false, $context);
            file_put_contents($filenameTmp, $downloadedFile);
            $newFileName = md5_file($dir . $filename). '.' . $extension;
            $filetype = mime_content_type($filenameTmp);
            rename($filenameTmp, $dir . $newFileName);
            $filename = $newFileName;
            $filenameTmp = $dir . $filename;
            break;
    }

    if(verifyIsArchive($filetype)) {
        $conversion = $storage->insertConversion($filenameTmp, $filename, $username, $options, true);
    }
    else if(verifyIsCSV($filetype)) {
        $conversion = $storage->insertConversion($filenameTmp, $filename, $username, $options, false);
    }
    else {
        respondWithBadRequest('The uploaded file is not a zip archive or comma separated file');
    }
    
    $appliedOptionsJSON = json_encode($options);
    header("X-Applied-Options: $appliedOptionsJSON");
    //TODO: change to be with json_encode()
    echo $conversion->toCSV($options);
} catch (Exception $e) {
    respondWithInternalServerError($e->getMessage());
}

function verifyIsArchive($fileType): bool {
    $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
    foreach ($accepted_types as $mime_type) {
        if ($mime_type == $fileType) {
            return true;
        }
    }

    return false;
}

function verifyIsCSV($fileType): bool {
    $accepted_types = array('application/vnd.ms-excel', 'text/csv', 'text/x-csv', 'application/csv', 'application/x-csv', 'text/comma-separated-values', 'text/x-comma-separated-values');
    foreach ($accepted_types as $mime_type) {
        if ($mime_type == $fileType) {
            return true;
        }
    }

    return false;
}

function authenticateUser()
{
    session_start();
    if (!isset($_SESSION['username'])) {
        header('Location: ./../../frontend/pages/login.html');
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

    $includedFields = $options['included-fields'];

    //verify url values do not contain chosen delimiter
    if (in_array('url', $includedFields)) {
        if (!array_key_exists('url-prefix', $options)
            || !array_key_exists('url-suffix', $options)
            || !array_key_exists('url-field-urlencoded', $options)) {
            respondWithBadRequest('Chosen conversion options are invalid. If "url" field is added the following options must be defined: "url-prefix", "url-suffix", "url-field-urlencoded."');
        }

        if (!in_array($options['url-field-urlencoded'], array('id', 'name', 'content-length', 'md5-sum'))) {
            respondWithBadRequest('Chosen conversion options are invalid. Value of "url-field-urlencoded" must be one of the following fields: "id", "name", "content-length", "md5-sum".');
        }

        checkKeyForDelimiter('url-prefix', $options, $options['delimiter']);
        checkKeyForDelimiter('url-suffix', $options, $options['delimiter']);
    }

    //Const columns names cannot contain chosen delimiter
    if (array_key_exists('const-cols', $options)) {
        foreach ($options['const-cols'] as $col) {
            if (strpos($col, $options['delimiter']) != false) {
                respondWithBadRequest('Chosen conversion options are invalid. Field names cannot contain the chosen delimiter.');
            }
        }
    }

    // foreach (DEFAULT_OPTIONS as $key => $value) {
    //     if (!array_key_exists($key, $options)) {
    //         $options[$key] = $value;
    //     }
    // }

    for ($i = 0; $i < sizeof($options['included-fields']); $i++) {
        if (!in_array($options['included-fields'][$i], DEFAULT_OPTIONS['included-fields'])) {
            array_splice($options['included-fields'], $i, 1);
        }
    }

    //you cannot choose to include parent_id field without including is field
    if (!in_array('id', $includedFields) && in_array('parent-id', $includedFields)) {
        respondWithBadRequest('Chosen conversion options are invalid. Field "parent-id" can only be included if field "id" is included.');
    }

    //verify css values do not contain chosen delimiter
    if (in_array('css', $includedFields)) {
        checkKeyForDelimiter('css-directory', $options, $options['delimiter']);
        checkKeyForDelimiter('css-text-file', $options, $options['delimiter']);
        checkKeyForDelimiter('css-image-file', $options, $options['delimiter']);
        checkKeyForDelimiter('css-default', $options, $options['delimiter']);
    }

    //TODO: color options handling

    return $options;
}

function checkKeyForDelimiter($key, $array, $delimiter) {
    if (array_key_exists($key, $array)) {
        if (strpos($array[$key], $delimiter) != false) {
            respondWithBadRequest('Chosen conversion options are invalid. CSS and URL values cannot contain the chosen delimiter.');
        }
    }
}
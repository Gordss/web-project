<?php

require "Storage.php";

if ($_FILES['file']['name']) {
    verifyFileType();
    Storage::getInstance()->insertArchive($_FILES["file"]["tmp_name"], $_FILES['file']['name'], 1);
} else {
    echo 'No file uploaded';
}

function verifyFileType()
{
    $fileType = $_FILES["file"]["type"];
    $fileExtension = explode(".", $_FILES['file']['name'])[1]; // TODO maybe has an extension field
    if ($fileExtension !== 'zip') {
        echo "The file you are trying to upload is not a .zip file. Please try again.";
        die;
    }

    $accepted_types = array('application/zip', 'application/x-zip-compressed', 'multipart/x-zip', 'application/x-compressed');
    foreach ($accepted_types as $mime_type) {
        if ($mime_type == $fileType) {
            return;
        }
    }
    echo "The file you are trying to upload is $fileType, not an archive.";
    die;
}
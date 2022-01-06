<?php

    require "Storage.php";
    require "./../utils/send_response.php";

    session_start();
    $archives = Storage::getInstance()->fetchArchivesForUser($_SESSION['username']);

    if ($archives == false)
    {
        sendResponse("Error with getting the archives", true, 500);
    }

    $response = [];
    foreach ($archives as $archive) {
        $id = $archive['id'];
        $date = $archive['uploaded_at'];
        $name = $archive['name'];
        $md5_sum = $archive['md5_sum'];

        array_push($response, ['id' => $id, 'name' => $name, 'md5-sum' => $md5_sum, 'create-date' => $date]);
    }

    http_response_code(200);
    exit(json_encode($response));

?>
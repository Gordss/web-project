<?php

    require "Storage.php";
    require "./../utils/send_response.php";

    if ($_SERVER['REQUEST_METHOD'] === 'GET')
    {
        session_start();
        if(!isset($_SESSION['username']) || empty($_SESSION['username']))
        {
            sendResponse("Not authenticated user", true, 401);
        }

        // ./history.php?count=true  -> returns convertions count for the user
        if(isset($_GET['count']) && $_GET['count'] == "true")
        {
            $convertionsCount = Storage::getInstance()->getConvertionCountForUser($_SESSION['username']);
            sendResponse($convertionsCount, false, 200);
        }

        // ./history.php?perpage=<int>&offset=<int>  -> returns per page items with offset
        if(isset($_GET['perpage']) && isset($_GET['offset']))
        {
            $convertions = Storage::getInstance()->fetchArchivesForUser($_SESSION['username'], $_GET['perpage'], $_GET['offset']);
        }
        else
        {
            // ./history.php -> returns all items
            $convertions = Storage::getInstance()->fetchArchivesForUser($_SESSION['username']);
        }

        if ($convertions == false)
        {
            sendResponse("Error with getting the archives", true, 500);
        }

        sendConvertions($convertions);
    }   

    function sendConvertions($convertions)
    {
        $response = [];
        foreach ($convertions as $convertion) {
            $id = $convertion['Id'];
            $date = $convertion['CreateDate'];
            $name = $convertion['SourceName'].'.'.$convertion['SourceExtension'];
            $md5_sum = $convertion['Md5_Sum'];
            $source_path = $convertion['SourcePath'];

            array_push($response, ['id' => $id, 'name' => $name, 'md5-sum' => $md5_sum, 'create-date' => $date, 'source-path' => $source_path]);
        }

        http_response_code(200);
        exit(json_encode($response));
    }

?>
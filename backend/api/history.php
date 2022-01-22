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

        // ./history.php?count=true  -> returns conversions count for the user
        if(isset($_GET['count']) && $_GET['count'] == "true")
        {
            $conversionsCount = Storage::getInstance()->getConversionCountForUser($_SESSION['username']);
            sendResponse($conversionsCount, false, 200);
        }

        // ./history.php?perpage=<int>&offset=<int>  -> returns per page items with offset
        if(isset($_GET['perpage']) && isset($_GET['offset']))
        {
            $conversions = Storage::getInstance()->fetchArchivesForUser($_SESSION['username'], $_GET['perpage'], $_GET['offset']);
        }
        else
        {
            // ./history.php -> returns all items
            $conversions = Storage::getInstance()->fetchArchivesForUser($_SESSION['username']);
        }

        if ($conversions == false)
        {
            sendResponse("Error with getting the archives", true, 500);
        }

        sendConversions($conversions);
    }   

    function sendConversions($conversions)
    {
        $response = [];
        foreach ($conversions as $conversion) {
            $id = $conversion['Id'];
            $date = $conversion['CreateDate'];
            $name = $conversion['SourceName'].'.'.$conversion['SourceExtension'];
            $md5_sum = $conversion['Md5_Sum'];
            $source_path = $conversion['SourcePath'];

            array_push($response, ['id' => $id, 'name' => $name, 'md5-sum' => $md5_sum, 'create-date' => $date, 'source-path' => $source_path]);
        }

        http_response_code(200);
        exit(json_encode($response));
    }

?>
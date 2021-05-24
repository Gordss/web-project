<?php

require "model/Archive.php";
require "model/File.php";

const DIRECTORY_TYPE = "directory";
class Storage
{

    private $conn;
    private static $_instance;

    public static function getInstance(): Storage
    {
        if (!self::$_instance) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public function insertArchive($path, $archiveName, $userID)
    {
        $stmt = $this->conn->prepare('INSERT INTO web_project.archives (user_id) VALUES(?)');
        if (!$stmt->execute([$userID])) {
            echo 'Could not insert archive in DB';
            // TODO respond with 500
            die;
        }
        $archiveID = $this->conn->lastInsertId();

        $files = array(new File($archiveID, $archiveName, null, 0, DIRECTORY_TYPE));
        $zip = new ZipArchive();

        if (!$zip->open($path)) {
            echo "Could not open zip archive";
            // TODO repsond with 500
            die;
        }

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filePathInfo = pathinfo($zip->getNameIndex($i));
            if ($filePathInfo['dirname'] === '.') {
                $filePathInfo['dirname'] = "$archiveName";
            } else {
                $filePathInfo['dirname'] = "$archiveName/" . $filePathInfo['dirname'];
            }

            $dir = $filePathInfo['dirname'];
            $type = $filePathInfo['extension'] ?? DIRECTORY_TYPE;
            $baseName = $filePathInfo['basename'];
            $fullName = "$dir/$baseName";
            $contentLength = strlen($zip->getFromIndex($i));

            echo "$i) Directory: $dir, name: $fullName, type: $type, length: $contentLength <br>";

            array_push($files, new File($this->conn->lastInsertId(), $fullName, $dir, $contentLength, $type));
        }
        usort($files, "Storage::cmpFiles");

        $sql = <<<SQL
            INSERT INTO web_project.nodes (archive_id, name, parent_name, content_length, type) 
                VALUES(?, ?, ?, ?, ?)
            SQL;
        for ($i = 0; $i < sizeof($files); $i++) {
            $file = $files[$i];
            $success = $this->conn->prepare($sql)->execute([
                $archiveID,
                $file->getName(),
                $file->getParentName(),
                $file->getContentLength(),
                $file->getType()]);

            if (!$success) {
                echo 'Could not insert file with name ' . $file->getName();
                die;
            }
        }

    }

    private static function cmpFiles($file1, $file2)
    {
        return strcmp(strlen($file1->getName()), strlen($file2->getName()));
    }

//
//    public function fetchProducts(): array
//    {
//        $result = array();
//        $stmt = $this->conn->query("SELECT * FROM web_hw2.products");
//        while ($row = $stmt->fetch()) {
//            array_push($result, new Product($row["id"], $row["name"], $row["quantity"]));
//        }
//        return $result;
//    }

//    public function addProduct($id, $quantity)
//    {
//        $stmt = $this->conn->prepare("SELECT quantity FROM web_hw2.products WHERE id = ?");
//        $stmt->execute([$id]);
//
//        if ($stmt->rowCount() === 0) {
//            $stmt = $this->conn->prepare("INSERT INTO web_hw2.products (`id`, `quantity`) VALUES (?,?)");
//            $stmt->execute([$id, $quantity]);
//        } else {
//            $currentQuantity = $stmt->fetchAll()[0]['quantity'];
//            $stmt = $this->conn->prepare("UPDATE web_hw2.products SET quantity = ? WHERE id = ?");
//            $stmt->execute([$currentQuantity + $quantity, $id]);
//        }
//    }

    private function __construct()
    {
        $this->conn = new PDO("mysql:host=localhost", "root", "");
        $this->ensureTables();
    }

    private function ensureTables()
    {
        $query = file_get_contents('db/create_tables.sql');
        $this->conn->exec($query);
    }

}

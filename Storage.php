<?php

require "model/Archive.php";

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
        try {
            $stmt = $this->conn->prepare('INSERT INTO web_project.archives (user_id) VALUES(?)');
            $stmt->execute([$userID]);
        } catch (Exception $e) {
            error_log('Could not insert archive in DB. Reason: ' . $e->getMessage(), 3, 'errors.log');
            return null;
        }
        $archiveID = $this->conn->lastInsertId();

        $archive = new Archive($archiveName, $path);
        $files = $archive->getFiles();

        $sql = 'INSERT INTO web_project.nodes (archive_id, name, parent_name, content_length, type) VALUES(?, ?, ?, ?, ?)';
        for ($i = 0; $i < sizeof($files); $i++) {
            $file = $files[$i];
            try {
                $this->conn->prepare($sql)->execute([
                    $archiveID, $file->getName(), $file->getParentName(), $file->getContentLength(), $file->getType()]);
            } catch (Exception $e) {
                error_log('Could not insert file ' . $file->getName() . $e->getMessage(), 3, 'errors.log');
                return null;
            }
        }
        return $archive;
    }

    private function __construct()
    {
        try {
            $this->conn = new PDO("mysql:host=localhost", "root", "");
            $this->ensureTables();
        } catch (Exception $e) {
            error_log('Could not connect to DB: ' . $e->getMessage(), 3, 'errors.log');
        }
    }

    private function ensureTables()
    {
        $query = file_get_contents('db/create_tables.sql');
        $this->conn->exec($query);
    }

}

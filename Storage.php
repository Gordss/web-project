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
        $stmt = $this->conn->prepare('INSERT INTO web_project.archives (user_id) VALUES(?)');
        $stmt->execute([$userID]);
        $archiveID = $this->conn->lastInsertId();

        $archive = new Archive($archiveName, $path);
        $files = $archive->getFiles();

        $sql = 'INSERT INTO web_project.nodes (archive_id, name, parent_name, content_length, type) VALUES(?, ?, ?, ?, ?)';
        for ($i = 0; $i < sizeof($files); $i++) {
            $file = $files[$i];
            $this->conn->prepare($sql)->execute([
                $archiveID, $file->getName(), $file->getParentName(), $file->getContentLength(), $file->getType()]);
        }
        return $archive;
    }

    public function registerUser($username, $password): string
    {
        try {
            $this->conn->prepare('INSERT INTO web_project.users (username,password) VALUES (?, ?)')
                ->execute([$username, $password]);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return "";
    }

    public function verifyUserCredentials($username, $password): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT password FROM web_project.users WHERE username = ? AND password = ?');
            $stmt->execute([$username, $password]);
            $result = $stmt->fetch();
            return sizeof($result) > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, 'errors.log');
            return false;
        }
    }

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

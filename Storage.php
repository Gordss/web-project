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

    public function insertArchive($path, $archiveName, $username)
    {
        $stmt = $this->conn->prepare('INSERT INTO archives (user_id) VALUES(?)');
        $stmt->execute([$this->getUserIDByName($username)]);
        $archiveID = $this->conn->lastInsertId();

        $archive = new Archive($archiveName, $path);
        $files = $archive->getFiles();

        $sql = 'INSERT INTO nodes (archive_id, name, parent_name, content_length, type, md5_sum) VALUES(?, ?, ?, ?, ?, ?)';
        for ($i = 0; $i < sizeof($files); $i++) {
            $fields = array_merge([$archiveID], $files[$i]->getFields());
            $this->conn->prepare($sql)->execute($fields);
        }
        return $archive;
    }

    public function fetchArchivesForUser($username): array
    {
        $stmt = $this->conn->prepare('SELECT * FROM archives WHERE user_id = ?');
        $stmt->execute([$this->getUserIDByName($username)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArchiveCSV($archiveID)
    {
        $sql = 'SELECT name,parent_name,content_length,type,md5_sum FROM nodes WHERE archive_id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$archiveID]);
        $nodes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        if (sizeof($nodes) == 0) {
            return null;
        }
        $csv = 'name,parent-name,content-length,type,md5_sum' . PHP_EOL;
        foreach ($nodes as $node) {
            $csv .= implode(',', $node) . PHP_EOL;
        }
        return $csv;
    }

    public function registerUser($username, $password): string
    {
        try {
            $this->conn->prepare('INSERT INTO users (username,password) VALUES (?, ?)')
                ->execute([$username, $password]);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return "";
    }

    public function verifyUserCredentials($username, $password): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT password FROM users WHERE username = ? AND password = ?');
            $stmt->execute([$username, $password]);
            $result = $stmt->fetch();
            return $result && sizeof($result) > 0;
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, 'errors.log');
            return false;
        }
    }

    private function getUserIDByName($username): string
    {
        try {
            $stmt = $this->conn->prepare('SELECT id FROM users WHERE username = ?');
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            return $result ? $result['id'] : "";
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, 'errors.log');
            return "";
        }
    }

    private function __construct()
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $db = getenv('DB_NAME') ?: 'web_project';
        try {
            $this->conn = new PDO("mysql:dbname=$db;host=$host", getenv('DB_USER') ?: 'root', getenv('DB_PASS') ?: '');
        } catch (PDOException $e) {
            error_log($e->getMessage(), 3, 'errors.log');
            http_response_code(500);
            die;
        }

        $this->ensureTables();
    }

    private function ensureTables()
    {
        $query = file_get_contents('db/create_tables.sql');
        $this->conn->exec($query);
    }

}

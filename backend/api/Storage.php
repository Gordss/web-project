<?php

require "./../model/Archive.php";
require "Logger.php";

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

    public function insertArchive($path, $archiveName, $username, $options)
    {
        $stmt = $this->conn->prepare('INSERT INTO archives (user_id, options_json) VALUES(?, ?)');
        $stmt->execute([$this->getUserIDByName($username), json_encode($options)]);
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
        $sql = <<<SQL
        SELECT a.id, a.uploaded_at, n.md5_sum, n.name FROM archives a
            JOIN nodes n on n.archive_id=a.id AND ISNULL(n.parent_name)
        WHERE user_id = ?;
        SQL;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->getUserIDByName($username)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getArchiveCSV($archiveID): ?string
    {
        $sql = 'SELECT name,parent_name,content_length,type,md5_sum FROM nodes WHERE archive_id = ? ORDER BY name';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$archiveID]);
        $nodes = $stmt->fetchAll();
        if (sizeof($nodes) == 0) {
            return null;
        }

        $files = array();
        foreach ($nodes as $node) {
            array_push($files, new File($node['name'], $node['parent_name'], $node['content_length'], $node['type'], $node['md5_sum']));
        }

        $stmt = $this->conn->prepare('SELECT options_json FROM archives WHERE id = ?');
        $stmt->execute([$archiveID]);

        return Archive::fileListToCSV($files, json_decode($stmt->fetch()['options_json'], true));
    }

    public function getArchiveOptions($archiveID): ?string
    {
        $sql = 'SELECT options_json FROM archives WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$archiveID]);
        $node = $stmt->fetch();
        if ($node == null) {
            return null;
        }

        return $node['options_json'];
    }

    public function deleteArchive($id): bool
    {
        $sql = 'DELETE FROM archives WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        return $stmt->execute([$id]);
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
            Logger::log('Could not verify user credentials: ' . $e->getMessage(),);
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
            Logger::log("Could not find user with username ${username}: " . $e->getMessage());
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
            Logger::log('Could not connect to DB: ' . $e->getMessage());
            http_response_code(500);
            die;
        }

        $this->ensureTables();
    }

    private function ensureTables()
    {
        $query = file_get_contents('./../../db/create_tables.sql');
        $this->conn->exec($query);
    }

}

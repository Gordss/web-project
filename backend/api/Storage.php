<?php

require "./../model/Convertion.php";
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

    public function insertConvertion($path, $tempFileName, $username, $options)
    {
        $location = './../files/';
        if ( !file_exists( $location ) && !is_dir( $location ) ) {
            mkdir( $location );
        }
        $fileWihtoutExt = pathinfo($tempFileName)['filename'];
        $fileExtension = pathinfo($tempFileName)['extension'];

        $stmt = $this->conn->prepare('INSERT INTO Convertion (FK_User_Id, Options, SourcePath, SourceName, SourceExtension, Md5_Sum) VALUES (?, ?, ?, ?, ?, ?)');
        
        $stmt->execute([$this->getUserIDByName($username), json_encode($options), $location, $fileWihtoutExt, $fileExtension, md5_file($path)]);
        $convertionId = $this->conn->lastInsertId();

        $newPath = $location . $convertionId . '.' . $fileExtension;
        // move archive to server storage
        move_uploaded_file($path, $newPath);

        $convertion = new Convertion($tempFileName, $newPath);

        return $convertion;
    }

    public function fetchArchivesForUser($username): array
    {
        $sql = <<<SQL
        SELECT c.Id, c.CreateDate, c.Md5_Sum, c.SourceName, c.SourceExtension FROM Convertion c
            JOIN user u on u.Id=c.Fk_User_Id
        WHERE u.Id = ?;
        SQL;

        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$this->getUserIDByName($username)]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConvertionCSV($convertionId): ?string
    {
        $sql = 'SELECT SourcePath, SourceName, SourceExtension, Options FROM Convertion WHERE Id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$convertionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result == false) {
            return null;
        }

        $path = $result['SourcePath'] . $convertionId . '.' . $result['SourceExtension']; 
        $convertion = new Convertion($result['SourceName'] . '.' . $result['SourceExtension'], $path);

        // convert options to json
        $options = json_decode($result['Options'], true);

        return $convertion->toCSV($options);
    }

    public function getOptions($archiveID): ?string
    {
        $sql = 'SELECT Options FROM Convertion WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$archiveID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result == null) {
            return null;
        }

        return json_encode($result);
    }

    public function deleteArchive($id): bool
    {
        //delete from file explorer
        $sql2 = 'SELECT SourceExtension FROM Convertion WHERE id = ?';
        $stmt2 = $this->conn->prepare($sql2);
        $stmt2->execute([$id]);
        $result = $stmt2->fetchAll(PDO::FETCH_ASSOC);
        $file_pointer = './../files/'. $id . '.' . $result['SourceExtension'];
        unlink($file_pointer);

        //delete from database
        $sql = 'DELETE FROM Convertion WHERE id = ?';
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function registerUser($username, $password): string
    {
        try {
            $this->conn->prepare('INSERT INTO User (Username,Password) VALUES (?, ?)')
                ->execute([$username, $password]);
        } catch (PDOException $e) {
            return $e->getMessage();
        }
        return "";
    }

    public function verifyUserCredentials($username, $password): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT Password FROM User WHERE Username = ?');
            $stmt->execute([$username]);
            $result = $stmt->fetch();

            return $result && password_verify($password, $result["Password"]);
        } catch (PDOException $e) {
            Logger::log('Could not verify user credentials: ' . $e->getMessage(),);
            return false;
        }
    }

    private function getUserIDByName($username): string
    {
        try {
            $stmt = $this->conn->prepare('SELECT Id FROM User WHERE Username = ?');
            $stmt->execute([$username]);
            $result = $stmt->fetch();
            return $result ? $result['Id'] : "";
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

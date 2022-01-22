<?php

require "./../model/Conversion.php";
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

    public function findFileSameMd5($location, $targetFileMd5) {
        $fileSameMd5 = false;
        $files = scandir($location);
        foreach($files as $file) {
            if($file != '.' && $file && $file != '..') {
                if(md5_file($location.$file) == md5_file($targetFileMd5)) {
                    $fileSameMd5 = $file;
                }
            }
        }

        return $fileSameMd5;
    }

    public function insertConversion($path, $tempFileName, $username, $options)
    {
        $location = './../files/';
        if ( !file_exists( $location ) && !is_dir( $location ) ) {
            mkdir( $location );
        }
        $fileWihtoutExt = pathinfo($tempFileName)['filename'];
        $fileExtension = pathinfo($tempFileName)['extension'];

        $stmt = $this->conn->prepare('INSERT INTO Conversion (FK_User_Id, Options, SourcePath, SourceName, SourceExtension, Md5_Sum) VALUES (?, ?, ?, ?, ?, ?)');

        $fileSameMd5 = $this->findFileSameMd5($location, $path);

        if(!$fileSameMd5) {
            $newPath = $location . md5_file($path) . '.' . $fileExtension;
            // move archive to server storage
            move_uploaded_file($path, $newPath);
        }
        else {
            $newPath = $location . $fileSameMd5;
        }
        $stmt->execute([$this->getUserIDByName($username), json_encode($options), $newPath, $fileWihtoutExt, $fileExtension, md5_file($newPath)]);
        
        $conversion = new Conversion($tempFileName, $newPath);

        return $conversion;
    }

    public function getConversionCountForUser($username): int
    {
        $sql = 'SELECT COUNT(1) AS Count FROM Conversion c JOIN user u ON u.Id = c.Fk_User_Id WHERE u.Username = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$username]);

        return $stmt->fetch(PDO::FETCH_ASSOC)['Count'];
    }

    public function fetchArchivesForUser($username, $itemPerPage = null, $offset = null): array
    {
        $userId = $this->getUserIDByName($username);

        $sql = '
        SELECT c.Id, c.CreateDate, c.Md5_Sum, c.SourceName, c.SourceExtension, c.SourcePath
        FROM
            Conversion c
            JOIN user u on u.Id=c.Fk_User_Id
        WHERE u.Id = :id
        ORDER BY c.Id DESC';
        
        if ($itemPerPage != null && $offset != null)
        {
            $sql .= '
            LIMIT :itemPerPage
            OFFSET :offset';
        }

        $stmt = $this->conn->prepare($sql);
        $stmt->bindValue("id", $userId, PDO::PARAM_INT);

        if ($itemPerPage != null && $offset != null)
        {
            $stmt->bindValue("offset", $offset, PDO::PARAM_INT);
            $stmt->bindValue("itemPerPage", $itemPerPage, PDO::PARAM_INT);   
        }

        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConversionCSV($conversionId): ?string
    {
        $sql = 'SELECT SourcePath, SourceName, SourceExtension, Options FROM Conversion WHERE Id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$conversionId]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result == false) {
            return null;
        }

        $path = $result['SourcePath'] . $conversionId . '.' . $result['SourceExtension']; 
        $conversion = new Conversion($result['SourceName'] . '.' . $result['SourceExtension'], $result['SourcePath']);

        // convert options to json
        $options = json_decode($result['Options'], true);

        return $conversion->toCSV($options);
    }

    public function getOptions($archiveID): ?string
    {
        $sql = 'SELECT Options FROM Conversion WHERE id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$archiveID]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result == null) {
            return null;
        }
        return $result['Options'];
    }

    public function getToken($email): ?string
    {
        $sql = 'SELECT Token FROM User WHERE Email = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$email]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result == null) {
            return null;
        }
        return $result['Token'];
    }

    public function deleteArchive($id): bool
    {
        $sql = 'SELECT Md5_sum FROM Conversion WHERE Id = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$result['Md5_sum'])
        {
            return false;
        }
        $md5_sum = $result['Md5_sum'];

        // check if more conversions exist for the same zip (md5 checksum)
        $sql = 'SELECT COUNT(*) as Count FROM Conversion WHERE Md5_Sum = ?';
        $stmt = $this->conn->prepare($sql);
        $stmt->execute([$md5_sum]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if(!$result['Count'])
        {
            return false;
        }

        if((int)$result['Count'] === 1) 
        {
            //delete from file explorer
            $sql = 'SELECT SourcePath FROM Conversion WHERE Md5_Sum = ?';
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$md5_sum]);
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            unlink($result["SourcePath"]);
        }
        
        //delete from database
        $sql = 'DELETE FROM Conversion WHERE Id = ?';
        $stmt = $this->conn->prepare($sql);

        return $stmt->execute([$id]);
    }

    public function isUniqueEmail($email): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT Username FROM User WHERE Email = ?');
            $stmt->execute([$email]);
            $count = $stmt->rowCount();
            $flag = true;
            if($count == 0)
            {
                return true;
            }

            return false;

        } catch (PDOException $e) {
            Logger::log('Invalid combination of email and username: ' . $e->getMessage(),);
            return false;
        }
    }

    public function isUniqueUsername($username): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT Email FROM User WHERE Username = ?');
            $stmt->execute([$username]);
            $count = $stmt->rowCount();
            $flag = true;
            if($count == 0)
            {
                return true;
            }

            return false;

        } catch (PDOException $e) {
            Logger::log('Invalid combination of email and username: ' . $e->getMessage(),);
            return false;
        }
    }

    public function registerUser($email, $username, $password, $token): string
    {
        try {
            $this->conn->prepare('INSERT INTO User (Email,Username,Password,Token) VALUES (?, ?, ?, ?)')
                ->execute([$email, $username, $password, $token]);
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

    public function verifyEmailandUsername($email, $username): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT Username FROM User WHERE Email = ?');
            $stmt->execute([$email]);
            $count = $stmt->rowCount();
            $flag = true;
            if($count == 0)
            {
                $flag = false;
            }
            $result = $stmt->fetch();

            return $result && $flag && ($username === $result["Username"]);
        } catch (PDOException $e) {
            Logger::log('Invalid combination of email and username: ' . $e->getMessage(),);
            return false;
        }
    }

    public function verifyEmail($email): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT Email FROM User WHERE Email = ?');
            $stmt->execute([$email]);
            $count = $stmt->rowCount();
            $flag = true;
            if($count == 0)
            {
                $flag = false;
            }
            $result = $stmt->fetch();

            return $result && $flag;
        } catch (PDOException $e) {
            Logger::log('Invalid email' . $e->getMessage(),);
            return false;
        }
    }

    public function verifyToken($token): bool
    {
        try {
            $stmt = $this->conn->prepare('SELECT Email FROM User WHERE Token = ?');
            $stmt->execute([$token]);
            $count = $stmt->rowCount();
            $result = $stmt->fetch();
            if($count == 0)
            {
                return false;
            }

            return true;

        } catch (PDOException $e) {
            Logger::log('Could not verify user token: ' . $e->getMessage(),);
            return false;
        }
    }

    public function changePassword($email, $password): bool 
    {
        $token = bin2hex(random_bytes(50));
        try {
            $stmt = $this->conn->prepare('Update User SET Password = ?, Token = ? WHERE Email = ?');
            $stmt->execute([$password, $token, $email]);
            $result = $stmt->fetch();

            return $result;
        } catch (PDOException $e) {
            Logger::log('Unable to change password: ' . $e->getMessage(),);
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

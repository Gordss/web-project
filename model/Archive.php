<?php

require 'File.php';

class Archive
{
    private $files;

    public function __construct($archiveName, $path)
    {
        $zip = new ZipArchive();
        if (!$zip->open($path)) {
            error_log('Could not open zip archive', 3, 'errors.log');
        }
        $this->parseArchive($zip, $archiveName);
    }

    private function parseArchive($zip, $archiveName)
    {
        $this->files = array(new File($archiveName, null, 0, DIRECTORY_TYPE));
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filePath = pathinfo($zip->getNameIndex($i));
            if ($filePath['dirname'] === '.') {
                $filePath['dirname'] = "$archiveName";
            } else {
                $filePath['dirname'] = "$archiveName/" . $filePath['dirname'];
            }
            $dir = $filePath['dirname'];
            $baseName = $filePath['basename'];
            $file = new File("$dir/$baseName", $dir, strlen($zip->getFromIndex($i)), $filePath['extension'] ?? DIRECTORY_TYPE);
            array_push($this->files, $file);
        }
        usort($this->files, "Archive::cmpFiles");
    }

    public function toCSV(): string
    {
        $csv = "name,parent_name,content_length,type" . PHP_EOL;
        foreach ($this->files as $file) {
            $fields = array($file->getName(), $file->getParentName(), $file->getContentLength(), $file->getType());
            $csv .= implode(",", $fields) . PHP_EOL;
        }
        return $csv;
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    private static function cmpFiles($file1, $file2)
    {
        return strcmp(strlen($file1->getName()), strlen($file2->getName()));
    }

}
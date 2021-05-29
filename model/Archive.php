<?php

require 'File.php';

class Archive
{
    private $files;

    public function __construct($archiveName, $path)
    {
        $zip = new ZipArchive();
        if (!$zip->open($path)) {
            throw new Exception('Could not open zip archive at ' . $path);
        }
        $this->parseArchive($zip, $archiveName, $path);
    }

    private function parseArchive($zip, $archiveName, $path)
    {
        $this->files = array(new File($archiveName, null, 0, DIRECTORY_TYPE, md5_file($path)));
        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filePath = pathinfo($zip->getNameIndex($i));
            if ($filePath['dirname'] === '.') {
                $filePath['dirname'] = "$archiveName";
            } else {
                $filePath['dirname'] = "$archiveName/" . $filePath['dirname'];
            }
            $dir = $filePath['dirname'];
            $baseName = $filePath['basename'];
            $fileContent = $zip->getFromIndex($i);
            $file = new File("$dir/$baseName", $dir, strlen($fileContent), $filePath['extension'] ?? DIRECTORY_TYPE, md5($fileContent));
            array_push($this->files, $file);
        }
        usort($this->files, "Archive::cmpFiles");
    }

    public function toCSV($options): string
    {
        $csv = '';
        if ($options['include-header']) {
            $csv = implode($options['delimiter'], $options['included-fields']) . PHP_EOL;
        }

        foreach ($this->files as $file) {
            $csv .= implode($options['delimiter'], $file->getFields($options['included-fields'])) . PHP_EOL;
        }
        return $options['uppercase'] ? strtoupper($csv) : $csv;
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
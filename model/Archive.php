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

            $name = "$dir/$baseName";
            $contentLength = strlen($fileContent);
            $type = $filePath['extension'] ?? DIRECTORY_TYPE;
            $md5Sum = md5($fileContent);

            $file = new File($name, $dir, $contentLength, $type, $md5Sum);
            array_push($this->files, $file);
        }
    }

    public function toCSV($options): string
    {
        return self::fileListToCSV($this->files, $options);
    }

    public static function fileListToCSV($files, $options): string
    {
        usort($files, "Archive::cmpFiles");
        if (self::shouldGenerateIds($options)) {
            self::setFileIds($files);
        }

        $csv = '';
        if ($options['include-header']) {
            $csv = implode($options['delimiter'], $options['included-fields']);
            if (array_key_exists('const-cols', $options)) {
                $csv .= $options['delimiter'] . implode($options['delimiter'], $options['const-cols']);
            }

            $csv .= PHP_EOL;
        }

        foreach ($files as $file) {
            if (array_key_exists('is-leaf-numeric', $options)) {
                $file->setIsLeafNumeric($options['is-leaf-numeric']);
            }

            if (array_key_exists('skip-zip-filename', $options)) {
                $file->setSkipZipFilename($options['skip-zip-filename']);
            }

            if (array_key_exists('css-directory', $options)) {
                $file->setCssDirectory($options['css-directory']);
            }

            if (array_key_exists('css-text-file', $options)) {
                $file->setCssTextFile($options['css-text-file']);
            }

            if (array_key_exists('css-image-file', $options)) {
                $file->setCssImageFile($options['css-image-file']);
            }

            if (array_key_exists('css-default', $options)) {
                $file->setCssDefault($options['css-default']);
            }

            if (array_key_exists('url-prefix', $options)) {
                $file->setUrlPrefix($options['url-prefix']);
            }

            if (array_key_exists('url-suffix', $options)) {
                $file->setUrlSuffix($options['url-suffix']);
            }

            if (array_key_exists('url-field-urlencoded', $options)) {
                $file->setUrlField($options['url-field-urlencoded']);
            }

            $file_string = implode($options['delimiter'], $file->getFields($options['included-fields']));

            if (array_key_exists('const-cols', $options)) {
                $file_string .= implode('', array_fill(0, count($options['const-cols']), $options['delimiter']));
            }
            $file_string .= PHP_EOL;
            $csv .= $file_string;
        }

        return $options['uppercase'] ? strtoupper($csv) : $csv;
    }

    private static function setFileIds($files)
    {
        $i = 0;
        $fileNameToId = array();
        $fileNameToChildren = array();
        foreach ($files as $file) {
            $file->id = $i;
            $fileNameToId[$file->name] = $i;
            $fileNameToChildren[$file->parent_name] = true;
            $i++;
        }

        $fileNameToId[''] = NULL;

        foreach ($files as $file) {
            $parent_id = $fileNameToId[$file->parent_name];
            $is_leaf = true;

            if (array_key_exists($file->name, $fileNameToChildren)) {
                $is_leaf = false;
            }

            $file->parent_id = $parent_id;
            $file->is_leaf = $is_leaf;
        }
    }

    public function getFiles(): array
    {
        return $this->files;
    }

    private static function cmpFiles($file1, $file2)
    {
        return strcmp($file1->name, $file2->name);
    }

    private static function shouldGenerateIds($options): bool
    {
        $includedFields = $options['included-fields'];
        return in_array('id', $includedFields) || in_array('parent_id', $includedFields) || in_array('is_leaf', $includedFields);
    }

}
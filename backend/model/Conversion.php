<?php

require 'File.php';

class Conversion
{
    private $files;

    public function __construct($archiveName, $path, $isArchive)
    {
        $zip = new ZipArchive();
        if (!$zip->open($path)) {
            throw new Exception('Could not open zip archive at ' . $path);
        }
        $this->parse($zip, $archiveName, $path, $isArchive);
    }

    private function getAttributeId($attribute, $attrArray): int
    {
        for($index = 0; $index < count($attrArray); $index++)
        {
            if($attribute == $attrArray[$index])
            {
                return $index;
            }
        }
        
        return -1;
    }

    private function parse($zip, $archiveName, $path, $isArchive)
    {
        if($isArchive)
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
        else
        {
            $this->files = array();
            $csv = fopen($path, 'r');
            $column = fgetcsv($csv);
            $attr1Id = $this->getAttributeId('name', $column);
            $attr2Id = $this->getAttributeId('parent-name', $column);
            $attr3Id = $this->getAttributeId('content-length', $column);
            $attr4Id = $this->getAttributeId('type', $column);
            $attr5Id = $this->getAttributeId('md5-sum', $column);

            while(($line = fgetcsv($csv)) !== FALSE) 
            {
                $file = new File($line[$attr1Id], $line[$attr2Id], $line[$attr3Id], $line[$attr4Id], $line[$attr5Id]);
                array_push($this->files, $file);
            }
            fclose($csv);
        }
    }

    public function toCSV($options): string
    {
        return self::fileListToCSV($this->files, $options);
    }

    public static function fileListToCSV($files, $options): string
    {
        usort($files, "Conversion::cmpFiles");
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

            if (array_key_exists('file-type-color', $options)) {
                $fileColor = $options['file-type-color'];
                if (array_key_exists('text', $fileColor)) {
                    $file->setTextFileColor($fileColor['text']);
                }
                if (array_key_exists('image', $fileColor)) {
                    $file->setImageFileColor($fileColor['image']);
                }
                if (array_key_exists('directory', $fileColor)) {
                    $file->setDirectoryColor($fileColor['directory']);
                }
            }

            if(array_key_exists('regex-color', $options))
            {
                $regexColor = $options['regex-color'];
                if(array_key_exists('regex', $regexColor)){
                    $file->setRegex($regexColor['regex']);
                }
                if(array_key_exists('color', $regexColor)){
                    $file->setRegexColor($regexColor['color']);
                }
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
        return in_array('id', $includedFields) || in_array('parent-id', $includedFields) || in_array('is-leaf', $includedFields);
    }
}
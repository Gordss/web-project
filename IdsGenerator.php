<?php

require 'model/IndexedFile.php';

class IdsGenerator
{
    private $files;

    public function __construct($csvData)
    {
        $this->parseData($csvData);
    }

    public function getIndexedCsvData($options) : string {
        $csv = '';
        if ($options['include-header']) {
            $csv = implode($options['delimiter'], $options['included-fields']) . PHP_EOL;
        }

        foreach ($this->files as $file) {
            $csv .= implode($options['delimiter'], $file->getFields($options['included-fields'])) . PHP_EOL;
        }
        return $options['uppercase'] ? strtoupper($csv) : $csv;
    }

    private function parseData($csvData) {
        $dataLines = explode("\n", $csvData);

        $indexedFiles = array();
        $fileNameToId = array();

        $fileNameToChildren = array();

        for ($i = 1; $i < count($dataLines) - 1; $i++) {
            $string = trim(preg_replace('/\s+/', '', $dataLines[$i]));
            $fileData = explode(',', $string);
            

            $name = $fileData[0];
            $parent_name = $fileData[2];
            $type = $fileData[1];
            $length = $fileData[3];
            $md5_sum = $fileData[4];
            $indexedFile = new IndexedFile($i, $name, $parent_name, $type, $length, $md5_sum);
            array_push($indexedFiles, $indexedFile);

            $fileNameToId[$name] = $i;
            $fileNameToChildren[$parent_name] = true;
            
        }

        $fileNameToId[''] = NULL;

        $this->files = array();

        foreach ($indexedFiles as $file) {
            $parent_id = $fileNameToId[$file->getParentName()];
            $is_leaf = true;

            if (array_key_exists($file->getName(), $fileNameToChildren)) {
                $is_leaf = false;
            }

            $file->setParentId($parent_id);
            $file->setIsLeaf($is_leaf);

            array_push($this->files, $file);
        }

    }


}
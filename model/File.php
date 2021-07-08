<?php

class File
{
    public $id;
    public $parent_id;
    public $name;
    public $parent_name;
    public $content_length;
    public $type;
    public $md5_sum;
    public $is_leaf;

    private $isLeafNumeric = false;
    private $skipZipFilename = false;
    private $cssDirectory = null;
    private $cssTextFile = null;
    private $cssImageFile = null;
    private $cssDefault = null;
    public function __construct($name, $parent_name, $content_length, $type, $md5_sum)
    {
        $this->name = $name;
        $this->parent_name = $parent_name;
        $this->content_length = $content_length;
        $this->type = $type;
        $this->md5_sum = $md5_sum;
    }

    public function setIsLeafNumeric($value) {
        $this->isLeafNumeric = $value;
    }

    public function setSkipZipFilename($value) {
        $this->skipZipFilename = $value;
    }

    public function setCssDirectory($value) {
        $this->cssDirectory = $value;
    }

    public function setCssTextFile($value) {
        $this->cssTextFile = $value;
    }

    public function setCssImageFile($value) {
        $this->cssImageFile = $value;
    }

    public function setCssDefault($value) {
        $this->cssDefault = $value;
    }

    public function getFields($includedFields = array('name', 'parent-name', 'content-length', 'type', 'md5_sum')): array
    {
        $fields = array();
        foreach ($includedFields as $fieldName) {
            switch ($fieldName) {
                case 'id':
                    array_push($fields, $this->id);
                    break;
                case 'parent_id':
                    array_push($fields, $this->parent_id);
                    break;
                case 'name':
                    array_push($fields, self::constructNameValue($this->name, $this->skipZipFilename));
                    break;
                case 'parent-name':
                    array_push($fields, self::constructNameValue($this->parent_name, $this->skipZipFilename));
                    break;
                case 'content-length':
                    array_push($fields, $this->content_length);
                    break;
                case 'type':
                    array_push($fields, $this->type);
                    break;
                case 'md5_sum':
                    array_push($fields, $this->md5_sum);
                    break;
                case 'is_leaf':
                    array_push($fields, self::constructIsLeafValue($this->isLeafNumeric));
                    break;
                case 'css':
                    array_push($fields, self::constructCssValue());
                    break;
                default:
            }
        }
        return $fields;
    }

    private function constructIsLeafValue($isLeafNumeric) : string {
        if ($isLeafNumeric) {
            return $this->is_leaf ? '1' : '0';
        }
        return $this->is_leaf ? 'true' : 'false';
    }

    private function constructNameValue($nameValue, $skipZipFilename) {
        if ($this->id == 0) {
            return $nameValue;
        }
        if ($skipZipFilename) {
            if (strpos($nameValue, '/') !== false) {
                $updatedName = strstr($nameValue, '/');
                return substr($updatedName, 1);
            } else {
                return NULL;
            }
        }
        return $nameValue;
    }

    private function constructCssValue() {
        if (in_array($this->type, array('txt', 'md', 'doc', 'docx'))) {
            return $this->cssTextFile;
        }
        if (in_array($this->type, array('jpg', 'png', 'gif'))) {
            return $this->cssImageFile;
        }
        if ('directory' === $this->type) {
            return $this->cssDirectory;
        }

        return $this->cssDefault;
    }

}
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

    public function __construct($name, $parent_name, $content_length, $type, $md5_sum)
    {
        $this->name = $name;
        $this->parent_name = $parent_name;
        $this->content_length = $content_length;
        $this->type = $type;
        $this->md5_sum = $md5_sum;
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
                    array_push($fields, $this->name);
                    break;
                case 'parent-name':
                    array_push($fields, $this->parent_name);
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
                    array_push($fields, $this->is_leaf ? 'true' : 'false');
                    break;
                default:
            }
        }
        return $fields;
    }

}
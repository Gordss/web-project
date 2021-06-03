<?php

class File
{
    private $id;
    private $parent_id;
    private $name;
    private $parent_name;
    private $content_length;
    private $type;
    private $md5_sum;
    private $is_leaf;

    public function __construct($name, $parent_name, $content_length, $type, $md5_sum)
    {
        $this->name = $name;
        $this->parent_name = $parent_name;
        $this->content_length = $content_length;
        $this->type = $type;
        $this->md5_sum = $md5_sum;
    }

    public function getFields($includedFields = array('name','parent-name','content-length','type','md5_sum')): array
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

    public function getName()
    {
        return $this->name;
    }

    public function getParentName()
    {
        return $this->parent_name;
    }

    public function getContentLength()
    {
        return $this->content_length;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getMD5Sum()
    {
        return $this->md5_sum;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setParentId($id)
    {
        $this->parent_id = $id;
    }

    public function setIsLeaf($is_leaf)
    {
        $this->is_leaf = $is_leaf;
    }

}
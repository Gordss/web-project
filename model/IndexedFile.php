<?php

class IndexedFile
{
    private $id;
    private $parent_id;
    private $name;
    private $parent_name;
    private $content_length;
    private $type;
    private $md5_sum;
    private $is_leaf;

    public function __construct($id, $name, $parent_name, $content_length, $type, $md5_sum)
    {
        $this->id = $id;
        $this->name = $name;
        $this->parent_name = $parent_name;
        $this->content_length = $content_length;
        $this->type = $type;
        $this->md5_sum = $md5_sum;
    }

    public function getFields($includedFields = array('id','parent_id','name','parent-name','content-length','type','md5_sum','is_leaf')): array
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
                    array_push($fields, $this->boolToString($this->is_leaf));
                    break;    

                default:
            }
        }
        return $fields;
    }

    public function setId($id)
    {
        $this->id = $id;
    }

    public function setParentId($id)
    {
        $this->parent_id = $id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getParentName()
    {
        return $this->parent_name;
    }

    public function setIsLeaf($is_leaf)
    {
        $this->is_leaf = $is_leaf;
    }

    private function boolToString($value) : string {
        if ($value) {
            return 'true';
        }

        return 'false';
    }

}
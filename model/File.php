<?php

class File
{
    private $name;
    private $parent_name;
    private $content_length;
    private $type;
    private $md5_sum;

    public function __construct($name, $parent_name, $content_length, $type, $md5_sum)
    {
        $this->name = $name;
        $this->parent_name = $parent_name;
        $this->content_length = $content_length;
        $this->type = $type;
        $this->md5_sum = $md5_sum;
    }

    public function getFields($options = null): array
    {
        $fields = array();
        if (!$options || $options['include-name']) {
            array_push($fields, $this->name);
        }
        if (!$options || $options['include-parent-name']) {
            array_push($fields, $this->parent_name);
        }
        if (!$options || $options['include-content-length']) {
            array_push($fields, $this->content_length);
        }
        if (!$options || $options['include-type']) {
            array_push($fields, $this->type);
        }
        if (!$options || $options['include-md5_sum']) {
            array_push($fields, $this->md5_sum);
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

}
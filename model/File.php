<?php

class File
{
    private $name;
    private $parent_name;
    private $content_length;
    private $type;

    public function __construct($name, $parent_name, $content_length, $type)
    {
        $this->name = $name;
        $this->parent_name = $parent_name;
        $this->content_length = $content_length;
        $this->type = $type;
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

}
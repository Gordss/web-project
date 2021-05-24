<?php


class Archive
{
    private $id;
    private $user_id;
    private $uploaded_at;

    public function __construct($user_id, $uploaded_at)
    {
        $this->user_id = $user_id;
        $this->uploaded_at = $uploaded_at;
    }

    public function getUserId()
    {
        return $this->user_id;
    }


    public function getUploadedAt()
    {
        return $this->uploaded_at;
    }


}
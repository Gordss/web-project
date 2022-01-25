<?php

class Config {

    public static $DEFAULT_OPTIONS = array(
        'input-data' => 'upload',
        'input-config' => 'textarea',
        'included-fields' => array('id', 'parent-id','name', 'parent-name', 'content-length', 'type', 'md5-sum','is-leaf', 'css', 'url'),
        'include-header' => true,
        'uppercase' => false,
        'delimiter' => ',',
        'is-leaf-numeric' => false,
        'url-prefix' => 'http://localhost/download.php?file=',
        'url-suffix' => '&force_download=true',
        'url-field-urlencoded' => 'id'
    );
}

?>
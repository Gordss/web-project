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

    public static $MAX_FILE_BYTES_SIZE = 524288000; // 500MB

    public static $USER_TOKEN_SIZE = 50;
    public static $MIN_USERNAME_SIZE = 3;
    public static $REMOVE_FORGOT_PASSWORD_FUNCTION = TRUE; //set it to TRUE to remove library usage for changing password
    public static $FORGOT_PASSWORD_URL = "http://localhost/web-project/frontend/pages/validation.html?token=";
    public static $FORGOT_PASSWORD_USERNAME = "webconverter7@gmail.com";
    public static $FORGOT_PASSWORD_PASSWORD = "jkuvibuauzaxzfgs";
    public static $FILES_LOCATION = "../files/";
    public static $DEFAULT_DB_NAME = "web_project";
}

?>
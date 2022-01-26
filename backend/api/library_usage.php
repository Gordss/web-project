<?php
    require_once "./config.php";

    $nolibrary = Config::$REMOVE_FORGOT_PASSWORD_FUNCTION;
    header("No-library: $nolibrary");

?>
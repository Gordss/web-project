<?php

class Logger
{
    private const LOG_FILE = 'errors.log';

    public static function log($msg)
    {
        error_log($msg . PHP_EOL, 3, self::LOG_FILE);
    }
}
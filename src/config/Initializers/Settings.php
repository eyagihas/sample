<?php

namespace Initializers;

class Settings{

    /**
     * PHP周りの設定
     */
    public static function init($mode)
    {
        date_default_timezone_set('Asia/Tokyo');
        mb_internal_encoding('UTF-8');
        mb_regex_encoding('UTF-8');
        error_reporting(E_ALL | E_STRICT);
        if ($mode === 'production') {
            ini_set('display_errors', 'Off');
        } else {
            ini_set('display_errors', 'On');
        }
        ini_set('log_errors', 'On');
        ini_set('error_log', '../../../logs/Inner_Error.log');
    }
}
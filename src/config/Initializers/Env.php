<?php

namespace Initializers;

class Env
{
    private static $_instance = null;

    /**
     * 設定ファイルload
     */
    public static function load($mode)
    {
        if (is_null(self::$_instance)) {
            self::$_instance = 
                new \Dotenv\Dotenv(
                    __DIR__.DS.ucfirst ($mode));
            self::$_instance->load();
        }
    }
}
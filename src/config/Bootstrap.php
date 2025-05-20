<?php

class Bootstrap
{
    /**
     * 初期メソッド
     *
     */
    public static function init()
    {
        $httpDomain = $_SERVER['HTTP_HOST'];
        if ($httpDomain === 'localhost' || 
            strpos($httpDomain, 'localhost:') === 0) {
            $mode = 'local';
        } else if ($httpDomain === '192.168.11.200' || 
            strpos($httpDomain, '192.168.11.200:') === 0) {
            $mode = 'office';
        } else if ($httpDomain === 'plus.implant.ac' ||
            $httpDomain === 'plus.implant.ac:443') {
            $mode = 'production';
        } else if ($httpDomain === 'dentarest.com' ||
            $httpDomain === 'dentarest.com:443') {
            $mode = 'production';
        } else {
            throw new Exception('Incorrect HTTP_HOST:'.$httpDomain);
        }

        \Initializers\Env::load($mode);
        \Initializers\Settings::init($mode);
    }
}

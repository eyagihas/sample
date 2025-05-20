<?php

namespace Services;

class Curl
{
    public static function curl_request($url, $isPost = false, $params=[], &$errno, &$errmsg)
    {
        $curl = curl_init($url);

        if ($isPost) {
            curl_setopt($curl, CURLOPT_POST, TRUE);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $params);
        }
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, FALSE); 
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

        $response = curl_exec($curl);
        if (!$response) {
            $errmsg = curl_error($curl);
            $errno= curl_errno($curl);
        }

        curl_close($curl);
        return $response;
    }

    public static function get_status_code($url)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_HEADER, TRUE);
        curl_setopt($curl, CURLOPT_NOBODY, TRUE);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, TRUE);
        curl_setopt($curl, CURLOPT_TIMEOUT, 10);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, TRUE);

        $response = curl_exec($curl);
        return curl_getinfo($curl, CURLINFO_HTTP_CODE);
    }
}

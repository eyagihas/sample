<?php

namespace Services;

class Render
{
    public static function render($view, $response, $path, $attach = null)
    {
        if (is_null($attach)) {
            return $view->render($response, $path);
        } else {
            return $view->render($response, $path, $attach);
        }
    }

    public static function to_json($response, $data)
    {
		return $response->withJson($data, 200, JSON_PRETTY_PRINT);
    }

    public static function redirect($response, $url)
    {
        return $response->withRedirect($url);
    }
}

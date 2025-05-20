<?php

namespace Services;

class Logger
{
    public static function write($message)
    {
        $backtrace = debug_backtrace();

        $file = $backtrace[0]['file'];
        $line = $backtrace[0]['line'];
        $context = array('file' => $file, 'line' => $line);

        $lb = (is_array($message) || is_object($message)) ? "\n" : '';
        ob_start();
        var_dump($message);
        $expression = $lb.trim(ob_get_contents());
        ob_end_clean();

        $logger = \Application::getInstance()->getContainer()->get('log');
        $logger->addDebug($expression, $context);
    }
}

<?php

/* WorkflowyPHP - https://github.com/johansatge/workflowy-php */

spl_autoload_register(function ($namespaced_class)
{
    preg_match_all('/WorkflowyPHP\\\(Workflowy[a-zA-Z]{0,})/ ', $namespaced_class, $class);
    $path = rtrim(dirname(__FILE__), '/') . '/WorkflowyPHP/' . (!empty($class[1][0]) ? $class[1][0] : '') . '.php';
    if (is_readable($path))
    {
        require $path;
    }
});
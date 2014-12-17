<?php

    date_default_timezone_set('Europe/Paris');

    // for now, static (@todo)
    require_once 'WorkflowyPHP/Workflowy.php';
    require_once 'WorkflowyPHP/WorkflowyLogin.php';
    require_once 'WorkflowyPHP/WorkflowySession.php';
    require_once 'WorkflowyPHP/WorkflowyError.php';

    // @todo remove
    function printr($thing)
    {
        echo '<pre>';
        print_r($thing);
        echo '</pre>';
    }

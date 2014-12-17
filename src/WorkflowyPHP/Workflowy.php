<?php namespace WorkflowyPHP;

class Workflowy
{

    const LOGIN_URL     = 'https://workflowy.com/accounts/login/';
    const LOGIN_TIMEOUT = 5;

}

// @todo remove
function printr($thing)
{
    echo '<pre>';
    print_r($thing);
    echo '</pre>';
}

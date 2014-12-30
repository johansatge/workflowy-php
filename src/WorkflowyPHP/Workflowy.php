<?php

/* WorkflowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkflowyPHP;

use WorkflowyPHP\WorkflowyException;
use WorkflowyPHP\WorkflowySession;

class Workflowy
{

    /**
     * Tries to login using the given credentials
     * @param string $username
     * @param string $password
     * @throws WorkflowyException
     * @return string
     */
    public static function login($username, $password)
    {
        $transport = new WorkflowyTransport();
        $answer    = $transport->loginRequest($username, $password);
        if ($answer !== false)
        {
            return $answer;
        }
        throw new WorkflowyException('Could not open the session with those credentials');
    }

}

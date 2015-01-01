<?php

/* WorkFlowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkFlowyPHP;

use WorkFlowyPHP\WorkFlowyException;
use WorkFlowyPHP\WorkFlowySession;

class WorkFlowy
{

    /**
     * Tries to login using the given credentials
     * @param string $username
     * @param string $password
     * @throws WorkFlowyException
     * @return string
     */
    public static function login($username, $password)
    {
        $transport = new WorkFlowyTransport();
        $answer    = $transport->loginRequest($username, $password);
        if ($answer !== false)
        {
            return $answer;
        }
        throw new WorkFlowyException('Could not open the session with those credentials');
    }

}

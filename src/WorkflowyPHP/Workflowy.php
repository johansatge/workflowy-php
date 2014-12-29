<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace WorkflowyPHP;

use WorkflowyPHP\WorkflowyException;
use WorkflowyPHP\WorkflowySession;

class Workflowy
{

    /**
     * Tries to login using the given credentials
     * @todo move in a WorkflowyLogin class ?
     * @param string $username
     * @param string $password
     * @throws WorkflowyException
     * @return string
     */
    public static function login($username, $password)
    {
        if (empty($username) || empty($password) || !is_string($username) || !is_string($password))
        {
            throw new WorkflowyException('You must provide credentials as strings');
        }
        $answer = WorkflowyTransport::curl(WorkflowyTransport::LOGIN_URL, array('username' => $username, 'password' => $password, 'next' => ''), true, false);
        preg_match('#^Set-Cookie:\s*sessionid=([^;]*)#mi', $answer, $session_id_match);
        if (preg_match('#^Location:#mi', $answer) && !empty($session_id_match[1]))
        {
            return $session_id_match[1];
        }
        throw new WorkflowyException('Could not open the session with those credentials');
    }

}

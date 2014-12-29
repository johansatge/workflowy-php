<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace WorkflowyPHP;

class WorkflowyTransport
{

    const LOGIN_URL = 'https://workflowy.com/accounts/login/';
    const API_URL   = 'https://workflowy.com/%s';
    const TIMEOUT   = 5;

    private $sessionID;

    public function __construct($session_id = false)
    {
        if ($session_id !== false && (!is_string($session_id) || !preg_match('/^[a-z0-9]{32}$/', $session_id)))
        {
            throw new WorkflowyException('Invalid session ID');
        }
        $this->sessionID = $session_id;
    }

    /**
     * Makes an API request and returns the answer
     * @param string $method
     * @param array  $data
     * @throws WorkflowyException
     * @return array
     */
    public function requestAPI($method, $data = array())
    {
        if (empty($this->sessionID))
        {
            throw new WorkflowyException('A session ID is needed to make API calls');
        }
        if (!is_string($method) || !preg_match('/^[a-zA-Z-_]{1,}$/', $method) || !is_array($data))
        {
            throw new WorkflowyException('Invalid API request');
        }
        $raw_data = $this->curl(sprintf(self::API_URL, $method), $data, false);
        $json     = json_decode($raw_data, true);
        if ($json === null)
        {
            throw new WorkflowyException('Could not decode JSON');
        }
        return $json;
    }

    /**
     * Makes a login request and returns the resulting session ID
     * @param string $username
     * @param string $password
     * @throws WorkflowyException
     * @return bool
     */
    public function requestLogin($username, $password)
    {
        if (empty($username) || empty($password) || !is_string($username) || !is_string($password))
        {
            throw new WorkflowyException('You must provide credentials as strings');
        }
        $raw_data = $this->curl(self::LOGIN_URL, array('username' => $username, 'password' => $password, 'next' => ''), true);
        preg_match('#^Set-Cookie:\s*sessionid=([^;]*)#mi', $raw_data, $session_id_match);
        return preg_match('#^Location:#mi', $raw_data) && !empty($session_id_match[1]) ? $session_id_match[1] : false;
    }

    /**
     * Sends a CURL request to the server, by using the given POST data
     * @param string $url
     * @param array  $post_fields
     * @param bool   $return_headers
     * @throws WorkflowyException
     * @return array|string
     */
    private function curl($url, $post_fields, $return_headers)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HEADER, $return_headers ? true : false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);
        if (count($post_fields) > 0)
        {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $post_fields);
        }
        if (!empty($this->sessionID))
        {
            curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: sessionid=' . $this->sessionID));
        }
        $raw_data = curl_exec($ch);
        $error    = curl_error($ch);
        curl_close($ch);
        if (!empty($error))
        {
            throw new WorkflowyException($error);
        }
        return $raw_data;
    }

}

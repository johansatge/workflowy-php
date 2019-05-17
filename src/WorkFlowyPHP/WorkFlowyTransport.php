<?php

/* WorkFlowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkFlowyPHP;

class WorkFlowyTransport
{

    const LOGIN_URL = 'https://workflowy.com/ajax_login';
    const API_URL   = 'https://workflowy.com/%s';
    const TIMEOUT   = 5;

    private $sessionID;
    private $clientID;
    private $mostRecentOperationTransactionId;

    /**
     * Builds a transport object, by using the providden session ID if needed
     * The class manages all communications with the server (login, CRUD list requests, account requests..)
     * @param bool $session_id
     * @throws WorkFlowyException
     */
    public function __construct($session_id = false)
    {
        if ($session_id !== false && (!is_string($session_id) || !preg_match('/^[a-z0-9]{32}$/', $session_id)))
        {
            throw new WorkFlowyException('Invalid session ID');
        }
        $this->sessionID = $session_id;
    }

    /**
     * Performs a list request
     * @param string $action
     * @param array $data
     * @throws WorkFlowyException
     * @return array
     */
    public function listRequest($action, $data)
    {
        if (!is_string($action) || !is_array($data))
        {
            throw new WorkFlowyException('Invalid list request');
        }
        $this->apiRequest('push_and_poll', array(
            'client_id'      => $this->clientID,
            'client_version' => 14,
            'push_poll_id'   => 'MSQBGpdw', // @todo make this dynamic
            'push_poll_data' => json_encode(array((object)array(
                'most_recent_operation_transaction_id' => $this->mostRecentOperationTransactionId,
                'operations'                           => array((object)array('type' => $action, 'data' => (object)$data))
            )))
        ));
    }

    /**
     * Makes an API request and returns the answer
     * @param string $method
     * @param array $data
     * @throws WorkFlowyException
     * @return array
     */
    public function apiRequest($method, $data = array())
    {
        if (empty($this->sessionID))
        {
            throw new WorkFlowyException('A session ID is needed to make API calls');
        }
        if (!is_string($method) || !is_array($data))
        {
            throw new WorkFlowyException('Invalid API request');
        }
        $raw_data = $this->curl(sprintf(self::API_URL, $method), $data);
        $json     = json_decode($raw_data, true);
        if ($json === null)
        {
            throw new WorkFlowyException('Could not decode JSON');
        }
        if (!empty($json['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId']))
        {
            $this->mostRecentOperationTransactionId = $json['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId'];
            $this->clientID                         = $json['projectTreeData']['clientId'];
        }
        if (!empty($json['results'][0]['new_most_recent_operation_transaction_id']))
        {
            $this->mostRecentOperationTransactionId = $json['results'][0]['new_most_recent_operation_transaction_id'];
        }
        if (!empty($json['results'][0]['error']))
        {
            throw new WorkFlowyException('An error occurred when executing the API request');
        }
        return $json;
    }

    /**
     * Makes a login request and returns the session ID on success
     * @param string $username
     * @param string $password
     * @throws WorkFlowyException
     * @return bool
     */
    public function loginRequest($username, $password)
    {
        if (empty($username) || empty($password) || !is_string($username) || !is_string($password))
        {
            throw new WorkFlowyException('You must provide credentials as strings');
        }
        $headers = [];
        $raw_data = $this->curl(
            self::LOGIN_URL,
            array('username' => $username, 'password' => $password, 'next' => ''),
            $headers
        );

        if (!empty($headers['set-cookie']) && is_array($headers['set-cookie'])) {
            foreach ($headers['set-cookie'] as $cookie) {
                $matches = [];
                if (preg_match('`^sessionid=([a-z0-9]+);`mis', $cookie, $matches)) {
                    return $matches[1];
                }
            }
        }

        return false;
    }

    /**
     * Sends a CURL request to the server, by using the given POST data
     * @param string $url
     * @param array $post_fields
     * @throws WorkFlowyException
     * @return array|string
     */
    private function curl($url, $post_fields, &$headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, self::TIMEOUT);

        $headers = [];
        // Code from https://stackoverflow.com/a/41135574/696517
        // This function is called by curl for each header received
        curl_setopt($ch, CURLOPT_HEADERFUNCTION, function($curl, $header) use (&$headers) {
            $len = strlen($header);
            $header = explode(':', $header, 2);
            if (count($header) < 2) { // ignore invalid headers
                return $len;
            }

            $name = strtolower(trim($header[0]));
            if (!array_key_exists($name, $headers)) {
                $headers[$name] = [trim($header[1])];
            } else {
                $headers[$name][] = trim($header[1]);
            }
            return $len;
        });

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
            throw new WorkFlowyException($error);
        }
        return $raw_data;
    }

}

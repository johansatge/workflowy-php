<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace WorkflowyPHP;

class WorkflowyListCollection
{

    private $uniqid;
    private $transport;

    public function __construct($uniqid)
    {
        $this->uniqid    = $uniqid;
        $this->transport = new WorkflowyTransport($uniqid);
    }

    /**
     * Returns the main list
     * @return WorkflowyList
     */
    public function getList()
    {
        $data      = $this->transport->requestAPI('get_initialization_data');
        $raw_lists = array();
        if (!empty($data['projectTreeData']['mainProjectTreeInfo']['rootProjectChildren']))
        {
            $raw_lists = $data['projectTreeData']['mainProjectTreeInfo']['rootProjectChildren'];
        }
        return $this->parseList(array('id' => 'None', 'nm' => null, 'no' => null, 'cp' => null, 'ch' => $raw_lists));
    }

    /**
     * Recursively parses the given list and builds an object
     * @todo store the relation between a parent and its child (or move this in an other class)
     * @param array $raw_list
     * @return WorkflowyList
     */
    private function parseList($raw_list)
    {
        $raw_sublists = !empty($raw_list['ch']) && is_array($raw_list['ch']) ? $raw_list['ch'] : array();
        $sublists     = array();
        foreach ($raw_sublists as $raw_sublist)
        {
            $sublists[] = $this->parseList($raw_sublist);
        }
        return new WorkflowyList(array(
            'id'          => !empty($raw_list['id']) ? $raw_list['id'] : '',
            'name'        => !empty($raw_list['nm']) ? $raw_list['nm'] : '',
            'description' => !empty($raw_list['no']) ? $raw_list['no'] : '',
            'complete'    => !empty($raw_list['cp'])
        ), $sublists, $this->transport);
    }

    /*



    /*
     * Tries to start a new session by using the given session ID
     * @param string $session_id
     * @throws WorkflowyError
     *
    public function __construct($session_id)
    {
        $this->sessionID = $session_id;
        $data            = $this->request('get_initialization_data');
        if (!empty($data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId']))
        {
            $this->mostRecentOperationTransactionId = $data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId'];
            $this->clientID                         = $data['projectTreeData']['clientId'];
        }
        else
        {
            throw new WorkflowyError('Could not initialize session.');
        }
    }

    /*
     * Performs a list request
     * Called from a WorkflowyList object
     * @param string $action
     * @param array $data
     * @param string $client_id
     * @throws WorkflowyError
     * @return array
     *
    public function performListRequest($action, $data, $client_id)
    {
        if ($client_id !== $this->clientID)
        {
            throw new WorkflowyError('The current list cannot communicate with this object.');
        }
        $push_poll_data = json_encode(array(
            (object)array(
                'most_recent_operation_transaction_id' => $this->mostRecentOperationTransactionId,
                'operations'                           => array(
                    (object)array(
                        'type' => $action,
                        'data' => (object)$data
                    )
                )
            )
        ));
        $this->request('push_and_poll', array(
                'client_id'      => $this->clientID,
                'client_version' => 14,
                'push_poll_id'   => 'MSQBGpdw', // @todo make this dynamic
                'push_poll_data' => $push_poll_data
            )
        );
    }

    /*
     * Performs an API request
     * @param string $method
     * @param array $params
     * @return array
     *
    private function request($method, $params = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, sprintf(Workflowy::API_URL, $method));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
        curl_setopt($ch, CURLOPT_TIMEOUT, 5);
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('Cookie: sessionid=' . $this->sessionID));
        $raw_data = curl_exec($ch);
        curl_close($ch);
        $data = json_decode($raw_data, true);
        if (!empty($data['results'][0]['new_most_recent_operation_transaction_id']))
        {
            $this->mostRecentOperationTransactionId = $data['results'][0]['new_most_recent_operation_transaction_id'];
        }
        return is_array($data) ? $data : array();
    }

    */

}

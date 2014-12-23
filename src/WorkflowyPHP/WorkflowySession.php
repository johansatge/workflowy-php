<?php namespace WorkflowyPHP;

use WorkflowyPHP\WorkflowyList;
use WorkflowyPHP\WorkflowyError;

class WorkflowySession
{

    private $sessionID;
    private $clientID;
    private $mostRecentOperationTransactionId;

    /**
     * Tries to start a new session by using the given session ID
     * @param string $session_id
     * @throws WorkflowyError
     */
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

    /**
     * Gets the global list
     * @return WorkflowyList
     */
    public function getList()
    {
        $data      = $this->request('get_initialization_data');
        $raw_lists = !empty($data['projectTreeData']['mainProjectTreeInfo']['rootProjectChildren']) ? $data['projectTreeData']['mainProjectTreeInfo']['rootProjectChildren'] : array();
        return $this->parseList(array('id' => 'None', 'nm' => null, 'no' => null, 'cp' => null, 'ch' => $raw_lists));
    }

    /**
     * Parses recursively the given list and builds a WorkflowyList object
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
        ), $sublists, $this);
    }

    /**
     * Gets the account informations
     * @todo refactor this function ?
     * @return array
     */
    public function getAccount()
    {
        $data     = $this->request('get_initialization_data');
        $settings = array(
            'username'               => !empty($data['settings']['username']) ? $data['settings']['username'] : false,
            'theme'                  => !empty($data['settings']['theme']) ? $data['settings']['theme'] : false,
            'email'                  => !empty($data['settings']['email']) ? $data['settings']['email'] : false,
            'items_created_in_month' => !empty($data['projectTreeData']['mainProjectTreeInfo']['itemsCreatedInCurrentMonth']) ? intval($data['projectTreeData']['mainProjectTreeInfo']['itemsCreatedInCurrentMonth']) : false,
            'monthly_quota'          => !empty($data['projectTreeData']['mainProjectTreeInfo']['monthlyItemQuota']) ? intval($data['projectTreeData']['mainProjectTreeInfo']['monthlyItemQuota']) : false,
            'registration_date'      => !empty($data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds']) ? date('Y-m-d H:i:s', intval($data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds'])) : false,
        );
        return $settings;
    }

    /**
     * Performs a list request
     * Called from a WorkflowyList object
     * @param string $action
     * @param array $data
     * @return array
     */
    public function performListRequest($action, $data)
    {
        // @todo make communication private with WorkflowyList objects
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
        return $this->request('push_and_poll', array(
                'client_id'      => $this->clientID,
                'client_version' => 14,
                'push_poll_id'   => 'MSQBGpdw', // @todo make this dynamic
                'push_poll_data' => $push_poll_data
            )
        );
    }

    /**
     * Performs an API request
     * @param string $method
     * @param array $params
     * @return array
     */
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

}

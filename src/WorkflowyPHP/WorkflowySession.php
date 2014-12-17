<?php namespace WorkflowyPHP;

use WorkflowyPHP\WorkflowyError;

class WorkflowySession
{

    private $sessionID = '';
    private $mostRecentOperationTransactionId;

    /**
     * Tries to start a new session by using the given session ID
     * @todo check if session is valid
     * @param string $session_id
     */
    public function __construct($session_id)
    {
        $this->sessionID = $session_id;
    }

    public function getTree()
    {

    }

    public function moveBullet($id, $parent_id, $priority)
    {

    }

    public function createBullet($name, $description, $parent_id, $priority)
    {

    }

    public function completeBullet($id)
    {

    }

    public function deleteBullet()
    {

    }

    public function editBullet($id, $name, $description)
    {

    }

    public function getAccount()
    {
        $data = $this->request('get_initialization_data');

        $settings = array(
            'username'                   => !empty($data['settings']['username']) ? $data['settings']['username'] : false,
            'theme'                      => !empty($data['settings']['theme']) ? $data['settings']['theme'] : false,
            'email'                      => !empty($data['settings']['email']) ? $data['settings']['email'] : false,
            'items_created_in_month'     => '@todo', //itemsCreatedInCurrentMonth
            'monthly_quota'              => '@todo', //monthlyItemQuota
            'registration_date'          => '@todo', //dateJoinedTimestampInSeconds
            'most_recent_transaction_id' => '@todo' //$data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId']
        );
        return $settings;
    }

    public function getExpandedBullets()
    {
        // get currently expanded projects (serverExpandedProjectsList) (and toggle expand ? how ?)
    }

    private function request($method, $params = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://workflowy.com/' . $method);
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
        if (!empty($init_data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId']))
        {
            $this->mostRecentOperationTransactionId = $data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId'];
        }
        return $data;
    }

}

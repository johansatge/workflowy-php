<?php namespace WorkflowyPHP;

use WorkflowyPHP\WorkflowyError;

class WorkflowySession
{

    private $sessionID = '';
    private $mostRecentOperationTransactionId;

    /**
     * Tries to start a new session by using the given session ID
     * @param string $session_id
     */
    public function __construct($session_id)
    {
        $this->sessionID = $session_id;
        $this->initMostRecentTransactionID();
    }

    public function getTree()
    {
        // @todo
    }

    public function moveBullet($id, $parent_id, $priority)
    {
        // @todo
    }

    public function createBullet($name, $description, $parent_id, $priority)
    {
        // @todo
    }

    public function completeBullet($id)
    {
        // @todo
    }

    public function deleteBullet()
    {
        // @todo
    }

    public function editBullet($id, $name, $description)
    {
        // @todo
    }

    /**
     * Gets the most recent transaction ID
     * That identifier is needed for each request
     */
    private function initMostRecentTransactionID()
    {
        $data = $this->request('get_initialization_data');
        if (!empty($data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId']))
        {
            $this->mostRecentOperationTransactionId = $data['projectTreeData']['mainProjectTreeInfo']['initialMostRecentOperationTransactionId'];
        }
        else
        {
            throw new WorkflowyError('Could not initialise session.');
        }

    }

    /**
     * Gets the account informations
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

    public function getExpandedBullets()
    {
        // @todo
        // get currently expanded projects (serverExpandedProjectsList) (and toggle expand ? how ?)
    }

    /**
     * Performs an API request
     * @param string $method
     * @param array  $params
     * @return array
     */
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
        return is_array($data) ? $data : array();
    }

}

<?php

/* WorkflowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkflowyPHP;

class WorkflowyAccount
{

    private $transport;
    private $username;
    private $theme;
    private $email;
    private $itemsCreatedInMonth;
    private $monthlyQuota;
    private $registrationDate;

    /**
     * Builds a Workflowy account
     * The class holds the account informations associated to the given session
     * @param string $session_id
     * @throws WorkflowyException
     */
    public function __construct($session_id)
    {
        $this->transport           = new WorkflowyTransport($session_id);
        $data                      = $this->transport->apiRequest('get_initialization_data');
        $this->username            = !empty($data['settings']['username']) ? $data['settings']['username'] : '';
        $this->theme               = !empty($data['settings']['theme']) ? $data['settings']['theme'] : '';
        $this->email               = !empty($data['settings']['email']) ? $data['settings']['email'] : '';
        $this->itemsCreatedInMonth = !empty($data['projectTreeData']['mainProjectTreeInfo']['itemsCreatedInCurrentMonth']) ? intval($data['projectTreeData']['mainProjectTreeInfo']['itemsCreatedInCurrentMonth']) : 0;
        $this->monthlyQuota        = !empty($data['projectTreeData']['mainProjectTreeInfo']['monthlyItemQuota']) ? intval($data['projectTreeData']['mainProjectTreeInfo']['monthlyItemQuota']) : 0;
        $this->registrationDate    = !empty($data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds']) ? intval($data['projectTreeData']['mainProjectTreeInfo']['dateJoinedTimestampInSeconds']) : 0;
    }

    /**
     * Returns the username
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Returns the current theme name
     * @return string
     */
    public function getTheme()
    {
        return $this->theme;
    }

    /**
     * Returns the email address
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Returns the number of items created on the current month
     * @return int
     */
    public function getItemsCreatedInMmonth()
    {
        return $this->itemsCreatedInMonth;
    }

    /**
     * Gets the user monthly quota
     * @return int
     */
    public function getMonthlyQuota()
    {
        return $this->monthlyQuota;
    }

    /**
     * Gets the user registration date
     * Takes a date format, or "timestamp" to get the raw value
     * @param string $format
     * @throws WorkflowyException
     * @return string
     */
    public function getRegistrationDate($format = 'Y-m-d H:i:s')
    {
        if (is_string($format) && !empty($format))
        {
            return $format == 'timestamp' ? $this->registrationDate : date($format, $this->registrationDate);
        }
        throw new WorkflowyException('Invalid date format');
    }

}

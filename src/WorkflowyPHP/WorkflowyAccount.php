<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace WorkflowyPHP;

class WorkflowyAccount
{

    public function __construct()
    {
        // @todo
    }

    public function getEmail()
    {
        // @todo
    }

    public function getRegistrationDate()
    {
        // @todo
    }

    public function getTheme()
    {
        // @todo
    }

    public function getItemsCreatedInMmonth() // @todo move in the list class ?
    {
        // @todo
    }

    public function getMonthlyQuota()
    {
        // @todo
    }

    /*
     * public function getAccount()
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
     */

}

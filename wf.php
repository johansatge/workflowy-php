<meta charset="utf-8">
<?php

date_default_timezone_set('Europe/Paris');

require_once 'vendor/autoload.php';

/**
 * Sample usage
 */

use WorkflowyPHP\Workflowy;
use WorkflowyPHP\WorkflowyException;
use WorkflowyPHP\WorkflowyList;
use WorkflowyPHP\WorkflowyAccount;

/**
 * Session
 */

if (!empty($_GET['sessionid']))
{
    $session_id = $_GET['sessionid'];
}
else
{
    try
    {
        $session_id = Workflowy::login('workflowy1@yopmail.com', 'workflowy1');
        var_dump($session_id);
    }
    catch (WorkflowyException $e)
    {
        var_dump($e->getMessage());
    }
}

/**
 * Account
 */

/*
$account_request = new WorkflowyAccount($session_id);
var_dump($account_request->getUsername());
var_dump($account_request->getEmail());
var_dump($account_request->getRegistrationDate());
var_dump($account_request->getTheme());
var_dump($account_request->getItemsCreatedInMmonth());
var_dump($account_request->getMonthlyQuota());
exit;
*/

/**
 * Lists
 */

$list_request = new WorkflowyList($session_id);
$list = $list_request->getList();

$sublist = $list->search('/#tag1/i');
var_dump($sublist->getName());
$sublist->setDescription(date('d-m-Y H:i:s'));


/*

$sublist->getID();
$sublist->getName();
$sublist->getDescription();
$sublist->getParent();
$sublist->isComplete();
$sublist->getOPML();
$sublist->$list->search('my sub-sub-list');

$sublist->setName('my sublist');
$sublist->setDescription('my description');
$sublist->setParent($list, 2);
$sublist->setComplete(true || false);

*/
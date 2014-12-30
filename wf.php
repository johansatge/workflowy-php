<meta charset="utf-8">
<?php

date_default_timezone_set('Europe/Paris');

require_once 'vendor/autoload.php';

// for now, static (@todo autoloader)
$classes = glob('src/WorkflowyPHP/*.php');
foreach ($classes as $path)
{
    require_once $path;
}

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
        s($session_id);
    }
    catch (WorkflowyException $e)
    {
        s($e->getMessage());
    }
}

/**
 * Account
 */

$account_request = new WorkflowyAccount($session_id);
$account_request->getUsername();
$account_request->getEmail();
$account_request->getRegistrationDate();
$account_request->getTheme();
$account_request->getItemsCreatedInMmonth();
$account_request->getMonthlyQuota();
exit;

/**
 * Lists
 */

$list_request = new WorkflowyList($session_id);
$list = $list_request->getList();

$sublist = $list->search('test1 sub2');

s($sublist->getName());
$sublist->setDescription(date('d-m-Y H:i:s'));

/*

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
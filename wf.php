<meta charset="utf-8">
<?php

date_default_timezone_set('Europe/Paris');

// for now, static (@todo autoloader)
require_once 'src/WorkflowyPHP/Workflowy.php';
require_once 'src/WorkflowyPHP/WorkflowySession.php';
require_once 'src/WorkflowyPHP/WorkflowyList.php';
require_once 'src/WorkflowyPHP/WorkflowyError.php';

require_once 'vendor/autoload.php';

use WorkflowyPHP\Workflowy;
use WorkflowyPHP\WorkflowySession;

$session_id = !empty($_GET['sessionid']) ? $_GET['sessionid'] : Workflowy::login('workflowy1@yopmail.com', 'workflowy1');
s($session_id);

$session = new WorkflowySession($session_id);

$list = $session->getList();

$item = $list->searchList('test2 sub1 sub1');
s($item);

$item->setDescription(date('d-m-Y H:i:s'));
$item->setComplete(false);

// ------------------------------------------------------------------------------------------------------------------------

/**
 * Sample usage @TODO
 */

/**
 * Session
 */

$session_id = Workflowy::login('user@domain.org', 'password');
$client = Workflowy::open($session_id);

/**
 * Lists
 */

$list = $client->getList();
$sublist = $list->search('my sublist'); // allow regexps ?

$sublist->getName();
$sublist->getDescription();
$sublist->getParent();
$sublist->isComplete();
$sublist->getOPML();

$sublist->setName('my sublist');
$sublist->setDescription('my description');
$sublist->setParent($list, 2);
$sublist->setComplete(true || false);

/**
 * Account
 */

$account = $client->getAccount();

$account->getEmail();
$account->getRegistrationDate();
$account->getTheme();
$account->getItemsCreatedInMmonth();
$account->getMonthlyQuota();

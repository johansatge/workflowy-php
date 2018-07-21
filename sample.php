<?php

/**
 * Sample usage
 */

date_default_timezone_set('Europe/Paris');
$sep = str_repeat('-', 20) . "\n";
$date = date('Y-m-d H:i:s');

require_once 'vendor/autoload.php';

use WorkFlowyPHP\WorkFlowy;
use WorkFlowyPHP\WorkFlowyException;
use WorkFlowyPHP\WorkFlowyList;
use WorkFlowyPHP\WorkFlowyAccount;

/**
 * Session
 */

echo $sep;
$session_id = null;
try
{
    $session_id = WorkFlowy::login('workflowy-php@protonmail.com', require('sample_password.php'));
    echo 'Session ID: ' . $session_id . "\n";
}
catch (WorkFlowyException $e)
{
    echo 'Connection error: ' . $e->getMessage() . "\n";
}

/**
 * Account
 */

$account_request = new WorkFlowyAccount($session_id);

echo $sep;
echo 'Username:               ' . $account_request->getUsername() . "\n";
echo 'Email:                  ' . $account_request->getEmail() . "\n";
echo 'Registration date:      ' . $account_request->getRegistrationDate() . "\n";
echo 'Theme:                  ' . $account_request->getTheme() . "\n";
echo 'Items created in month: ' . $account_request->getItemsCreatedInMmonth() . "\n";
echo 'Monthly quota:          ' . $account_request->getMonthlyQuota() . "\n";

/**
 * Lists
 */

$list_request = new WorkFlowyList($session_id);
$list = $list_request->getList();

$sublist = $list->searchSublist('#Sample sublist#');

echo $sep;
echo 'List ID:              ' . $sublist->getID() . "\n";
echo 'List name:            ' . $sublist->getName() . "\n";
echo 'List modified date:   ' . date('Y-m-d H:i:s', $sublist->getLastModifiedTime()) . "\n";
echo 'List description:     ' . $sublist->getDescription() . "\n";
echo 'List completion:      ' . $sublist->isComplete() . "\n";
echo 'List completion time: ' . date('Y-m-d H:i:s', $sublist->getCompletedTime()) . "\n";

echo $sep;
echo 'List OPML: ' . $sublist->getOPML() . "\n";

$sublist->createSublist('Sample created sublist', 'With sample description ' . $date, 999);
$sublist->setName('Sample sublist ' . $date);
$sublist->setDescription('Sample description ' . $date);

// var_dump($sublist->getParent());
// var_dump($sublist->getOPML());
// var_dump($sublist->getSublists());

// $sublist->setParent($list, 2);
// $sublist->setComplete(true || false);

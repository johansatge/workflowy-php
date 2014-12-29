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
use WorkflowyPHP\WorkflowyAccountRequest;
use WorkflowyPHP\WorkflowyListRequest;

/**
 * Session
 */

try
{
    $uniqid = Workflowy::login('workflowy1@yopmail.com', 'workflowy1');
    s($uniqid);
}
catch (WorkflowyException $e)
{
    s($e->getMessage());
}

/**
 * Lists
 */

$list_request = new WorkflowyListRequest($uniqid);
$list = $list_request->getList();

$sublist = $list->search('one of my first level lists'); // allow regexps ? allow to return multiple results ?

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

/**
 * Account
 */

$account_request = new WorkflowyAccountRequest($uniqid);

$account_request->getEmail();
$account_request->getRegistrationDate();
$account_request->getTheme();
$account_request->getItemsCreatedInMmonth();
$account_request->getMonthlyQuota();

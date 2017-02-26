<?php

date_default_timezone_set('Europe/Paris');

require_once 'vendor/autoload.php';

/**
 * CLI parameters
 */
$session_id = false;
foreach($argv as $arg)
{
    preg_match_all('#^--session-id=(.*)#', $arg, $matches);
    if (!empty($matches[1][0]))
    {
        $session_id = $matches[1][0];
    }
}

/**
 * Sample usage
 */

use WorkFlowyPHP\WorkFlowy;
use WorkFlowyPHP\WorkFlowyException;
use WorkFlowyPHP\WorkFlowyList;
use WorkFlowyPHP\WorkFlowyAccount;

/**
 * Session
 */

if (empty($session_id))
{
    try
    {
        $session_id = WorkFlowy::login('workflowy1@yopmail.com', 'workflowy1');
        var_dump('Session ID: ' . $session_id);
    }
    catch (WorkFlowyException $e)
    {
        var_dump($e->getMessage());
    }
    exit;
}

/**
 * Account
 */

// $account_request = new WorkFlowyAccount($session_id);
// var_dump($account_request->getUsername());
// var_dump($account_request->getEmail());
// var_dump($account_request->getRegistrationDate());
// var_dump($account_request->getTheme());
// var_dump($account_request->getItemsCreatedInMmonth());
// var_dump($account_request->getMonthlyQuota());
// exit;

/**
 * Lists
 */

// $list_request = new WorkFlowyList($session_id);
// $list = $list_request->getList();

// $sublist = $list->searchSublist('#Test for timestamp#');
// $sublist->createSublist('creation test', date('m-d-Y H:i:s'), 999);

// var_dump($sublist->getID());
// var_dump($sublist->getName());
// var_dump($sublist->getDescription());
// var_dump($sublist->getParent());
// var_dump($sublist->isComplete());
// var_dump($sublist->getOPML());
// var_dump($sublist->getSublists());

// $sublist->setName('my sublist');
// $sublist->setDescription('my description');
// $sublist->setParent($list, 2);
// $sublist->setComplete(true || false);
// $sublist->createSublist('My list name', 'My list description', 999);

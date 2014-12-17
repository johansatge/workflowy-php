<?php

class WorkflowySessionTest extends PHPUnit_Framework_TestCase
{


    public function testInitSession()
    {
        $login      = new WorkflowyPHP\WorkflowyLogin();
        $session_id = $login->login('workflowy1@yopmail.com', 'workflowy1');
        $session    = new WorkflowyPHP\WorkflowySession($session_id);

        printr($session->getAccount());
    }

}

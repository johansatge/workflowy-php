<?php

class WorkflowyLoginTest extends PHPUnit_Framework_TestCase
{


    public function testCorrectLogin()
    {
        $workflowy = new WorkflowyPHP\WorkflowyLogin();
        $this->assertRegExp('#^[a-zA-Z0-9]{32}$#', $workflowy->login('workflowy1@yopmail.com', 'workflowy1'));
    }

    public function testIncorrectLogin()
    {
        $workflowy = new WorkflowyPHP\WorkflowyLogin();
        $this->assertEquals(false, $workflowy->login('workflowy1@yopmail.com', 'wrong_password'));
    }

}
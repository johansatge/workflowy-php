<?php

/* WorkflowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkflowyPHP;

class WorkflowyException extends \Exception
{

    protected $message;
    protected $code;

    /**
     * Builds a custom Workflowy exception
     * For now, uses the \Exception behavior
     * @param string $message
     * @param int    $code
     */
    public function __construct($message, $code = 0)
    {
        $this->message = is_string($message) ? $message : '';
        $this->code    = is_int($code) ? $code : 0;
    }

}

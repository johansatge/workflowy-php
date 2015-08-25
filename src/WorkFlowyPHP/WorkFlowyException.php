<?php

/* WorkFlowyPHP - https://github.com/johansatge/workflowy-php */

namespace WorkFlowyPHP;

class WorkFlowyException extends \Exception
{

    protected $message;
    protected $code;

    /**
     * Builds a custom WorkFlowy exception
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

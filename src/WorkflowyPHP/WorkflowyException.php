<?php

/*
 * This file is part of the WorkflowyPHP package.
 *
 * (c) Johan Satgé
 *
 * For the full copyright and license information, please view the LICENSE file that was distributed with this source code.
 */

namespace WorkflowyPHP;

class WorkflowyException extends \Exception
{

    protected $message;
    protected $code;

    public function __construct($message, $code = 0)
    {
        $this->message = $message;
        $this->code    = $code;
    }

}

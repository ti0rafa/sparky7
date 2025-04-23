<?php

namespace Sparky7\Logger\Notify;

use Sparky7\Api\Response\APIResponse;
use Sparky7\Logger\LoggerNotify;

/**
 * Browser handler.
 */
class Json extends LoggerNotify
{
    private $code;

    /**
     * Constructor.
     *
     * @param string $code HTTP status code
     */
    public function __construct($code)
    {
        $this->code = $code;
    }

    /**
     * Sends log notification.
     */
    public function send()
    {
        $APIResponse = new APIResponse();
        $APIResponse->rid = $this->pid;
        $APIResponse->status = false;
        $APIResponse->errors = $this->errors;
        $APIResponse->code = $this->code;
        $APIResponse->message = $this->message;

        $APIResponse->sendHeaders();
        echo $APIResponse->format();
    }
}

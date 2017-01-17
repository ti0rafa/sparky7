<?php

namespace Sparky7\Api;

/**
 * Response class.
 */
abstract class Response
{
    public $headers;

    public $rid;
    public $code;
    public $error;
    public $status;
    public $message;
    public $response;

    public $pretty;

    /**
     * Construct.
     */
    public function __construct()
    {
        $this->code = 200;
        $this->status = true;
        $this->pretty = false;
    }

    /**
     * toArray method.
     */
    abstract public function toArray();

    /**
     * Format response.
     */
    abstract public function format();

    /**
     * Get Response headers.
     */
    abstract public function getHeaders();

    /**
     * Send Headers.
     */
    abstract public function sendHeaders();
}

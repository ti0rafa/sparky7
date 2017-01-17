<?php

namespace Sparky7\Api\Response;

use Sparky7\Api\Response;
use Sparky7\Helper\Buffer;
use Sparky7\Helper\Json;

/**
 * API Response class.
 */
class APIResponse extends Response
{
    public $headers;

    public $rid;
    public $code;
    public $errors;
    public $status;
    public $message;
    public $response;

    public $pretty;

    /**
     * Construct.
     */
    final public function __construct()
    {
        $this->code = 200;
        $this->status = true;
        $this->pretty = false;
    }

    /**
     * toArray method.
     *
     * @return array Response
     */
    final public function toArray()
    {
        return [
            'rid' => $this->rid,
            'code' => $this->code,
            'errors' => $this->errors,
            'status' => $this->status,
            'message' => $this->message,
            'response' => $this->response,
        ];
    }

    /**
     * Not found - set response to not found.
     */
    final public function notFound()
    {
        $this->code = 404;
        $this->errors = null;
        $this->status = false;
        $this->message = 'Not found';
        $this->response = null;
    }

    /**
     * Format response into JSON.
     *
     * @param bool $die End on output
     */
    final public function format()
    {
        return ($this->pretty) ? Json::indent(json_encode($this->toArray())) : json_encode($this->toArray());
    }

    /**
     * Get Response headers.
     *
     * @return array Headers
     */
    final public function getHeaders()
    {
        $this->headers = [
            'Access-Control-Allow-Origin: *',
            'Access-Control-Allow-Headers: Authorization, Content-Type, X-Requested-With',
            'Access-Control-Allow-Methods: HEAD, POST, GET, DELETE, PUT',
            'Access-Control-Max-Age: 86400',
            'Cache-Control: max-age=0, no-store, no-cache',
            'Cache-Control: post-check=0, pre-check=0',
            'Content-type: application/json; charset=utf-8',
            'Expires: Mon, 26 Jul 1997 05:00:00 GMT',
            'Last-Modified: '.gmdate('D, d M Y H:i:s').' GMT',
            'Pragma: no-cache',
        ];

        if (!is_null($this->rid)) {
            $this->headers[] = 'Request-ID: '.$this->rid;
        }

        return $this->headers;
    }

    /**
     * Send Headers.
     */
    final public function sendHeaders()
    {
        if (headers_sent() || !in_array((int) $this->code, [200, 203, 400, 401, 402, 403, 404, 500, 501, 503])) {
            return;
        }

        Buffer::clear();
        Buffer::gzip();

        http_response_code($this->code);

        foreach ($this->getHeaders() as $header) {
            header($header);
        }
    }
}

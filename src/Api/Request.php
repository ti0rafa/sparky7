<?php

namespace Sparky7\Api;

use Ramsey\Uuid\Uuid;
use Sparky7\Api\Request\Headers;
use Sparky7\Api\Request\Incoming;
use Sparky7\Api\Request\Ip;

/**
 * Request class, gets its method, uri and params.
 */
class Request
{
    private $rid;
    private $content_type;
    private $headers;
    private $method;
    private $parameters;
    private $remote_ip;
    private $server;
    private $uri;

    /**
     * Constructor.
     */
    final public function __construct()
    {
        $this->rid = (string) Uuid::uuid4();

        /*
         * Grab incoming headers
         */

        $this->headers = Headers::detect();

        /*
         * Grab server
         */

        $this->server = $_SERVER['SERVER_NAME'];

        /*
         * Grab parameters
         */

        $this->parameters = [];
        foreach (Incoming::all() as $method => $collection) {
            foreach ($collection as $key => $value) {
                $this->setParam($key, $value, $method);
            }
        }

        /*
         * Decode URL into variables
         */

        $this->uri = parse_url(filter_var($_SERVER['REQUEST_URI'], FILTER_SANITIZE_URL));
        $this->uri = preg_split('[\\/]', $this->uri['path'], -1, PREG_SPLIT_NO_EMPTY);

        /*
         * IP address
         */

        $this->remote_ip = IP::detect();

        /*
         * Request method (overwrite if requested)
         *   - Check parameters for _method
         *   - Check headers for X-Http-Method-Override
         *   - Set default request method
         */

        $methods = ['DELETE', 'GET', 'POST', 'PUT'];

        $this->method = null;

        if (is_null($this->method) && isset($this->parameters['_method'])) {
            $method = strtoupper($this->parameters['_method']['value']);
            if (in_array($method, $methods)) {
                $this->method = $method;

                // remove from parameters
                unset($this->parameters['_method']);
            }
        } elseif (is_null($this->method) && isset($this->headers['X-Http-Method-Override'])) {
            $method = strtoupper($this->headers['X-Http-Method-Override']);
            if (in_array($method, $methods)) {
                $this->method = $method;
            }
        } else {
            $this->method = $_SERVER['REQUEST_METHOD'];
        }

        /*
         * Content type
         */

        $content_types = ['application/json', 'application/x-www-form-urlencoded', 'multipart/form-data-encoded'];

        $request = (isset($_SERVER['HTTP_CONTENT_TYPE'])) ? $_SERVER['HTTP_CONTENT_TYPE'] : null;

        foreach ($content_types as $content_type) {
            if (strpos($request, $content_type) !== false) {
                $this->content_type = $content_type;
            }
        }
    }

    /**
     * Get method.
     *
     * @param string $key Parameter name
     *
     * @return string Parameter value
     */
    final public function __get($key)
    {
        return (isset($this->{$key})) ? $this->{$key} : null;
    }

    /**
     * Is ajax request.
     *
     * @return bool [description]
     */
    final public function isAjax()
    {
        if (strpos(strtolower($this->content_type), 'application/json') !== false) {
            return true;
        }

        foreach (['X-Requested-With', 'Requested-With'] as $header) {
            if (isset($this->headers[header]) && strtolower($this->headers[header]) === 'xmlhttprequest') {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Payload.
     *
     * @return string Request payload
     */
    final public function getPayload()
    {
        return Incoming::payload();
    }

    /**
     * Get Header.
     *
     * @param string $key Header name
     *
     * @return string Header value
     */
    final public function getHeader($key)
    {
        return (isset($this->headers[$key])) ? trim($this->headers[$key]) : null;
    }

    /**
     * Is header set.
     *
     * @param string $key Get parameter
     *
     * @return bool
     */
    final public function isHeader($key)
    {
        return isset($this->headers[$key]);
    }

    /**
     * Get parameter.
     *
     * @param string $key           Get parameter
     * @param bool   $return_object Return parameter object
     *
     * @return any Parameter value
     */
    final public function getParam($key, $return_object = false)
    {
        if ($return_object) {
            return (isset($this->parameters[$key])) ? $this->parameters[$key] : null;
        } else {
            return (isset($this->parameters[$key])) ? $this->parameters[$key]['value'] : null;
        }
    }

    /**
     * Get Parameters (not defined as constants).
     *
     * @return array Parameter values
     */
    final public function getParameters()
    {
        $parameters = [];
        foreach ($this->parameters as $key => $parameter) {
            $parameters[$key] = $parameter;
        }

        return $parameters;
    }

    /**
     * Is parameter set.
     *
     * @param string $key    Get parameter
     * @param string $method Method
     *
     * @return bool
     */
    final public function isParam($key, $method = null)
    {
        if (is_null($method)) {
            return isset($this->parameters[$key]);
        } else {
            return (isset($this->parameters[$key]) && $this->parameters[$key]['method'] === $method) ? true : false;
        }
    }

    /**
     * Remove parameter.
     *
     * @param string $key Parameter name
     */
    final public function removeParam($key)
    {
        unset($this->parameters[$key]);
    }

    /**
     * Set parameter.
     *
     * @param string $key    Parameter name
     * @param any    $value  Parameter value
     * @param string $method Set method
     */
    final public function setParam($key, $value, $method = 'SET')
    {
        $this->parameters[$key] = [
            'method' => $method,
            'value' => $value,
        ];
    }

    /**
     * Replace URL.
     *
     * @param string $replace Search for
     * @param string $with    Replace with
     */
    final public function replaceURL($replace, $with)
    {
        $this->uri = str_replace($replace, $with, '/'.implode('/', $this->uri));
        $this->uri = parse_url(filter_var($this->uri, FILTER_SANITIZE_URL));
        $this->uri = preg_split('[\\/]', $this->uri['path'], -1, PREG_SPLIT_NO_EMPTY);
    }
}

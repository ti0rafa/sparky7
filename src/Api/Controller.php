<?php

namespace Sparky7\Api;

use Sparky7\Api\Response\APIResponse;
use Sparky7\Error\Exception\ExBadRequest;
use Sparky7\Event\Emitter;
use Sparky7\Helper\Constant;

/**
 * Controller abstract class.
 */
abstract class Controller
{
    use Emitter;

    public $Request;
    public $Session;

    public $response;

    /**
     * Construct method.
     */
    public function __construct(Request $Request)
    {
        $this->Request = $Request;
        $this->Session = new Constant();

        // Register events
        foreach ($this->events() as $key => $value) {
            $this->on($key, $value);
        }

        $this->emit('start');

        // Load parameters
        $this->load();
    }

    /**
     * Return parameters.
     *
     * @param string $key Parameter name
     *
     * @return Any Parameter value
     */
    public function __get($key)
    {
        return $this->Request->getParam($key);
    }

    /**
     * Debug Info.
     *
     * @return array Debug array
     */
    public function __debugInfo()
    {
        return [
            'Request' => $this->Request,
        ];
    }

    /**
     * Defined parameters.
     */
    abstract public function define();

    /**
     * Execute this logic.
     *
     * @return string Json response
     */
    abstract public function exec();

    /**
     * Events.
     *
     * @return array
     */
    public function events()
    {
        return [];
    }

    /**
     * Load controller parameters.
     */
    private function load()
    {
        $this->emit('before.load');

        foreach ($this->define() as $key => $Parameter) {
            try {
                $this->getParamObject($key, $Parameter);

                // Add it as a collection parameter
                $this->Request->setParam($key, $Parameter->value, $Parameter->method);
            } catch (ExBadRequest $ExBadRequest) {
                throw new ExBadRequest($ExBadRequest->getMessage() . ': ' . $key);
            }
        }

        $this->emit('after.load');
    }

    /**
     * Get incoming parameter.
     *
     * @param string $key Parameter name
     *
     * @return Parameter Parameter Object
     */
    public function getParamObject($key, ?Parameter $Parameter = null)
    {
        /*
         * Grab parameter
         */

        if (is_null($Parameter)) {
            $Parameter = $this->define()[$key];
        }

        // Not defined
        if (is_null($Parameter)) {
            return;
        }

        // Look for parameter in request
        $request_parameter = $this->Request->getParam($key, true);

        // Grab incoming value or set default
        if (!is_null($request_parameter)) {
            $method = $request_parameter['method'];
            $value = $request_parameter['value'];
        } else {
            $method = (is_null($Parameter->default)) ? $Parameter->method : 'default';
            $value = $Parameter->default;
        }

        // Validate parameters
        $Parameter->set($value, $method);
        $Parameter->validate();

        return $Parameter;
    }

    /**
     * Run controller.
     *
     * @return string Json response
     */
    public function run()
    {
        $this->emit('before.exec');

        $this->response = $this->exec();

        /*
         * Prepare Response
         */

        $Response = null;

        if (is_object($this->response) && $this->response instanceof Response) {
            $Response = $this->response;
        }

        if (is_null($Response)) {
            // If response is boolean, it sets the status to that value and returns null
            if (is_bool($this->response)) {
                $status = (bool) $this->response;
            } else {
                $status = true;
            }

            $Response = new APIResponse();
            $Response->rid = $this->Request->rid;
            $Response->status = $status;
            $Response->code = 200;
            $Response->response = $this->response;
            $Response->pretty = $this->Request->isParam('pretty'); // If pretty param is set parse the JSON output
        }

        $this->emit('after.exec');

        return $Response;
    }
}

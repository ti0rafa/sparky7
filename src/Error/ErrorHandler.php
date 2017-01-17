<?php

namespace Sparky7\Error;

use Closure;
use Exception;

/**
 * Error Handler class - acts a wrapper for Exceptions and Errors.
 */
class ErrorHandler
{
    private $callback;
    private $debug_info;

    /**
     * Constructor sets basic data.
     */
    final public function __construct(Closure $callback)
    {
        $this->callback = $callback;
        $this->debug_info = [];
    }

    /**
     * To Debug Info.
     *
     * @return array
     */
    final public function __debugInfo()
    {
        return $this->debug_info;
    }

    /**
     * To String method.
     *
     * @return string Error
     */
    final public function __toString()
    {
        return $this->debug_info['error']['message'].' on '.$this->debug_info['error']['file'];
    }

    /**
     * Invokes callback after error and stops execution.
     */
    final public function afterError()
    {
        if (is_callable($this->callback)) {
            $this->callback->__invoke($this->__debugInfo());
        }
    }

    /**
     * Error handler.
     *
     * @param string $code    Error number
     * @param string $message Error message
     * @param string $file    File that caused the error
     * @param string $line    Line of the error
     */
    final public function onError($code, $message, $file = '', $line = '')
    {
        $this->debug_info['error']['type'] = 'Error';
        $this->debug_info['error']['code'] = 500;
        $this->debug_info['error']['file'] = $file.' ('.$line.')';
        $this->debug_info['error']['message'] = $message;
        $this->debug_info['error']['server'] = gethostname();

        $this->debug_info['errors'] = null;

        $this->debug_info['trace'] = Trace::removeArguments(debug_backtrace());

        $this->afterError();
    }

    /**
     * Exception handler.
     *
     * @param Catchable $Catchable Catchable thrown
     */
    final public function onException($Catchable)
    {
        // $code = ($Catchable instanceof \Sparky7\Error\Exception) ? $Catchable->getCode() : 500;

        $code = (is_a($Catchable, 'Sparky7\Error\Exception')) ? $Catchable->getCode() : 500;

        $this->debug_info['error']['type'] = 'Exceptions';
        $this->debug_info['error']['exception'] = get_class($Catchable);
        $this->debug_info['error']['code'] = $code;
        $this->debug_info['error']['file'] = $Catchable->getFile().' ('.$Catchable->getLine().')';
        $this->debug_info['error']['message'] = $Catchable->getMessage();
        $this->debug_info['error']['server'] = gethostname();

        $this->debug_info['errors'] = null;

        $this->debug_info['trace'] = Trace::removeArguments(Trace::getTrace($Catchable));

        $Previous = $Catchable->getPrevious();
        if ($Previous) {
            $this->debug_info['previous']['exception'] = get_class($Previous);
            $this->debug_info['previous']['file'] = $Previous->getFile().' ('.$Previous->getLine().')';
            $this->debug_info['previous']['message'] = $Previous->getMessage();
        }

        if (method_exists($Catchable, 'getContext')) {
            $context = $Catchable->getContext();
            if (is_array($context) && count($context) > 0) {
                $this->debug_info['context'] = $context;
            }
        }

        if (method_exists($Catchable, 'getErrors')) {
            $errors = $Catchable->getErrors();
            if (is_array($errors) && count($errors) > 0) {
                $this->debug_info['errors'] = $errors;
            }
        }

        $this->afterError();
    }

    /**
     * Shutdown handler checks to see if any error was thrown.
     */
    final public function onShutdown()
    {
        $error = error_get_last();

        if (!is_null($error)) {
            $this->debug_info['error']['type'] = 'Error';
            $this->debug_info['error']['code'] = 500;
            $this->debug_info['error']['file'] = $error['file'].' ('.$error['line'].')';
            $this->debug_info['error']['message'] = $error['message'];
            $this->debug_info['error']['server'] = gethostname();

            $this->debug_info['errors'] = null;

            $this->debug_info['trace'] = Trace::removeArguments(debug_backtrace());

            $this->afterError();
        }
    }

    /**
     * Register default handlers.
     */
    final public function register()
    {
        set_error_handler(array($this, 'onError'));
        set_exception_handler(array($this, 'onException'));
        register_shutdown_function(array($this, 'onShutdown'));
    }
}

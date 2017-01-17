<?php

namespace Sparky7\Error;

class Trace
{
    /**
     * Search recursively for any previous exceptions.
     *
     * @param Catchable Thrown catchable
     * @param bool Simply trace (Remove parameters/arguments from methods/functions)
     *
     * @return array Trace array
     */
    final public static function getTrace($Catchable)
    {
        $trace = [];

        if (!is_null($Catchable->getPrevious())) {
            $previous_trace = self::getTrace($Catchable->getPrevious());
            foreach ($previous_trace as $value) {
                $trace[] = $value;
            }
        }

        foreach ($Catchable->getTrace() as $value) {
            $trace[] = $value;
        }

        return $trace;
    }

    /**
     * Remove arguments from the trace array.
     *
     * @param array $source Trace
     *
     * @return array Trace with its info flatten
     */
    final public static function removeArguments(array $source)
    {
        if (is_null($source)) {
            return [];
        }

        $trace = [];
        foreach ($source as $key => $value) {
            if (array_key_exists('file', $value)) {
                $trace[$key]['File'] = $value['file'].' ('.$value['line'].')';
            } else {
                continue;
            }

            if (array_key_exists('class', $value) &&
                array_key_exists('type', $value) &&
                array_key_exists('function', $value)) {
                $trace[$key]['Object'] = $value['class'].$value['type'].$value['function'].'()';
            }

            if (array_key_exists('class', $value)) {
                $trace[$key]['Object'] = $value['class'].'()';
            }

            if (array_key_exists('function', $value)) {
                $trace[$key]['Function'] = $value['function'].'()';
            }

            if (array_key_exists('args', $value) && count($value['args']) > 0) {
                switch (gettype($value)) {
                    case 'boolean':
                        $trace[$key]['Arguments'] = ($value) ? 'true' : 'false';
                        break;
                    case 'integer':
                    case 'double':
                    case 'string':
                        $trace[$key]['Arguments'] = $value;
                        break;
                    case 'resource':
                        $trace[$key]['Arguments'] = '{resource}';
                        break;
                    case 'NULL':
                        $trace[$key]['Arguments'] = 'NULL';
                        break;
                    case 'unknown type':
                        $trace[$key]['Arguments'] = '{unkown}';
                        break;
                    case 'array':
                    case 'object':
                        $trace[$key]['Arguments'] = '{'.gettype($value).'}';
                        break;
                }
            }
        }

        return $trace;
    }
}

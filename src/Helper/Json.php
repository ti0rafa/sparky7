<?php

namespace Sparky7\Helper;

use Sparky7\Error\Exception\ExBadRequest;

/**
 * JSON helper class.
 */
class Json
{
    /**
     * Decodes a Json.
     *
     * @param string $json       Json Data
     * @param bool   $throwError Throw an error if json can't be decoded
     *
     * @return array Returns and empty array if $json was not process correctly or the Json Data
     */
    public static function decode($json, $throwError = true)
    {
        $array = json_decode($json, true);
        $error = self::getMessageError();

        if (!is_null($error) && $throwError) {
            throw new ExBadRequest(self::getMessageError());
        } elseif (!is_null($error) && !$throwError) {
            return false;
        }

        return $array;
    }

    /**
     * Returns the last JSON decode error.
     *
     * @return string
     */
    public static function getMessageError()
    {
        if (JSON_ERROR_NONE === json_last_error()) {
            return null;
        }

        $error_msg = 'Unknown error';
        switch (json_last_error()) {
            case JSON_ERROR_DEPTH:
                $error_msg = 'Maximum stack depth exceeded';
                break;
            case JSON_ERROR_STATE_MISMATCH:
                $error_msg = 'Underflow or the modes mismatch';
                break;
            case JSON_ERROR_CTRL_CHAR:
                $error_msg = 'Unexpected control character found';
                break;
            case JSON_ERROR_SYNTAX:
                $error_msg = 'Syntax error, malformed JSON';
                break;
            case JSON_ERROR_UTF8:
                $error_msg = 'Malformed UTF-8 characters, possibly incorrectly encoded';
                break;
        }

        return $error_msg;
    }

    /**
     * Preattys a json var.
     *
     * @param string $json Json Data
     *
     * @return string Json encoded
     */
    public static function indent($json)
    {
        $result = '';
        $pos = 0;
        $strLen = strlen($json);
        $indentStr = "\t";
        $newLine = "\n";
        $prevChar = '';
        $outOfQuotes = true;

        for ($i = 0; $i < $strLen; ++$i) {
            $char = substr($json, $i, 1);

            if ('"' == $char && '\\' != $prevChar) {
                $outOfQuotes = !$outOfQuotes;
            } elseif (('}' == $char || ']' == $char) && $outOfQuotes) {
                $result .= $newLine;
                --$pos;

                for ($j = 0; $j < $pos; ++$j) {
                    $result .= $indentStr;
                }
            } elseif (false !== strpos(" \t\r\n", $char)) {
                continue;
            }

            $result .= $char;

            if (':' == $char && $outOfQuotes) {
                $result .= ' ';
            }

            if ((',' == $char || '{' == $char || '[' == $char) && $outOfQuotes) {
                $result .= $newLine;
                if ('{' == $char || '[' == $char) {
                    ++$pos;
                }

                for ($j = 0; $j < $pos; ++$j) {
                    $result .= $indentStr;
                }
            }

            $prevChar = $char;
        }

        return $result;
    }

    /**
     * Pretty print.
     *
     * @param string $json Json Data
     *
     * @return string JSON pretty print
     */
    public static function pretty($json)
    {
        if (version_compare(PHP_VERSION, '5.4', '>=')) {
            return json_encode($json, JSON_PRETTY_PRINT);
        }

        return self::indent($json);
    }

    /**
     * Load a file and returns an array of the JSON string stored on it.
     *
     * @param string $file_path
     *
     * @return array
     */
    public static function loadFile($file_path)
    {
        if (!is_file($file_path)) {
            return [];
        }

        return self::decode(file_get_contents($file_path));
    }
}

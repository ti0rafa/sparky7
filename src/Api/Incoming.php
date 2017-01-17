<?php

namespace Sparky7\Api;

use Sparky7\Error\Exception\ExError;
use Sparky7\Helper\Json;

/**
 * Incoming parameters class.
 */
class Incoming
{
    private static $json;
    private static $payload;

    /**
     * Get ALL variables.
     *
     * @return array Request variables
     */
    final public static function all()
    {
        return [
            'GET' => self::get(),
            'POST' => self::post(),
            'JSON' => self::json(),
            'FILE' => self::file(),
        ];
    }

    /**
     * URL Decode.
     *
     * @param array $data Data
     *
     * @return array Data
     */
    final private static function decode($data)
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = urldecode($value);
            } elseif (is_array($value) || is_object($value)) {
                $data[$key] = self::decode($value);
            } else {
                $data[$key] = $data;
            }
        }

        return $data;
    }

    /**
     * Detaches a file and sets it under the FILE scope.
     *
     * @param array $data Data
     *
     * @return array Data
     */
    final private static function detachFile(array $data)
    {
        $return = [];
        foreach ($data as $key => $value) {
            if (is_scalar($value) && strpos($value, 'data:') === 0 && strpos($value, ';base64,') !== false) {
                // Get data
                $start = strpos($value, ';base64,') + strlen(';base64,') - 1;
                $data = substr($value, $start, strlen($value));
                $data = str_replace(' ', '+', $data);

                // Get mime
                $start = strpos($value, 'data:') + strlen('data:');
                $mime = substr($value, $start, strpos($value, ';base64,') - $start);

                // Get extension
                $extension = self::fileExtension($mime);

                $name = uniqid().'.'.$extension;

                // File path
                $tmp_name = sys_get_temp_dir().'/'.$name;

                // Save file
                file_put_contents($tmp_name, base64_decode($data));

                $_FILES[$key] = array(
                    'error' => 0,
                    'name' => $name,
                    'tmp_name' => $tmp_name,
                    'type' => $mime,
                    'size' => filesize($tmp_name),
                );
            } else {
                $return[$key] = $value;
            }
        }

        return $return;
    }

    /**
     * Get file extension from mime.
     *
     * @param string $mime Mime type
     *
     * @return string Extension
     */
    final private static function fileExtension($mime)
    {
        switch ($mime) {
            case 'application/pdf':
                return 'pdf';
            case 'application/zip':
                return 'zip';
            case 'image/gif':
                return 'gif';
            case 'image/jpeg':
                return 'jpg';
            case 'image/png':
                return 'png';
            case 'text/csv':
                return 'csv';
            case 'text/css':
                return 'css';
            case 'text/html':
                return 'html';
            case 'text/javascript':
                return 'js';
            case 'text/plain':
                return 'txt';
            case 'text/xml':
                return 'xml';
            default:
                throw new ExError('Invalid mime type: '.$mime);
        }
    }

    /**
     * Get GET variables.
     *
     * @return array Request variables
     */
    final public static function get()
    {
        return self::detachFile(self::decode($_GET));
    }

    /**
     * Get JSON variables.
     *
     * @return array Request variables
     */
    final public static function json()
    {
        if (is_null(self::$json)) {
            self::$json = Json::decode(self::payload(), false);
            self::$json = (is_array(self::$json)) ? self::$json : [];
        }

        return self::detachFile(self::$json);
    }

    /**
     * Get FILE variables.
     *
     * @return array Request variables
     */
    final public static function file()
    {
        return $_FILES;
    }

    /**
     * Get Payload.
     *
     * @return
     */
    final public static function payload()
    {
        if (is_null(self::$payload)) {
            self::$payload = file_get_contents('php://input');
        }

        return self::$payload;
    }

    /**
     * Get POST variables.
     *
     * @return array Request variables
     */
    final public static function post()
    {
        return self::detachFile($_POST);
    }
}

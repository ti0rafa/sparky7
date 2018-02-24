<?php

namespace Sparky7\Orm;

use Exception;

class Mongo
{
    /**
     * Return a Mongo BSON Id.
     *
     * @param string $value Id
     *
     * @return object Mongo ID Object
     */
    final public static function id($value = null)
    {
        if (is_object($value) && is_a($value, 'MongoDB\BSON\ObjectId')) {
            return $value;
        } elseif (is_object($value) && is_a($value, 'MongoId')) {
            return $value;
        } elseif (is_string($value) && mb_strlen($value) > 0) {
            try {
                if (extension_loaded('mongodb')) {
                    return new \MongoDB\BSON\ObjectId($value);
                } elseif (extension_loaded('mongo')) {
                    return new \MongoId($value);
                }
            } catch (Exception $Exception) {
                return;
            }
        } else {
            return;
        }
    }

    /**
     * Validate and return a Mongo BSON Date.
     *
     * @param int $timestamp Timestamp
     *
     * @return object Mongo ID Object
     */
    final public static function date($value = null)
    {
        if (is_object($value) && is_a($value, 'MongoDB\BSON\UTCDateTime')) {
            return $value;
        } elseif (is_object($value) && is_a($value, 'MongoId')) {
            return $value;
        } else {
            if (extension_loaded('mongodb')) {
                return new \MongoDB\BSON\UTCDateTime($timestamp);
            } elseif (extension_loaded('mongo')) {
                return new \MongoDate($date_time);
            }
        }
    }
}

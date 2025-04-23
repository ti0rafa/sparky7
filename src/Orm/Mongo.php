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
    public static function id($value = null)
    {
        if (is_object($value) && is_a($value, 'MongoDB\BSON\ObjectId')) {
            return $value;
        } elseif (is_object($value) && is_a($value, 'MongoId')) {
            return $value;
        }

        try {
            if (extension_loaded('mongodb')) {
                return new \MongoDB\BSON\ObjectId($value);
            } elseif (extension_loaded('mongo')) {
                return new \MongoId($value);
            }
        } catch (Exception $Exception) {
            return;
        }
    }

    /**
     * Validate and return a Mongo BSON Date.
     *
     * @return object Mongo ID Object
     */
    public static function date($value = null)
    {
        if (is_object($value) && is_a($value, 'MongoDB\BSON\UTCDateTime')) {
            return $value;
        } elseif (is_object($value) && is_a($value, 'MongoId')) {
            return $value;
        } else {
            if (extension_loaded('mongodb')) {
                return new \MongoDB\BSON\UTCDateTime($value);
            } elseif (extension_loaded('mongo')) {
                return new \MongoDate($value);
            }
        }
    }
}

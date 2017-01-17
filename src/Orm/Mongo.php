<?php

namespace Sparky7\Orm;

use Exception;

class Mongo
{
    /**
     * Return a Mongo BSON Id.
     *
     * @param string $id Id
     *
     * @return object Mongo ID Object
     */
    final public static function id($id = null)
    {
        try {
            if (extension_loaded('mongodb')) {
                return new \MongoDB\BSON\ObjectID($id);
            } elseif (extension_loaded('mongo')) {
                return new \MongoId($id);
            }
        } catch (Exception $Exception) {
            return;
        }
    }

    /**
     * Validate and return a Mongo BSON Date.
     *
     * @param string $id Id
     *
     * @return object Mongo ID Object
     */
    final public static function date($timestamp = null)
    {
        if (extension_loaded('mongodb')) {
            return new \MongoDB\BSON\UTCDateTime($timestamp);
        } elseif (extension_loaded('mongo')) {
            return new \MongoDate($date_time);
        }
    }
}

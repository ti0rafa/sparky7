<?php

namespace Sparky7\Orm;

use MongoGridFS;
use MongoId;
use Sparky7\Event\Emitter;

/**
 * File Class.
 */
abstract class File
{
    use Emitter;

    private $MongoGridFS;

    private $_id;
    private $metadata;

    /**
     * Construct.
     *
     * @param MongoGridFS $MongoGridFS Mongo grid
     */
    public function __construct(MongoGridFS $MongoGridFS)
    {
        $this->MongoGridFS = $MongoGridFS;

        $this->metadata = [];
    }

    /**
     * Set value.
     *
     * @param string $key   Key
     * @param string $value Value
     */
    public function __set($key, $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Save file contents.
     *
     * @param string  $content     Content
     * @param MongoId $id_previous Previous mongo id
     *
     * @return MongoId MongoId
     */
    public function save($content, ?MongoId $id_previous = null)
    {
        /*
         * Remove previous file
         */

        if (!is_null($id_previous)) {
            $this->MongoGridFS->delete($id_previous);
        }

        /*
         * Metadata
         */

        $metadata = $this->metadata;

        $this->metadata = [];

        /*
         * Save file
         */

        return $this->MongoGridFS->storeBytes(
            zlib_encode($content, ZLIB_ENCODING_RAW, 9),
            $metadata
        );
    }
}

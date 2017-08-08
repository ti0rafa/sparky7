<?php

namespace Sparky7\Orm\Fl;

use MongoId;

/**
 * Mongo Legacy - File storage class.
 */
class MongoDB
{
    private $MongoGridFS;

    /**
     * Construct.
     *
     * @param MongoGridFS $MongoGridFS Mongo grid
     */
    public function __construct(MongoGridFS $MongoGridFS)
    {
        $this->MongoGridFS = $MongoGridFS;
    }

    /**
     * Insert file contents.
     *
     * @param string $content  Content
     * @param array  $metadata Metadata
     *
     * @return MongoId MongoId
     */
    final public function insert($content, array $metadata = null)
    {
        $stream = fopen('php://memory', 'r+');
        fwrite($stream, zlib_encode($content, ZLIB_ENCODING_RAW, 9));
        rewind($stream);

        return $this->MongoGridFS->uploadFromStream(
            $name,
            $stream,
            $metadata
        );
    }

    /**
     * Delete file.
     *
     * @param MongoId $_id [description]
     *
     * @return [type] [description]
     */
    final public function delete(MongoId $_id)
    {
        $this->MongoGridFS->delete($id_previous);
    }
}

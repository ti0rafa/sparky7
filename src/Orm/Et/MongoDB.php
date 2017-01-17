<?php

namespace Sparky7\Orm\Et;

/**
 * Mongo Legacy - Entity class.
 */
class MongoDB
{
    private $MongoCollection;

    /**
     * Constructor.
     */
    final public function __construct(\MongoDB\Collection $MongoCollection)
    {
        $this->MongoCollection = $MongoCollection;
    }

    /**
     * Insert document.
     *
     * @param array $document Document data
     */
    final public function insert(array $document)
    {
        $this->MongoCollection->insertOne($document);

        return true;
    }

    /**
     * Update document.
     *
     * @param array $query    Match criteria
     * @param array $document Document data
     */
    final public function update(array $query, array $document)
    {
        $this->MongoCollection->replaceOne($query, $document);

        return true;
    }

    /**
     * Delete document.
     *
     * @param array $query Match criteria
     */
    final public function delete(array $query)
    {
        $this->MongoCollection->deleteOne($query);

        return true;
    }
}

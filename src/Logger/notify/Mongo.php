<?php

namespace Sparky7\Logger\Notify;

use Sparky7\Logger\LoggerNotify;
use Sparky7\Orm\Mongo as MongoWrapper;
use MongoDB\Database;

/**
 * Mongo handler.
 */
class Mongo extends LoggerNotify
{
    private $MongoDb;
    private $collection_uuid;
    private $collection;

    /**
     * Constructor.
     *
     * @param MongoDb $MongoDb         MongoDb
     * @param string  $collection_uuid Collection UUID
     * @param string  $collection      Collection
     */
    final public function __construct(Database $MongoDb, $collection_uuid = 'log_uuid', $collection = 'log')
    {
        $this->MongoDb = $MongoDb;

        $this->collection_uuid = $collection_uuid;
        $this->collection = $collection;
    }

    /**
     * Sends log notification.
     */
    final public function send()
    {
        $document = $this->MongoDb->{$this->collection_uuid}->findOne([
            'pid' => $this->pid,
        ]);

        if ($document) {
            $_id = $document['_id'];

            $this->MongoDb->{$this->collection_uuid}->updateOne([
                '_id' => $_id,
            ], [
                '$set' => [
                    'code' => (isset($this->context['content']['code'])) ? $this->context['content']['code'] : null,
                    'tag' => $this->tags,
                    'keyword' => (is_array($this->tags)) ? array_values($this->tags) : null,
                    'dt_modify' => MongoWrapper::date(),
                ],
            ], [
                'multi' => true,
            ]);
        } else {
            $_id = MongoWrapper::id();

            $this->MongoDb->{$this->collection_uuid}->insertOne([
                '_id' => $_id,
                'pid' => $this->pid,
                'app' => $this->app,
                'description' => $this->description,
                'code' => (isset($this->context->content->code)) ? $this->context->content->code : null,
                'tag' => $this->tags,
                'keyword' => (is_array($this->tags)) ? array_values($this->tags) : null,
                'dt_create' => MongoWrapper::date(),
                'dt_modify' => null,
            ]);
        }

        $this->MongoDb->{$this->collection}->insertOne([
            'id_log_uuid' => $_id,
            'pid' => $this->pid,
            'count' => $this->count,
            'level' => $this->level,
            'message' => $this->message,
            'context' => $this->context,
            'dt_create' => MongoWrapper::date(),
            'dt_modify' => null,
        ]);
    }
}

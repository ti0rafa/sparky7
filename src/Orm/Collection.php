<?php

namespace Sparky7\Orm;

use Exception;
use Iterator;
use OuterIterator;
use Sparky7\Orm\Qb\MongoDB;

/**
 * Collection class.
 */
abstract class Collection implements OuterIterator
{
    /*
     * OuterIterator.
     */

    private $Iterator;

    /*
     * Other properties.
     */

    private $QB;

    /*
     * DB variables.
     */

    protected $MongoDB;
    protected $collection;
    protected $entity;

    /**
     * Call method.
     *
     * @param string $method    Method name
     * @param array  $arguments Arguments
     *
     * @return any
     */
    public function __call($method, ?array $arguments = null)
    {
        if (is_callable([$this->QB, $method])) {
            if ('find' === $method) {
                $this->Iterator = call_user_func_array([$this->QB, $method], $arguments);
            } else {
                return call_user_func_array([$this->QB, $method], $arguments);
            }
        }

        return false;
    }

    /**
     * Define events.
     */
    public function events()
    {
        return [];
    }

    /**
     * Init class.
     */
    public function init()
    {
        // Select Query Builder
        if ('MongoDB\Database' === get_class($this->MongoDB)) {
            $this->QB = new MongoDB($this->MongoDB, $this->collection);
        } elseif ('MongoDB' === get_class($this->MongoDB)) {
            $this->QB = new Qb\Mongo($this->MongoDB, $this->collection);
        } else {
            throw new Exception('Invalid mongo database');
        }

        // Register events
        if (method_exists($this, 'events')) {
            foreach ($this->events() as $key => $value) {
                $this->QB->on($key, $value);
            }
        }
    }

    /**
     * Create a new empty entity.
     *
     * @return object Entity
     */
    public function entity()
    {
        return new $this->entity($this->MongoDB->{$this->collection}, []);
    }

    /**
     * Exports collection into an array.
     *
     * @return array Collection data
     */
    public function toArray(): array
    {
        $collection = [];

        foreach ($this as $Entity) {
            $collection[] = $Entity->toArray();
        }

        return $collection;
    }

    /**
     * Get Next.
     */
    public function getNext()
    {
        if ('MongoDB' === get_class($this->MongoDB)) {
            $this->next();
        }

        $data = $this->current();

        return (is_null($data)) ? $this->entity() : $data;
    }

    /**
     * Get the current value.
     */
    public function current(): mixed
    {
        if (is_null($this->Iterator)) {
            return null;
        }

        $data = $this->Iterator->current();

        if (is_null($data)) {
            return null;
        }

        return new $this->entity($this->MongoDB->{$this->collection}, (array) $data);
    }

    /**
     * Get key.
     *
     * @return int Iterator key
     */
    public function key(): mixed
    {
        return $this->Iterator->key();
    }

    /**
     * Increments iterator key.
     *
     * @return bool
     */
    public function next(): void
    {
        $this->Iterator->next();
    }

    /**
     * Decrements iterator key.
     *
     * @return bool
     */
    public function rewind(): void
    {
        $this->Iterator->rewind();
    }

    /**
     * Check to see if current key is valid.
     */
    public function valid(): bool
    {
        return $this->Iterator->valid();
    }

    /**
     * Get Inner Iterator.
     *
     * @return Iterator Iterator
     */
    public function getInnerIterator(): ?Iterator
    {
        return $this->Iterator;
    }
}

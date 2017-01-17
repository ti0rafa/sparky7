<?php

namespace Sparky7\Orm\Qb;

use Sparky7\Api\Parameter;
use Sparky7\Event\Emitter;
use Sparky7\Orm\ORMException;
use Exception;
use IteratorIterator;
use MongoDate;
use MongoRegex;

/**
 * Mongo - Query builder class.
 */
class MongoDB
{
    use Emitter;

    private $collection;
    private $mongo;

    private $limit;
    private $projection;
    private $skip;
    private $sort;
    private $query;

    /**
     * Construct method.
     *
     * @param string $mongo      Mongo Cursor
     * @param string $collection Collection
     */
    final public function __construct(\MongoDB\Database $MongoDB, $collection)
    {
        if (is_null($collection)) {
            throw new Exception('Undefined collection name');
        }

        $this->collection = $collection;
        $this->MongoCollection = $MongoDB->{$collection};

        $this->clear();
    }

    /**
     * Aggregate framework method.
     *
     * @param array $pipeline Pipeline
     *
     * @return any Result
     */
    final public function aggregate(array $pipeline = [])
    {
        try {
            $result = $this->MongoCollection->aggregate($pipeline);
        } catch (Exception $Exception) {
            throw new ORMException($Exception->getMessage(), $Exception->getCode(), [
                'method' => __METHOD__,
                'collection' => $this->collection,
                'pipeline' => $pipeline,
                ]);
        }

        if ($result['ok'] == 1) {
            return $result['result'];
        } else {
            return [];
        }
    }

    /**
     * Count method.
     *
     * @return int Number of rows that match the search criteria
     */
    final public function count()
    {
        /*
         * Set query and options
         */

        $query = (is_array($this->query)) ? $this->query : [];

        $options = [];
        if (!isset($options['limit']) && is_numeric($this->limit) && $this->limit > 0) {
            $options['limit'] = $this->limit;
        }
        if (!isset($options['skip']) && is_numeric($this->skip) && $this->skip > 0) {
            $options['skip'] = $this->skip;
        }

        /*
         * Execute query
         */

        try {
            $result = $this->MongoCollection->count($query, $options);
        } catch (Exception $Exception) {
            throw new ORMException($Exception->getMessage(), $Exception->getCode(), [
                'method' => __METHOD__,
                'collection' => $this->collection,
                'query' => $query,
                ]);
        }

        $this->clear();

        return $result;
    }

    /**
     * Get One Method.
     *
     * @return any Result
     */
    final public function findOne()
    {
        /*
         * Set query and options
         */

        $query = (is_array($this->query)) ? $this->query : [];

        $options = [];
        if (!isset($options['projection']) && is_array($this->projection)) {
            $options['projection'] = $this->projection;
        }

        /*
         * Events
         */

        $this->emit('before.search', [
            $query,
            $options,
            ]);

        /*
         * Execute query
         */

        try {
            return $this->MongoCollection->findOne(
                $query,
                $options
                );
        } catch (Exception $Exception) {
            throw new ORMException($Exception->getMessage(), $Exception->getCode(), [
                'method' => __METHOD__,
                'collection' => $this->collection,
                'select' => $projection,
                'where' => $query,
                ]);
        }
    }

    /**
     * Search Method.
     *
     * @return any Result
     */
    final public function find()
    {
        /*
         * Set query and options
         */

        $query = (is_array($this->query)) ? $this->query : [];

        $options = [];
        if (!isset($options['limit']) && is_numeric($this->limit) && $this->limit > 0) {
            $options['limit'] = $this->limit;
        }
        if (!isset($options['projection']) && is_array($this->projection) && count($this->projection) > 0) {
            $options['projection'] = $this->projection;
        }
        if (!isset($options['skip']) && is_numeric($this->skip) && $this->skip > 0) {
            $options['skip'] = $this->skip;
        }
        if (!isset($options['sort']) && is_array($this->sort) && count($this->sort) > 0) {
            $options['sort'] = $this->sort;
        }

        /*
         * Events
         */

        $this->emit('before.search', [
            $query,
            $options,
        ]);

        try {
            // Search
            $cursor = $this->MongoCollection->find(
                $query,
                $options
            );

            // Clear query
            $this->clear();

            // Create Iterator
            $IteratorIterator = new IteratorIterator($cursor);
            $IteratorIterator->rewind();

            return $IteratorIterator;
        } catch (Exception $Exception) {
            throw new ORMException($Exception->getMessage(), $Exception->getCode(), [
                'method' => __METHOD__,
                'collection' => $this->collection,
                'limit' => $this->limit,
                'skip' => $this->skip,
                'sort' => $this->sort,
                'query' => $query,
            ]);
        }
    }

    /**
     * Clear all query data.
     */
    final public function clear()
    {
        $this->limit = null;
        $this->skip = null;
        $this->projection = [];
        $this->query = [];
        $this->sort = [];
    }

    /**
     * Select fields from collection.
     *
     * @param string/array  $field      Field name
     * @param boolean/array $projection True to include False to exclude
     *
     * @return object This Objet
     */
    final public function select($field, $projection = true)
    {
        if ($field instanceof Parameter) {
            if ($field->method === 'undefined') {
                return $this;
            }
            $field = $field->value;
        }

        if (!is_array($field)) {
            $field = [$field];
        }

        if (!is_array($projection)) {
            $projection = [];
            foreach ($field as $key => $value) {
                $projection[$key] = $projection;
            }
            $projection = $projection;
        }

        foreach ($field as $key => $value) {
            $this->projection[$value] = ($projection[$key]) ? 1 : -1;
        }

        return $this;
    }

    /**
     * Limit the number of returned rows.
     *
     * @param int $limit Number of the rows to return
     *
     * @return object This Object
     */
    final public function limit($limit)
    {
        if ($limit instanceof Parameter) {
            if ($limit->method === 'undefined') {
                return $this;
            }
            $limit = $limit->value;
        }

        if ($limit >= 0) {
            $this->limit = (int) $limit;
        }

        return $this;
    }

    /**
     * Skip from return rows.
     *
     * @param int $skip Rows to skip
     *
     * @return object This object
     */
    final public function skip($skip)
    {
        if ($skip instanceof Parameter) {
            if ($skip->method === 'undefined') {
                return $this;
            }
            $skip = $skip->value;
        }

        if ($skip >= 0) {
            $this->skip = (int) $skip;
        }

        return $this;
    }

    /**
     * Order rows by fields.
     *
     * @param string/array  $field Field name
     * @param boolean/array $asc   Associative array
     *
     * @return object This object
     */
    final public function sort($field)
    {
        if ($field instanceof Parameter) {
            if ($field->method === 'undefined') {
                return $this;
            }
            $field = $field->value;
        }

        if (!is_array($field)) {
            $field = [$field];
        }

        foreach ($field as $key => $value) {
            $sort = (substr($value, 0, 1) === '-') ? -1 : 1;
            $key = str_replace('-', '', $value);

            $this->sort[$key] = $sort;
        }

        return $this;
    }

    /**
     * Sort alias.
     *
     * @param string/array  $field Field name
     * @param boolean/array $asc   Associative array
     *
     * @return object This object
     */
    final public function order($field)
    {
        return $this->sort($field);
    }

    /**
     * Where value matches row.
     *
     * @param string $field Field name
     * @param any    $value Field value
     *
     * @return object This object
     */
    final public function where($field, $value = null)
    {
        if ($value instanceof Parameter) {
            if ($value->method === 'unset') {
                return $this;
            }
            $value = $value->value;
        }

        $this->query[$field] = $value;

        return $this;
    }

    /**
     * Where not equals.
     *
     * @param string $field Field name
     * @param any    $value Field value
     *
     * @return object This object
     */
    final public function whereNe($field, $value = null)
    {
        if ($value instanceof Parameter) {
            if ($value->method === 'undefined') {
                return $this;
            }
            $value = $value->value;
        }

        $this->query[$field]['$ne'] = $value;

        return $this;
    }

    /**
     * Where value greater then.
     *
     * @param string $field Field name
     * @param any    $value Field value
     * @param string $type  Field type
     *
     * @return object This object
     */
    final public function whereGt($field, $value = null, $type = null)
    {
        if ($value instanceof Parameter) {
            if ($value->method === 'undefined') {
                return $this;
            }
            $value = $value->value;
        }

        switch ($type) {
            case 'date':
                if (is_object($value) && get_class($value) === 'MongoDate') {
                    $this->query[$field]['$gt'] = $value;
                } else {
                    $this->query[$field]['$gt'] = new MongoDate($value);
                }
                break;
            default:
                 $this->query[$field]['$gt'] = $value;
                break;
        }

        return $this;
    }

    /**
     * Where value greater then or equals.
     *
     * @param string $field Field name
     * @param any    $value Field value
     * @param string $type  Field type
     *
     * @return object This object
     */
    final public function whereGte($field, $value = null, $type = null)
    {
        if ($value instanceof Parameter) {
            if ($value->method === 'undefined') {
                return $this;
            }
            $value = $value->value;
        }

        switch ($type) {
            case 'date':
                if (is_object($value) && get_class($value) === 'MongoDate') {
                    $this->query[$field]['$gte'] = $value;
                } else {
                    $this->query[$field]['$gte'] = new MongoDate($value);
                }
                break;
            default:
                 $this->query[$field]['$gte'] = $value;
                break;
        }

        return $this;
    }

    /**
     * Where lesser than.
     *
     * @param string $field Field name
     * @param any    $value Field value
     * @param string $type  Field type
     *
     * @return object This object
     */
    final public function whereLt($field, $value = null, $type = null)
    {
        if ($value instanceof Parameter) {
            if ($value->method === 'undefined') {
                return $this;
            }
            $value = $value->value;
        }

        switch ($type) {
            case 'date':
                if (is_object($value) && get_class($value) === 'MongoDate') {
                    $this->query[$field]['$lt'] = $value;
                } else {
                    $this->query[$field]['$lt'] = new MongoDate($value);
                }
                break;
            default:
                 $this->query[$field]['$lt'] = $value;
                break;
        }

        return $this;
    }

    /**
     * Where lesser than equals.
     *
     * @param string $field Field name
     * @param any    $value Field value
     * @param string $type  Field type
     *
     * @return object This object
     */
    final public function whereLte($field, $value = null, $type = null)
    {
        if ($value instanceof Parameter) {
            if ($value->method === 'undefined') {
                return $this;
            }
            $value = $value->value;
        }

        switch ($type) {
            case 'date':
                if (is_object($value) && get_class($value) === 'MongoDate') {
                    $this->query[$field]['$lte'] = $value;
                } else {
                    $this->query[$field]['$lte'] = new MongoDate($value);
                }
                break;

            default:
                 $this->query[$field]['$lte'] = $value;
                break;
        }

        return $this;
    }

    /**
     * Where field like.
     *
     * @param string $field               Field name
     * @param string $value               Field value
     * @param string $flags               Flags (??)
     * @param bool   $enableStartWildcard (??)
     * @param bool   $enableEndWildcard   (??)
     *
     * @return object This Object
     */
    final public function whereLike(
        $field,
        $value = null,
        $flags = 'i',
        $enableStartWildcard = true,
        $enableEndWildcard = true
    ) {
        if ($value instanceof Parameter) {
            if ($value->method === 'undefined') {
                return $this;
            }
            $value = $value->value;
        }

        $value = trim($value);
        $value = quotemeta($value);

        if ($enableStartWildcard !== true) {
            $value = '^'.$value;
        }

        if ($enableEndWildcard !== true) {
            $value .= '$';
        }

        $this->query[$field] = new MongoRegex('/'.$value.'/'.$flags);

        return $this;
    }

    /**
     * Where field or like.
     *
     * @param string $field               Field name
     * @param string $value               Field value
     * @param string $flags               Flags (??)
     * @param bool   $enableStartWildcard (??)
     * @param bool   $enableEndWildcard   (??)
     *
     * @return object This Object
     */
    final public function whereOrLike(
        $field,
        $value = null,
        $flags = 'i',
        $enableStartWildcard = true,
        $enableEndWildcard = true
    ) {
        if ($value instanceof Parameter) {
            if ($value->method === 'undefined') {
                return $this;
            }
            $value = $value->value;
        }

        $value = trim($value);
        $value = quotemeta($value);

        if ($enableStartWildcard !== true) {
            $value = '^'.$value;
        }

        if ($enableEndWildcard !== true) {
            $value .= '$';
        }

        $this->query['$or'][] = [
            $field => new MongoRegex('/'.$value.'/'.$flags),
            ];

        return $this;
    }

    /**
     * Where value in.
     *
     * @param string $field  Field name
     * @param array  $values Field values
     *
     * @return object This Object
     */
    final public function whereIn($field, $values)
    {
        if ($values instanceof Parameter) {
            if ($values->method === 'undefined') {
                return $this;
            }
            $values = $values->value;
        }

        $this->query[$field]['$in'] = $values;

        return $this;
    }

    /**
     * Where all values have matches in the db.
     *
     * @param string $field  Field name
     * @param array  $values Field values
     *
     * @return object This Object
     */
    final public function whereAll($field, $values)
    {
        if ($values instanceof Parameter) {
            if ($values->method === 'undefined') {
                return $this;
            }
            $values = $values->value;
        }

        if (count($values) > 0) {
            $this->query[$field]['$all'] = $values;
        }

        return $this;
    }

    /**
     * Where not in values.
     *
     * @param string $field  Field name
     * @param array  $values Field values
     *
     * @return object This object
     */
    final public function whereNin($field, $values)
    {
        if ($values instanceof Parameter) {
            if ($values->method === 'undefined') {
                return $this;
            }
            $values = $values->value;
        }

        $this->query[$field]['$nin'] = $values;

        return $this;
    }

    /**
     * Where or.
     *
     * @param string $field Field name
     * @param array  $value Values
     *
     * @return object This object
     */
    final public function whereOr($field, $values)
    {
        if ($values instanceof Parameter) {
            if ($values->method === 'undefined') {
                return $this;
            }
            $values = $values->value;
        }

        if (!isset($this->query['$or'])) {
            $this->query['$or'] = [];
        }

        $this->query['$or'][] = [$field => $values];

        return $this;
    }

    /**
     * Where near.
     *
     * @param string $field  Field name
     * @param array  $coords Coordenates
     *
     * @return object This object
     */
    final public function whereNear($field, array $coords = [], $spherical = true)
    {
        if ($spherical) {
            $this->query[$field]['$nearSphere'] = $coords;
        } else {
            $this->query[$field]['$near'] = $coords;
        }

        if ($distance !== null) {
            $this->query[$field]['$maxDistance'] = $distance;
        }

        return $this;
    }

    /**
     * Where field regex.
     *
     * @param string $field               Field name
     * @param string $value               Field value
     * @param string $flags               Flags (??)
     * @param bool   $enableStartWildcard (??)
     * @param bool   $enableEndWildcard   (??)
     *
     * @return object This Object
     */
    final public function whereRegex(
        $field,
        $value = null,
        $flags = 'i',
        $enableStartWildcard = true,
        $enableEndWildcard = true
    ) {
        $value = trim($value);

        if ($enableStartWildcard !== true) {
            $value = '^'.$value;
        }

        if ($enableEndWildcard !== true) {
            $value .= '$';
        }

        $this->query[$field] = new MongoRegex('/'.$value.'/'.$flags);

        return $this;
    }

    /**
     * Remove method.
     *
     * @param array|null $query Search criteria
     */
    final public function remove(array $query = null)
    {
        $query = (!is_null($query)) ? $query : $this->query;

        try {
            $this->MongoCollection->deleteMany($query);
        } catch (Exception $Exception) {
            throw new ORMException($Exception->getMessage(), $Exception->getCode(), [
                'method' => __METHOD__,
                'collection' => $this->collection,
                'where' => $query,
            ]);
        }

        $this->clear();

        return;
    }
}

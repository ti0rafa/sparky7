<?php

namespace Sparky7\Orm\Qb;

use IteratorIterator;
use MongoDB\BSON\ObjectId;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Database;
use Sparky7\Event\Emitter;

/**
 * Query builder class.
 */
class MongoDB
{
    use Emitter;

    protected $DataBase;
    protected $collection;

    protected $query;
    protected $options;

    /**
     * Construct method.
     *
     * @param Database $DataBase   DataBase object
     * @param string   $collection Collection name
     */
    public function __construct(Database $DataBase, $collection)
    {
        /*
         * Parameters
         */

        $this->DataBase = $DataBase;
        $this->collection = $collection;

        /*
         * Clear values
         */

        $this->clearQueryOptions();
    }

    /**
     * Clear Query Options.
     */
    public function clearQueryOptions()
    {
        $this->query = [];
        $this->options = [];
    }

    /**
     * Sanitize multiple values.
     *
     * @return array Sanitize variable
     */
    public function sanitize($raw_data)
    {
        if (is_array($raw_data)) {
            $count_ignore = 0;
            $sanitize_data = [];
            foreach ($raw_data as $key => $value) {
                $sanitize = self::sanitizeValue($value);

                if ($sanitize->ignore) {
                    ++$count_ignore;
                }

                $sanitize_data[$key] = $sanitize->value;
            }

            return (object) [
                'ignore' => ($count_ignore === count($sanitize_data)) ? true : false,
                'value' => $sanitize_data,
            ];
        }

        return self::sanitizeValue($raw_data);
    }

    /**
     * Sanitize value.
     *
     * @param any $raw_value Raw variable
     *
     * @return array Sanitize variable
     */
    public function sanitizeValue($raw_value)
    {
        /*
         * Sanitize if value is controller parameter
         */

        $controller_parameters = [
            'Sparky7\Api\Parameter',
        ];

        if (is_object($raw_value) && in_array(get_class($raw_value), $controller_parameters)) {
            if ('unset' === $raw_value->method) {
                return (object) [
                    'ignore' => true,
                    'value' => $raw_value->value,
                ];
            }
            $raw_value = $raw_value->value;
        }

        /*
         * Sanitize previous mongo driver
         */

        if (is_a($raw_value, 'MongoId')) {
            $raw_value = new ObjectId((string) $raw_value);
        }

        if (is_a($raw_value, 'MongoDate')) {
            $raw_value = new UTCDateTime((int) $raw_value->sec * 1000);
        }

        return (object) [
            'ignore' => false,
            'value' => $raw_value,
        ];
    }

    /**
     * Aggregate.
     *
     * @param array $pipeline Pipeline
     * @param array $options  Options
     *
     * @return cursor Cursor
     */
    public function aggregate(array $pipeline = [], array $options = [])
    {
        try {
            return $this->DataBase->{$this->collection}->aggregate($pipeline, $options);
        } catch (Exception $Exception) {
            throw new OrmException($Exception->getMessage(), $Exception->getCode(), ['method' => __METHOD__, 'collection' => $this->collection, 'pipeline' => $pipeline, 'options' => $options]);
        }
    }

    /**
     * Count.
     *
     * @param array $query   Query
     * @param array $options Options
     *
     * @return int Count
     */
    public function count(array $query = [], array $options = [])
    {
        /*
         * Parameters
         */

        $query = array_merge($this->query, $query);
        $options = array_merge($this->options, $options);

        /*
         * Query
         */

        try {
            $count = $this->DataBase->{$this->collection}->count($query, $options);
        } catch (Exception $Exception) {
            throw new OrmException($Exception->getMessage(), $Exception->getCode(), ['method' => __METHOD__, 'collection' => $this->collection, 'query' => $query, 'options' => $options]);
        }

        /*
         * Clear data
         */

        $this->clearQueryOptions();

        return $count;
    }

    /**
     * Exists.
     *
     * @param array $query   Query
     * @param array $options Options
     *
     * @return bool Exists
     */
    public function exists(array $query = [], array $options = [])
    {
        return ($this->count($query, $options) > 0) ? true : false;
    }

    /**
     * Find.
     *
     * @param array $query   Query
     * @param array $options Options
     *
     * @return Iterator Iterator
     */
    public function find(array $query = [], array $options = [])
    {
        /*
         * Parameters
         */

        $query = array_merge($this->query, $query);
        $options = array_merge($this->options, $options);

        /*
         * Events
         */

        $this->emit('before.search', [
            $query,
            $options,
        ]);

        /*
         * Query
         */

        try {
            $cursor = $this->DataBase->{$this->collection}->find($query, $options);
        } catch (Exception $Exception) {
            throw new OrmException($Exception->getMessage(), $Exception->getCode(), ['method' => __METHOD__, 'collection' => $this->collection, 'query' => $query, 'options' => $options]);
        }

        /*
         * Transform cursor into iterator
         */

        $Iterator = new IteratorIterator($cursor);
        $Iterator->rewind();

        /*
         * Clear data
         */

        $this->clearQueryOptions();

        return $Iterator;
    }

    /**
     * Find one.
     *
     * @param array $query   Query
     * @param array $options Options
     *
     * @return array Document
     */
    public function findOne(array $query = [], array $options = [])
    {
        /*
         * Parameters
         */

        $query = array_merge($this->query, $query);
        $options = array_merge($this->options, $options);

        /*
         * Events
         */

        $this->emit('before.search', [
            $query,
            $options,
        ]);

        /*
         * Query
         */

        try {
            $result = $this->DataBase->{$this->collection}->findOne($query, $options);
        } catch (Exception $Exception) {
            throw new OrmException($Exception->getMessage(), $Exception->getCode(), ['method' => __METHOD__, 'collection' => $this->collection, 'query' => $query, 'options' => $options]);
        }

        /*
         * Clear data
         */

        $this->clearQueryOptions();

        return $result;
    }

    /**
     * Select fields from collection.
     *
     * @param any $raw_value Field value
     *
     * @return object This Objet
     */
    public function select($raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $select = $sanitize->value;

        /*
         * Add value to query
         */

        if (!is_array($select)) {
            $select = [$select];
        }

        foreach ($select as $key => $value) {
            $option = ('-' === substr($value, 0, 1)) ? -1 : 1;
            $key = trim(str_replace('-', '', $value));

            if (mb_strlen($key) > 0) {
                $this->options['projection'][$key] = $option;
            }
        }

        return $this;
    }

    /**
     * Limit the number of returned rows.
     *
     * @param int $raw_value Field value
     *
     * @return object This Object
     */
    public function limit($raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $limit = $sanitize->value;

        /*
         * Add value to query
         */

        if ($limit >= 0) {
            $this->options['limit'] = (int) $limit;
        }

        return $this;
    }

    /**
     * Skip from return rows.
     *
     * @param int $raw_value Field value
     *
     * @return object This object
     */
    public function skip($raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $skip = $sanitize->value;

        /*
         * Add value to query
         */

        if ($skip >= 0) {
            $this->options['skip'] = (int) $skip;
        }

        return $this;
    }

    /**
     * Order rows by fields.
     *
     * @param any $raw_value Field value
     *
     * @return object This object
     */
    public function sort($raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $sort = $sanitize->value;

        /*
         * Add value to query
         */

        if (!is_array($sort)) {
            $sort = [$sort];
        }

        foreach ($sort as $key => $value) {
            if (null === $value) {
                continue;
            }
            $option = ('-' === substr($value, 0, 1)) ? -1 : 1;
            $key = trim(str_replace('-', '', $value));

            if (mb_strlen($key) > 0) {
                $this->options['sort'][$key] = $option;
            }
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
    public function order($field)
    {
        return $this->sort($field);
    }

    /**
     * Where value matches row.
     *
     * @param string $field     Field name
     * @param any    $raw_value Field value
     *
     * @return object This object
     */
    public function where($field, $raw_value = null)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field] = $value;

        return $this;
    }

    /**
     * Where not equals.
     *
     * @param string $field     Field name
     * @param any    $raw_value Field value
     *
     * @return object This object
     */
    public function whereNe($field, $raw_value = null)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$ne'] = $value;

        return $this;
    }

    /**
     * Where value greater then.
     *
     * @param string $field     Field name
     * @param any    $raw_value Field value
     *
     * @return object This object
     */
    public function whereGt($field, $raw_value = null)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$gt'] = $value;

        return $this;
    }

    /**
     * Where value greater then or equals.
     *
     * @param string $field     Field name
     * @param any    $raw_value Field value
     *
     * @return object This object
     */
    public function whereGte($field, $raw_value = null)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$gte'] = $value;

        return $this;
    }

    /**
     * Where lesser than.
     *
     * @param string $field     Field name
     * @param any    $raw_value Field value
     *
     * @return object This object
     */
    public function whereLt($field, $raw_value = null)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$lt'] = $value;

        return $this;
    }

    /**
     * Where lesser than equals.
     *
     * @param string $field     Field name
     * @param any    $raw_value Field value
     *
     * @return object This object
     */
    public function whereLte($field, $raw_value = null)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$lte'] = $value;

        return $this;
    }

    /**
     * Where value in.
     *
     * @param string $field     Field name
     * @param array  $raw_value Field value
     *
     * @return object This Object
     */
    public function whereIn($field, array $raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$in'] = $value;

        return $this;
    }

    /**
     * Where all values have matches in the db.
     *
     * @param string $field     Field name
     * @param array  $raw_value Field value
     *
     * @return object This Object
     */
    public function whereAll($field, array $raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$all'] = $value;

        return $this;
    }

    /**
     * Where not in values.
     *
     * @param string $field     Field name
     * @param array  $raw_value Field value
     *
     * @return object This object
     */
    public function whereNin($field, array $raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        $this->query[$field]['$nin'] = $value;

        return $this;
    }

    /**
     * Where or.
     *
     * @param string $field     Field name
     * @param any    $raw_value Field value
     *
     * @return object This object
     */
    public function whereOr($field, $raw_value)
    {
        /*
         * Sanitize value
         */

        $sanitize = self::sanitize($raw_value);

        if ($sanitize->ignore) {
            return $this;
        }

        $value = $sanitize->value;

        /*
         * Add value to query
         */

        if (!isset($this->query['$or'])) {
            $this->query['$or'] = [];
        }

        $this->query['$or'][] = [$field => $value];

        return $this;
    }
}

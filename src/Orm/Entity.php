<?php

namespace Sparky7\Orm;

use Exception;
use Sparky7\Api\Parameter;
use Sparky7\Error\Exception\ExBadRequest;
use Sparky7\Event\Emitter;

/**
 * Entity Class.
 */
abstract class Entity
{
    use Emitter;

    /*
     * Other properties.
     */

    private $ET;

    /*
     * Entity variables.
     */

    private $is_valid;
    private $data;
    private $method;

    /**
     * Constructor.
     */
    final public function __construct($MongoCollection, array $data = [])
    {
        /*
         * Configure mongo driver (map methods from legacy and current driver)
         */

        // Select Entity
        if ('MongoDB\Collection' === get_class($MongoCollection)) {
            $this->ET = new Et\MongoDB($MongoCollection);
        } elseif ('MongoCollection' === get_class($MongoCollection)) {
            $this->ET = new Et\Mongo($MongoCollection);
        } else {
            throw new Exception('Invalid mongo database');
        }

        /*
         * Register events
         */

        if (method_exists($this, 'events')) {
            foreach ($this->events() as $key => $value) {
                $this->on($key, $value);
            }
        }

        /*
         * Set defaults
         */

        $this->reset($data);
    }

    /**
     * Get property value magic method.
     *
     * @param string $name Property name
     *
     * @return any Property value
     */
    final public function __get($name)
    {
        if (!isset($this->data[$name])) {
            throw new Exception('Invalid property '.$name);
        }

        return $this->data[$name]->value;
    }

    /**
     * Is property set method.
     *
     * @param string $name Property name
     *
     * @return bool
     */
    final public function __isset($name)
    {
        return isset($this->data[$name]);
    }

    /**
     * Set property magic method.
     *
     * @param string $name  Property name
     * @param value  $value Property value
     */
    final public function __set($name, $value)
    {
        if (!isset($this->data[$name])) {
            return;
        }

        if ($value instanceof Parameter) {
            if ('unset' === $value->method) {
                return;
            }

            $value = $value->value;
        }

        /*
         * Before Events
         *  - Change default set method while running events
         *  - Change default set method back after running events
         */

        $this->method[$name] = 'event';

        $this->emit('before.set.'.$name, [
            $value,
            $this->data[$name]->value,
            $this->data[$name]->previous,
        ]);

        $this->method[$name] = 'set';

        /*
         * Dont set value if previous method was set on an event
         */

        if ('event' !== $this->data[$name]->method) {
            $this->set($name, $value, 'set');
        }
    }

    /**
     * Set property method.
     *
     * @param string $name  Property name
     * @param value  $value Property value
     */
    final public function set($name, $value)
    {
        if (!isset($this->data[$name])) {
            return;
        }

        $this->data[$name]->set($value, $this->method[$name]);
        $this->data[$name]->sanitize();
    }

    /**
     * Unset magic method.
     *
     * @param string $name Property name
     */
    final public function __unset($name)
    {
        if (!isset($this->data[$name])) {
            return;
        }

        $this->data[$name] = null;
    }

    /**
     * Define entity properties.
     */
    abstract public function define();

    /**
     * Define events.
     */
    public function events()
    {
        return [];
    }

    /**
     * Reset property values.
     *
     * @param array|null $data Property values
     */
    private function reset(array $data = [])
    {
        $this->is_valid = (0 === count($data)) ? false : true;

        /*
         * Create data
         */

        $this->data = [];
        $this->method = [];

        foreach ($this->define() as $name => $Property) {
            $value = (isset($data[$name])) ? $data[$name] : $Property->default;

            if (is_a($value, 'MongoDB\Model\BSONDocument')) {
                $value = json_decode(json_encode($value));
            }

            $Property->set($value, 'loaded');
            $Property->sanitize();

            $this->data[$name] = $Property;
            $this->method[$name] = 'set';
        }
    }

    /**
     * Is entity valid.
     *
     * @return bool
     */
    final public function isValid()
    {
        return $this->is_valid;
    }

    /**
     * Get ID value.
     *
     * @return ID
     */
    final public function getId()
    {
        if (!$this->isValid()) {
            return;
        }

        return (string) $this->data['_id']->value;
    }

    /**
     * Save entity.
     */
    final public function save()
    {
        return ($this->isValid()) ? $this->update() : $this->insert();
    }

    /**
     * Insert entity.
     *
     * @return bool
     */
    final public function insert()
    {
        if ($this->isValid()) {
            return false;
        }

        $this->emit('before.save');
        $this->emit('before.insert');

        /*
         * Validate properties
         */

        $row = [];
        foreach ($this->data as $name => $Property) {
            if ('_id' === $name && in_array($Property->type, ['ID', 'MongoId'])) {
                if (is_null($Property->value)) {
                    $Property->set(Mongo::id(), 'set');
                }
            }

            try {
                $Property->validate();
            } catch (Exception $Exception) {
                throw new ExBadRequest($Exception->getMessage().': '.$name.' in '.get_class($this));
            }

            $row[$name] = $Property->value;
        }

        /*
         * Execute query
         */

        if (!$this->ET->insert($row)) {
            return false;
        }

        $this->is_valid = true;

        // Set loaded as method for each property
        foreach ($this->data as $key => $value) {
            $this->data[$key]->method('loaded');
        }

        $this->emit('after.save');
        $this->emit('after.insert');

        return true;
    }

    /**
     * Update entity.
     *
     * @return bool
     */
    final public function update()
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->emit('before.save');
        $this->emit('before.update');

        /*
         * Validate properties
         */

        $row = [];
        foreach ($this->data as $name => $Property) {
            try {
                $Property->validate();
            } catch (Exception $Exception) {
                throw new ExBadRequest($Exception->getMessage().': '.$name.' in '.get_class($this));
            }

            $row[$name] = $Property->value;
        }

        /*
         * Execute query
         */

        $query = [
            '_id' => Mongo::id($this->getId()),
        ];

        if (!$this->ET->update($query, $row)) {
            return false;
        }

        // Set loaded as method for each property
        foreach ($this->data as $key => $value) {
            $this->data[$key]->method('loaded');
        }

        $this->emit('after.save');
        $this->emit('after.update');

        return true;
    }

    /**
     * Delete entity.
     *
     * @return bool
     */
    final public function delete()
    {
        if (!$this->isValid()) {
            return false;
        }

        $this->emit('before.delete');

        /*
         * Execute query
         */

        $query = [
            '_id' => Mongo::id($this->getId()),
        ];

        if (!$this->ET->delete($query)) {
            return false;
        }

        $this->emit('after.delete');

        // Reset data
        $this->reset();

        return true;
    }

    /**
     * Returns document data as array.
     *
     * @return array Entity data
     */
    final public function toArray()
    {
        $row = [];
        foreach ($this->data as $name => $Property) {
            if (!$Property->ignore) {
                $field = (!is_null($Property->alias)) ? $Property->alias : $name;

                $row[$field] = $Property->export();

                /**
                 * Launch event.
                 */
                $event_value = $this->emit('before.array.'.$name, [$row[$field]]);

                if (!is_null($event_value)) {
                    $row[$field] = $event_value;
                }
            }
        }

        return $row;
    }
}

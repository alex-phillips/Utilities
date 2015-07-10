<?php

namespace Utility\Traits;

use ArrayIterator;
use RecursiveArrayIterator;
use RecursiveIteratorIterator;
use stdClass;

/**
 * Utility class for storing values. Nested values can be set using dot-notation.
 *
 * @package Fortitude\Utility
 */
trait Bag
{
    /**
     * Data structure all of the values are stored.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Construct a new instance of a `ParameterBag`.
     *
     * @param array|\stdClass $data Data to pre-populate the bag with
     */
    public function __construct($data = [])
    {
        if ($data instanceof stdClass) {
            $data = (array)$data;
        }
        $this->add($data);
    }

    /**
     * Add an array of (dot-notated) data to the existing data. Any existing
     * paths will be overwritten.
     *
     * @param array $data The new data to add
     */
    public function add(array $data)
    {
        foreach ($data as $key => $value) {
            $this->set($key, $value);
        }
    }

    public function append($data)
    {
        if (is_array($data)) {
            return $this->merge($data);
        }

        $this->data[] = $data;
    }

    public function merge(array $data)
    {
        $this->data = array_merge($this->data, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function count()
    {
        return count($this->data);
    }

    /**
     * Getter method to retriev a value associated with a given key.
     *
     * @param string $path    The key to fetch
     * @param null   $default The default value to return if the key is not present
     *
     * @return array|null
     */
    public function get($path, $default = null)
    {
        $keys = explode('.', $path);
        $ary = &$this->data;

        while ($key = array_shift($keys)) {
            if (!isset($ary[$key])) {
                return $default;
            }
            $ary = &$ary[$key];
        }

        return $ary;
    }

    /**
     * Retrieve the data from the `Bag` in a one-dimensional, dot-notated
     * array.
     *
     * @return array
     */
    public function flatten()
    {
        return $this->flattenData($this->raw());
    }

    /**
     * Recursive method that flattens the containing data into a single-level,
     * hashed array.
     *
     * @param array  $data   The data to flatten
     * @param string $prefix The key prefix from the previous recursive call
     *
     * @return array
     */
    protected function flattenData(array $data, $prefix = '')
    {
        $retval = [];
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $retval += $this->flattenData($value, "{$prefix}{$key}.");
            } else {
                $retval["{$prefix}{$key}"] = $value;
            }
        }

        return $retval;
    }

    /**
     * {@inheritdoc}
     */
    public function getIterator()
    {
        return new ArrayIterator($this->data);
    }

    /**
     * Boolean to check if the given key exists.
     *
     * @param string $key The key to check
     *
     * @return bool
     */
    public function has($key)
    {
        if ($this->get($key) !== null) {
            return true;
        }

        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return $this->raw();
    }

    /**
     * {@inheritdoc}
     */
    public function offsetExists($key)
    {
        return $this->has($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetGet($key)
    {
        return $this->get($key);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetSet($key, $value)
    {
        $this->set($key, $value);
    }

    /**
     * {@inheritdoc}
     */
    public function offsetUnset($key)
    {
        $this->clear($key);
    }

    /**
     * Retrieve the data structure containing all values.
     *
     * @return array
     */
    public function raw()
    {
        return $this->data;
    }

    /**
     * Remove a value from the bag associated with the given key.
     *
     * @param string $path The key to clear
     */
    public function remove($path)
    {
        $keys = explode('.', $path);
        $ary = &$this->data;

        foreach ($keys as $k) {
            if (!isset ($ary[$k])) {
                $ary[$k] = [];
            }

            $key = &$ary;
            $ary = &$ary[$k];
        }

        if (isset($key) && isset($k)) {
            unset($key[$k]);
        }
    }

    /**
     * Replace all values of the `ParameterBag` with the provided data structure.
     *
     * @param array $params The new values
     */
    public function replace(array $params)
    {
        $this->data = [];
        $this->add($params);
    }

    /**
     * Clear out all values in the `ParameterBag`.
     */
    public function reset()
    {
        $this->data = [];
    }

    /**
     * Assign a path to a value and store it in the `ParameterBag`.
     *
     * @param string $path  The key to create
     * @param mixed  $value The value to store
     */
    public function set($path, $value)
    {
        $keys = explode('.', $path);
        $arr =& $this->data;

        while ($key = array_shift($keys)) {
            $arr = &$arr[$key];
        }

        $arr = $value;
    }
}

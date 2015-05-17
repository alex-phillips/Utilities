<?php

namespace Exonintrendo\Utility\Traits;

use Closure;

/**
 * The `Cacheable` trait provides functionality to cache any data from the
 * class layer. All data is unique and represented by a generated cache key.
 *
 * @package Exonintrendo\Utility
 */
trait Cacheable
{
    /**
     * Cached items indexed by key.
     *
     * @var array
     */
    protected $cache = [];

    /**
     * Is cache on or off?
     *
     * @var bool
     */
    private $cacheEnabled = true;

    /**
     * Dynamically read and write from the cache at once. If the cache exists
     * with the key return it, else execute and save the result. If the value
     * happens to be a closure, evaluate the closure and save the result.
     *
     * @param array|string  $key
     * @param mixed|Closure $value
     *
     * @return array|mixed|null
     */
    public function cache($key, $value = null)
    {
        $key = $this->createCacheKey($key);

        if ($cache = $this->getCache($key)) {
            return $cache;
        }

        if ($value instanceof Closure) {
            $callback = Closure::bind($value, $this, $this);
            $value = $callback();
        }

        if (!$this->cacheEnabled) {
            return $value;
        }

        return $this->setCache($key, $value);
    }

    /**
     * Generate a cache key. If an array is passed, drill down and form a key.
     *
     * @param $keys
     *
     * @return string
     */
    public function createCacheKey($keys)
    {
        if (is_array($keys)) {
            $key = array_shift($keys);

            if ($keys) {
                foreach ($keys as $value) {
                    if (is_array($value)) {
                        $key .= '-' . md5(json_encode($value));
                    } else {
                        if ($value) {
                            $key .= '-' . $value;
                        }
                    }
                }
            }
        } else {
            $key = $keys;
        }

        return (string)$key;
    }

    /**
     * Empty the cache.
     *
     * @return $this
     */
    public function flushCache()
    {
        $this->cache = [];

        return $this;
    }

    /**
     * Return a cached item if it exists, else return null.
     *
     * @param null $key
     *
     * @return array|null
     */
    public function getCache($key = null)
    {
        if (!$this->cacheEnabled) {
            return null;
        }

        if ($key === null) {
            return $this->cache;
        }

        $key = $this->createCacheKey($key);

        if ($this->hasCache($key)) {
            return $this->cache[$key];
        }

        return null;
    }

    /**
     * Check to see if the cache key exists.
     *
     * @param $key
     *
     * @return bool
     */
    public function hasCache($key)
    {
        return isset($this->cache[$this->createCacheKey($key)]);
    }

    /**
     * Remove an item from the cache. Return true if the item was removed.
     *
     * @param $key
     *
     * @return bool
     */
    public function removeCache($key)
    {
        $key = $this->createCacheKey($key);

        if ($this->hasCache($key)) {
            unset($this->cache[$key]);

            return true;
        }

        return false;
    }

    /**
     * Set a value to the cache with the defined key. This will overwrite any
     * data with the same key. The value being saved will be returned.
     *
     * @param $key
     * @param $value
     *
     * @return mixed
     */
    public function setCache($key, $value)
    {
        $this->cache[$this->createCacheKey($key)] = $value;

        return $value;
    }

    /**
     * Toggle cache on and off.
     *
     * @param bool $on
     *
     * @return $this
     */
    public function toggleCache($on = true)
    {
        $this->cacheEnabled = (bool)$on;

        return $this;
    }
}

<?php

namespace Exonintrendo\Utility;

use ArrayAccess;
use IteratorAggregate;
use JsonSerializable;
use Countable;
use Exonintrendo\Utility\Traits\Bag;

/**
 * Utility class for storing values. Nested values can be set using dot-notation.
 *
 * @package Fortitude\Utility
 */
class ParameterBag implements ArrayAccess, IteratorAggregate, JsonSerializable, Countable
{
    use Bag;

    /**
     * {@inheritdoc}
     */
    public function raw()
    {
        $retval = [];
        foreach ($this->data as $k => $v) {
            if ($v instanceof ParameterBag) {
                $retval[$k] = $v->raw();
            } else {
                $retval[$k] = $v;
            }
        }

        return $retval;
    }

    /**
     * Overridden method that instead of setting nested values as new arrays,
     * it creates a new instance of `ParameterBag` to storage and retrieval.
     *
     * @param string $path  The key to set
     * @param mixed  $value The value
     */
    public function set($path, $value)
    {
        $keys = explode('.', $path);
        $key = array_shift($keys);

        if (empty($keys)) {
            if (is_array($value)) {
                $this->data[$key] = new ParameterBag($value);
            } else {
                $this->data[$key] = $value;
            }
        } else {
            if (!$this->has($key)) {
                $this->data[$key] = new ParameterBag();
            }

            $this->data[$key]->set(implode('.', $keys), $value);
        }
    }
}

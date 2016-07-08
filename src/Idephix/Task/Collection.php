<?php
namespace Idephix\Task;

abstract class Collection extends \IteratorIterator implements \ArrayAccess
{
    public static function ofArray($array)
    {
        return new static(new \ArrayIterator($array));
    }

    public static function dry()
    {
        return static::ofArray(array());
    }

    public function count()
    {
        return count($this->getInnerIterator());
    }

    public function offsetExists($offset)
    {
        return $this->getInnerIterator()->offsetExists($offset);
    }

    public function offsetGet($offset)
    {
        return $this->getInnerIterator()->offsetGet($offset);
    }

    public function offsetUnset($offset)
    {
        $this->getInnerIterator()->offsetUnset($offset);
    }
}
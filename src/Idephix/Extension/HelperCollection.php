<?php
namespace Idephix\Extension;

use Idephix\Exception\MissingMethodException;
use Idephix\Task\CollectionIterator;

class HelperCollection extends CollectionIterator
{
    public static function ofCallables($array)
    {
        return new static(
            new \ArrayIterator(
                array_filter(
                    $array,
                    function ($method) {
                        return $method instanceof Helper;
                    }
                )
            )
        );
    }

    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Helper) {
            throw new \DomainException('HelperCollection can only accept \Idephix\Extension\Helper instances');
        }

        $this->getInnerIterator()->offsetSet($offset, $value);
    }

    public function merge(HelperCollection $collection)
    {
        return new static(
            new \ArrayIterator(
                array_merge(
                    iterator_to_array($this->getInnerIterator()),
                    iterator_to_array($collection->getInnerIterator())
                )
            )
        );
    }

    public function execute($methodName, $args = array())
    {
        foreach ($this->getInnerIterator() as $method) {
            if ($method->name() == $methodName) {
                return call_user_func_array($method, $args);
            }
        }

        throw new MissingMethodException("Unable to find method named $methodName");
    }
}

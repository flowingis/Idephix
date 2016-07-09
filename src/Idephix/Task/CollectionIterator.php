<?php
namespace Idephix\Task;

abstract class CollectionIterator implements \Iterator, \OuterIterator, \ArrayAccess
{
    /** @var  \Iterator */
    private $innerIterator;

    protected function __construct(\Iterator $iterator)
    {
        $this->innerIterator = $iterator;
    }

    public function getInnerIterator()
    {
        return $this->innerIterator;
    }

    public static function dry()
    {
        return new static(new \ArrayIterator(array()));
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

    public function current()
    {
        return $this->innerIterator->current();
    }

    public function next()
    {
        $this->innerIterator->next();
    }

    public function key()
    {
        return $this->innerIterator->key();
    }

    public function valid()
    {
        return $this->innerIterator->valid();
    }

    public function rewind()
    {
        $this->innerIterator->rewind();
    }
}

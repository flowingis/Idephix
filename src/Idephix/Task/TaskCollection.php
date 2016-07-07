<?php
namespace Idephix\Task;

class TaskCollection extends \FilterIterator
{
    public static function ofArray($array)
    {
        return new static(new \ArrayIterator($array));
    }
    
    public function accept()
    {
        if (!($this->current() instanceof Task)) {
            throw new \DomainException('Invalid element');
        }
    }
}

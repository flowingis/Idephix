<?php
namespace Idephix\Task\Parameter;

use Idephix\Task\CollectionIterator;

class Collection extends CollectionIterator
{
    public static function createFromArray($parametersData)
    {
        $parameters = array();
        foreach ($parametersData as $name => $data) {
            $defaultValue = array_key_exists('defaultValue', $data) ? $data['defaultValue'] : null;
            $parameters[] = UserDefined::create($name, $data['description'], $defaultValue);
        }

        return new static(new \ArrayIterator($parameters));
    }
    
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof ParameterInterface) {
            throw new \DomainException('ParameterCollection can only accept \Idephix\Task\Parameter object');
        }

        $this->getInnerIterator()->offsetSet($offset, $value);
    }
}

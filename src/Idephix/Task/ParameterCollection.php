<?php
namespace Idephix\Task;

class ParameterCollection extends Collection
{
    public static function createFromArray($parametersData)
    {
        $parameters = array();
        foreach ($parametersData as $name => $data) {
            $defaultValue = array_key_exists('defaultValue', $data) ? $data['defaultValue'] : null;
            $parameters[] = Parameter::create($name, $data['description'], $defaultValue);
        }

        return new static(new \ArrayIterator($parameters));
    }
    
    public function offsetSet($offset, $value)
    {
        if (!$value instanceof Parameter) {
            throw new \DomainException('TaskCollection can only accept \Idephix\Task\Parameter object');
        }

        $this->getInnerIterator()->offsetSet($offset, $value);
    }
}

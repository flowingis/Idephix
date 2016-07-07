<?php
namespace Idephix\Task;

class ParameterCollection extends \ArrayIterator
{
    public static function create($parametersData)
    {
        $parameters = array();
        foreach ($parametersData as $name => $data) {
            $defaultValue = array_key_exists('defaultValue', $data) ? $data['defaultValue'] : null;
            $parameters[] = Parameter::create($name, $data['description'], $defaultValue);
        }

        return new static($parameters);
    }
}

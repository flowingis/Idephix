<?php
namespace Idephix\Task\Parameter;

class UserDefined implements ParameterInterface
{
    private $name;
    private $description;
    private $defaultValue;

    private function __construct()
    {
    }

    public static function create($name, $description, $defaultValue = null)
    {
        $param = new static();
        $param->name = $name;
        $param->description = $description;
        $param->defaultValue = $defaultValue;

        return $param;
    }

    public function isFlagOption()
    {
        return $this->defaultValue === false;
    }

    public function isOptional()
    {
        return !is_null($this->defaultValue);
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return $this->description;
    }

    public function defaultValue()
    {
        return $this->defaultValue;
    }
}

<?php
namespace Idephix\Task;

class Task
{
    private $name;
    private $description;
    private $parameters;
    private $code;

    public function __construct($name, $description, $code, ParameterCollection $parameters)
    {
        $this->name = $name;
        $this->description = $description;
        $this->parameters = $parameters;
        $this->code = $code;
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return $this->description;
    }

    /**
     * @return ParameterCollection
     */
    public function parameters()
    {
        return $this->parameters;
    }

    public function userDefinedParameters()
    {
        return new UserDefinedParameterCollection($this->parameters);
    }

    public function code()
    {
        return $this->code;
    }

    public static function dummy()
    {
        $code = function ($bar) { echo $bar; };
        $params = ParameterCollection::createFromArray(array('bar'=> array('description' => '')));

        return new static('foo', 'foo descr', $code, $params);
    }
}

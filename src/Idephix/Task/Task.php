<?php
namespace Idephix\Task;

use Idephix\Task\Parameter\Collection;
use Idephix\Task\Parameter\UserDefinedCollection;

class Task
{
    private $name;
    private $description;
    private $parameters;
    private $code;

    public function __construct($name, $description, $code, Collection $parameters)
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
     * @return Collection
     */
    public function parameters()
    {
        return $this->parameters;
    }

    public function userDefinedParameters()
    {
        return new UserDefinedCollection($this->parameters);
    }

    public function code()
    {
        return $this->code;
    }

    public static function dummy()
    {
        $code = function ($bar) { echo $bar; };
        $params = Collection::createFromArray(array('bar'=> array('description' => '')));

        return new static('foo', 'foo descr', $code, $params);
    }
}

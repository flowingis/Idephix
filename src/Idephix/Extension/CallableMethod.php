<?php
namespace Idephix\Extension;

class CallableMethod implements Method
{
    private $name;
    private $callable;

    public function __construct($name, $callable)
    {
        $this->name = $name;
        $this->callable = $callable;
    }


    public function name()
    {
        return $this->name;
    }

    public function __invoke()
    {
        return call_user_func_array($this->callable, func_get_args());
    }
}

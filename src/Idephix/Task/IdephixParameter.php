<?php
namespace Idephix\Task;

class IdephixParameter implements Parameter
{
    private $name;

    private function __construct()
    {
    }

    public static function create()
    {
        $param = new static();
        $param->name = 'idx';

        return $param;
    }

    public function isFlagOption()
    {
        return false;
    }

    public function isOptional()
    {
        return false;
    }

    public function name()
    {
        return $this->name;
    }

    public function description()
    {
        return '';
    }

    public function defaultValue()
    {
        throw new \RuntimeException('This parameter is injected at runtime, it cannot have a default value');
    }
}

<?php
namespace Idephix\Task;

interface Parameter
{
    public function isFlagOption();

    public function isOptional();

    public function name();

    public function description();

    public function defaultValue();
}

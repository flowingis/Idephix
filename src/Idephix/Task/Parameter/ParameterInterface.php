<?php
namespace Idephix\Task\Parameter;

interface ParameterInterface
{
    public function isFlagOption();

    public function isOptional();

    public function name();

    public function description();

    public function defaultValue();
}

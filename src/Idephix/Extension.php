<?php


namespace Idephix;

use Idephix\Extension\MethodCollection;
use Idephix\Task\TaskCollection;

interface Extension
{
    /** @return TaskCollection */
    public function tasks();

    /** @return MethodCollection */
    public function methods();

    public function name();
}

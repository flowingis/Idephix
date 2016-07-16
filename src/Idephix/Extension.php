<?php


namespace Idephix;

use Idephix\Task\TaskCollection;

interface Extension
{
    /** @return TaskCollection */
    public function tasks();

    public function name();
}

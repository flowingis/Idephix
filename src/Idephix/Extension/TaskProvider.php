<?php

namespace Idephix;

use Idephix\Extension\TaskCollection;

interface TaskProvider
{
    /** @return TaskCollection */
    public function tasks();

    public function name();
}

<?php

namespace Idephix\Extension;

use Idephix\Extension\TaskCollection;

interface TaskProvider extends Extension
{
    /** @return TaskCollection */
    public function tasks();

    public function name();
}

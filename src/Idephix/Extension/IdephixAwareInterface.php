<?php

namespace Idephix\Extension;

use Idephix\TaskExecutor;

interface IdephixAwareInterface
{
    /**
     * @param TaskExecutor $idx
     */
    public function setIdephix(TaskExecutor $idx);
}

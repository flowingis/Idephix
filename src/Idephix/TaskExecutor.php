<?php
namespace Idephix;

use Idephix\Task\Task;

interface TaskExecutor
{
    public function run($name, $arguments = array());

    public function has($name);
}

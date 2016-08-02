<?php
namespace Idephix\Task;

use Idephix\Task\Parameter\Collection;

interface Task
{
    public function name();

    public function description();

    /**
     * @return Collection
     */
    public function parameters();

    public function userDefinedParameters();

    /**
     * @return callable
     */
    public function code();
}

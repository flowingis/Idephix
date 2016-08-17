<?php

namespace Idephix;

use Idephix\Task\Task;

interface Builder
{
    /**
     * @return null|integer
     */
    public function run();

    /**
     * Add a Task to the application.
     *
     * @param Task $task
     * @return Builder
     */
    public function addTask(Task $task);

    /**
     * @param Extension $extension
     * @return
     */
    public function addExtension(Extension $extension);
}

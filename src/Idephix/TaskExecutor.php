<?php
namespace Idephix;

interface TaskExecutor
{
    public function run(Context $ctx);

    public function runTask($name, $arguments = array());

    public function hasTask($name);
}

<?php
namespace Idephix;

interface TaskExecutor
{
    public function runContext(Context $ctx);

    public function runTask($name, $arguments = array());

    public function hasTask($name);
}

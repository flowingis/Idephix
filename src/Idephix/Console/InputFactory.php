<?php
namespace Idephix\Console;

use Idephix\Task\Task;
use Symfony\Component\Console\Input\ArrayInput;

class InputFactory
{
    public function buildFromUserArgsForTask($arguments, Task $task)
    {
        $argumentsKeys = array('command');
        foreach ($task->userDefinedParameters() as $parameter) {
            if ($parameter->isFlagOption()) {
                $argumentsKeys[] = '--' . $parameter->name();
            } else {
                $argumentsKeys[] = $parameter->name();
            }
        }
        
        $arguments = array_combine($argumentsKeys, $arguments);
        $input = new ArrayInput($arguments);

        return $input;
    }
}

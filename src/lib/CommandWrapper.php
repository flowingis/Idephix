<?php
namespace Ideato\Deploy;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;

class CommandWrapper extends Command
{
    public function setCode(\Closure $code)
    {
        parent::setCode(function (ArgvInput $input, ConsoleOutput $output) use ($code)
        {
            $args = $input->getArguments();
            array_shift($args);
            return call_user_func_array($code, $args);
        });

        return $this;
    }

    public function __call($name, $arguments)
    {
        if (is_callable(array($this->command, $name))) {
            call_user_func_array(array($this->command, $name), $arguments);
        }
    }
}

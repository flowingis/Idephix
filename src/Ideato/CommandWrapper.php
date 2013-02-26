<?php

namespace Ideato;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CommandWrapper extends Command
{
    private $output;

    public function setCode(\Closure $code)
    {
        parent::setCode(function (InputInterface $input, OutputInterface $output) use ($code)
        {
            $args = $input->getArguments();
            array_shift($args);

            return call_user_func_array($code, $args);
        });

        return $this;
    }
}

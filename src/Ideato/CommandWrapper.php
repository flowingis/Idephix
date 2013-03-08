<?php

namespace Ideato;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Output\OutputInterface;

class CommandWrapper extends Command
{
    private $output;

    public function setCode(\Closure $code)
    {
        parent::setCode(function (InputInterface $input, OutputInterface $output) use ($code)
        {
            $args = $input->getArguments();
            $args += $input->getOptions();

            return call_user_func_array($code, $args);
        });

        return $this;
    }

    protected function initialize(InputInterface &$input, OutputInterface $output)
    {
        $input = $this->filterByOriginalDefinition($input);
    }

    private function filterByOriginalDefinition(InputInterface $input)
    {
        $newDefinition = new InputDefinition();
        $newInput = new ArrayInput(array(), $newDefinition);

        foreach ($input->getArguments() as $name => $value) {
            if (!$this->getApplication()->getDefinition()->hasArgument($name)) {
                $newDefinition->addArgument(
                    $this->getDefinition()->getArgument($name)
                );
                $newInput->setArgument($name, $value);
            }
        }

        foreach ($input->getOptions() as $name => $value) {
            if (!$this->getApplication()->getDefinition()->hasOption($name)) {
                $newDefinition->addOption(
                    $this->getDefinition()->getOption($name)
                );
                $newInput->setOption($name, $value);
            }
        }

        return $newInput;
    }
}

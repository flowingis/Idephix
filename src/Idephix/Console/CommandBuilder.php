<?php
namespace Idephix\Console;

use Idephix\Task\Definition;
use Idephix\Task\Parameter;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CommandBuilder
{
    /**
     * @return Command
     */
    public static function fromTask(Definition $task)
    {
        $command = new Command($task->name());
        $command->setDescription($task->description());

        /** @var Parameter $parameter */
        foreach ($task->parameters() as $parameter){
            if(!$parameter->isOptional()){
                $command->addArgument($parameter->name(), InputArgument::REQUIRED, $parameter->description());
                continue;
            }

            if($parameter->isFlagOption()){
                $command->addOption($parameter->name(), null, InputOption::VALUE_NONE, $parameter->description());
                continue;
            }

            $command->addArgument(
                $parameter->name(),
                InputArgument::OPTIONAL,
                $parameter->description(),
                $parameter->defaultValue()
            );
            
            
        }

        return $command;
    }
}

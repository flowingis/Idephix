<?php
namespace Idephix\Console;

use Idephix\TaskExecutor;
use Idephix\Task\Parameter;
use Idephix\Task\Task;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    private $idxTaskCode;
    /** @var  TaskExecutor */
    private $idx;
    /** @var  Task */
    private $task;

    /**
     * @param Task $task
     * @param TaskExecutor $idx
     * @return Command
     */
    public static function fromTask(Task $task, TaskExecutor $idx)
    {
        $command = new static($task->name());
        $command->task = $task;
        $command->idx = $idx;

        $command->setDescription($task->description());

        /** @var Parameter\UserDefined $parameter */
        foreach ($task->userDefinedParameters() as $parameter) {
            if (!$parameter->isOptional()) {
                $command->addArgument($parameter->name(), InputArgument::REQUIRED, $parameter->description());
                continue;
            }

            if ($parameter->isFlagOption()) {
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

        $command->idxTaskCode = $task->code();

        return $command;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        return call_user_func_array($this->idxTaskCode, $this->extractArgumentsFrom($input));
    }

    /**
     * Create an array of arguments to call the defined task
     *
     * We remove all the arguments and the options defined
     * for the command and we use the rest for the closure. This
     * will remove all default args/options defined within command
     * definition.
     *
     * @param InputInterface $input
     * @return array
     */
    protected function extractArgumentsFrom(InputInterface $input)
    {
        $args = array();
        /** @var Parameter\UserDefined $parameter */
        foreach ($this->task->parameters() as $parameter) {
            if ($parameter instanceof Parameter\Context) {
                $args[] = $this->idx->getCurrentTarget();
                continue;
            }

            if (false === $parameter->defaultValue()) {
                $args[] = $input->getOption($parameter->name());
                continue;
            }

            $args[] = $input->getArgument($parameter->name());
        }

        return $args;
    }
}

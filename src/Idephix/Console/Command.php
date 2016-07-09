<?php
namespace Idephix\Console;

use Idephix\IdephixInterface;
use Idephix\Task\IdephixParameter;
use Idephix\Task\ParameterCollection;
use Idephix\Task\Task;
use Idephix\Task\UserDefinedParameter;
use Idephix\Util\DocBlockParser;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    private $idxTaskCode;
    /** @var  IdephixInterface */
    private $idx;
    /** @var  Task */
    private $task;

    /**
     * Build a command from callable code
     *
     * This is maintained only to support legacy idxfile and will soon removed
     *
     * @param callable $code
     * @return $this
     * @deprecated
     */
    public static function buildFromCode($name, $code, IdephixInterface $idx)
    {
        if (!is_callable($code)) {
            throw new \InvalidArgumentException('Code must be a callable');
        }

        $parameters = ParameterCollection::dry();

        $reflector = new \ReflectionFunction($code);
        $parser = new DocBlockParser($reflector->getDocComment());

        foreach ($reflector->getParameters() as $parameter) {
            if ($parameter->getName() == 'idx') {
                $parameters[] = IdephixParameter::create();
                continue;
            }

            $description = $parser->getParamDescription($parameter->getName());
            $default = $parameter->isDefaultValueAvailable() ? $parameter->getDefaultValue() : null;
            $parameters[] = UserDefinedParameter::create($parameter->getName(), $description, $default);
        }

        $task = new Task($name, $parser->getDescription(), $code, $parameters);
        return static::fromTask($task, $idx);
    }

    /**
     * @param Task $task
     * @return Command
     */
    public static function fromTask(Task $task, IdephixInterface $idx)
    {
        $command = new static($task->name());
        $command->task = $task;
        $command->idx = $idx;

        $command->setDescription($task->description());

        /** @var UserDefinedParameter $parameter */
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
        /** @var UserDefinedParameter $parameter */
        foreach ($this->task->parameters() as $parameter) {
            if ($parameter instanceof IdephixParameter) {
                $args[] = $this->idx;
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

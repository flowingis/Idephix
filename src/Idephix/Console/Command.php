<?php
namespace Idephix\Console;

use Idephix\IdephixInterface;
use Idephix\Task\Task;
use Idephix\Task\Parameter;
use Idephix\Util\DocBlockParser;
use Symfony\Component\Console\Command\Command as SymfonyCommand;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class Command extends SymfonyCommand
{
    private $idxTaskCode;
    /** @var  IdephixInterface */
    private $idx;

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
        $command = new static($name);
        $command->idx = $idx;

        $command->assertCallable($code);
        $command->idxTaskCode = $code;

        $reflector = new \ReflectionFunction($code);
        $parser = new DocBlockParser($reflector->getDocComment());
        $command->setDescription($parser->getDescription());

        foreach ($reflector->getParameters() as $parameter) {
            $description = $parser->getParamDescription($parameter->getName());

            if ($parameter->getName() !== 'idx') {
                $command->addParameter($parameter, $description);
            }
        }

        return $command;
    }

    /**
     * @param Task $task
     * @return Command
     */
    public static function fromTask(Task $task, IdephixInterface $idx)
    {
        $command = new static($task->name());
        $command->idx = $idx;

        $command->setDescription($task->description());

        /** @var Parameter $parameter */
        foreach ($task->parameters() as $parameter) {
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

        $command->assertCallable($task->code());
        $command->idxTaskCode = $task->code();

        return $command;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @param string $description
     */
    private function addParameter(\ReflectionParameter $parameter, $description)
    {
        $name = $parameter->getName();

        if (!$parameter->isOptional()) {
            $this->addArgument($name, InputArgument::REQUIRED, $description);

            return;
        }

        if ($this->isFlagOption($parameter)) {
            $this->addOption($name, null, InputOption::VALUE_NONE, $description);

            return;
        }

        $default = $parameter->getDefaultValue();
        $this->addArgument($name, InputArgument::OPTIONAL, $description, $default);
    }


    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $input = $this->inheritDefinitionFrom(
            $this->getApplication()->getDefinition(),
            $input
        );

        $idxTask = new \ReflectionFunction($this->idxTaskCode);
        $idxArguments = $idxTask->getParameters();

        $args = $input->getArguments();
        $args += $input->getOptions();

        if (!empty($idxArguments) && $idxArguments[0]->getName() == 'idx') {
            array_unshift($args, $this->idx);
        }

        return call_user_func_array($this->idxTaskCode, $args);
    }

    /**
     * @param InputInterface $input
     * @param $appDefinition
     * @return InputInterface
     */
    private function inheritDefinitionFrom(InputDefinition $appDefinition, InputInterface $input)
    {
        $newDefinition = new InputDefinition();
        $newInput = new ArrayInput(array(), $newDefinition);

        foreach ($input->getArguments() as $name => $value) {
            if (!$appDefinition->hasArgument($name)) {
                $newDefinition->addArgument(
                    $this->getDefinition()->getArgument($name)
                );
                if (!empty($value)) {
                    $newInput->setArgument($name, $value);
                }
            }
        }

        foreach ($input->getOptions() as $name => $value) {
            if (!$appDefinition->hasOption($name)) {
                $newDefinition->addOption(
                    $this->getDefinition()->getOption($name)
                );
                if (!empty($value)) {
                    $newInput->setOption($name, $value);
                }
            }
        }

        return $newInput;
    }

    /**
     * @param \ReflectionParameter $parameter
     * @return bool
     */
    private function isFlagOption(\ReflectionParameter $parameter)
    {
        return false === $parameter->getDefaultValue();
    }

    /**
     * @param callable $code
     */
    private function assertCallable($code)
    {
        if (!is_callable($code)) {
            throw new \InvalidArgumentException('Code must be a callable');
        }
    }
}

<?php

namespace Idephix;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Idephix\Util\DocBlockParser;

class CommandWrapper extends Command
{
    /**
     * @param callable $code
     */
    public function buildFromCode($code)
    {
        $this->assertCallable($code);

        $this->setCode($code);

        $reflector = new \ReflectionFunction($code);
        $parser = new DocBlockParser($reflector->getDocComment());
        $this->setDescription($parser->getDescription());

        foreach ($reflector->getParameters() as $parameter) {
            $description = $parser->getParamDescription($parameter->getName());
            $this->addParameter($parameter, $description);
        }
    }

    /**
     * @param callable $code
     * @return $this|Command
     */
    public function setCode($code)
    {
        $this->assertCallable($code);

        $command = $this;
        parent::setCode(function (InputInterface $input) use ($code, $command) {
            $input = $command->filterByOriginalDefinition(
                $input,
                $command->getApplication()->getDefinition()
            );

            $args = $input->getArguments();
            $args += $input->getOptions();

            return call_user_func_array($code, $args);
        });

        return $this;
    }

    /**
     * @param InputInterface $input
     * @param $appDefinition
     * @return ArrayInput
     */
    public function filterByOriginalDefinition(InputInterface $input, $appDefinition)
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
     * @param string $description
     */
    public function addParameter(\ReflectionParameter $parameter, $description)
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
    protected function assertCallable($code)
    {
        if (!is_callable($code)) {
            throw new \InvalidArgumentException("Code must be a callable");
        }
    }
}

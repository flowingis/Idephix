<?php

namespace Idephix;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Output\OutputInterface;
use Idephix\Util\DocBlockParser;


class CommandWrapper extends Command
{
    public function buildFromCode(\Closure $code)
    {
        $this->setCode($code);

        $reflector = new \ReflectionFunction($code);
        $parser = new DocBlockParser($reflector->getDocComment());
        $this->setDescription($parser->getDescription());

        foreach ($reflector->getParameters() as $parameter) {
            $description = $parser->getParamDescription($parameter->getName());
            $this->addParameter($parameter, $description);
        }
    }

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
        $input = $this->filterByOriginalDefinition(
            $input,
            $this->getApplication()->getDefinition()
        );
    }

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

    private function isFlagOption(\ReflectionParameter $parameter)
    {
        return false === $parameter->getDefaultValue();
    }
}

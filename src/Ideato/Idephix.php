<?php

namespace Ideato;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Ideato\CommandWrapper;

class Idephix
{
    private $application;
    private $library = array();
    private $output;

    public function __construct()
    {
        $this->application = new Application();
        $this->output = new ConsoleOutput();
    }

    /**
     * Per i parametri tipo "--go" devono essere definiti come "bool $go=null"
     * @param $name
     * @param Closure $code
     */
    public function add($name, \Closure $code)
    {
        $command = new CommandWrapper($name);
        $command->setCode($code);

        $reflector = new \ReflectionFunction($code);

        if (preg_match('/\s*\*\s*@[Dd]escription(.*)/', $reflector->getDocComment(), $matches)) {
            $command->setDescription(trim($matches[1], '*/ '));
        }
        foreach ($reflector->getParameters() as $parameter) {
            if ($parameter->isOptional()) {
                if ($this->isParameterBoolean($parameter)) {
                    $command->addOption(
                       $parameter->getName(),
                       null,
                       InputOption::VALUE_NONE
                    );                    
                } else {
                    $command->addArgument(
                        $parameter->getName(),
                        InputArgument::OPTIONAL,
                        '',
                        $parameter->getDefaultValue()
                    );
                }
            } else {
                $command->addArgument(
                    $parameter->getName(),
                    InputArgument::REQUIRED
                );
            }
        }

        $this->application->add($command);

        return $this;
    }

    public function run()
    {
        $this->application->run(null, $this->output);
    }

    public function addLibrary($library)
    {
        $this->library[] = $library;
    }

    /**
     * runTask
     * @param  string $name the name of the task you want to call
     * @param  (...) arbitrary number of parameter maching the target task interface
     */
    public function runTask($name)
    {
        if (!$this->application->has($name)) {
            throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }

        $arguments = new ArgvInput(array_merge(array('exec_placeholder'), func_get_args()));

        return $this->application->get($name)->run($arguments, $this->output);
    }

    public function __call($name, $arguments = array())
    {
        foreach ($this->library as $library) {
            if (is_callable(array($library, $name))) {
                return call_user_func_array(array($library, $name), $arguments);
                break;
            }
        }

        throw new \BadMethodCallException('Call to undefined method: "'.$name.'"');
    }

    private function isParameterBoolean(\ReflectionParameter $parameter)
    {
        return
            strpos((string)$parameter, ' bool or NULL $'.$parameter->getName())|
            strpos((string)$parameter, ' boolean or NULL $'.$parameter->getName());
    }

    public function __get($name)
    {
        if ($name == 'output') {
            return $this->output;
        }
    }

    public function local($cmd)
    {
        $this->output->writeln("<info>Exec</info>: $cmd");
        $process = new Process($cmd);
        $output = $this->output;
        $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });
    }
}

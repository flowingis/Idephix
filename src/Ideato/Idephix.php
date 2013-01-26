<?php

namespace Ideato;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Ideato\CommandWrapper;

class Idephix
{
    private $application;
    private $library = array();
    private $consoleOutput;

    public function __construct()
    {
        $this->application = new Application();
    }

    /**
     * @todo come facciamo i parametri tipo "--go"? Con convention? Tipo se il nome è flag_* allora...
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
                $command->addArgument($parameter->getName(), InputArgument::OPTIONAL, '', $parameter->getDefaultValue());
            } else {
                $command->addArgument($parameter->getName(), InputArgument::REQUIRED);
            }
//            ->addOption('yell', null, InputOption::VALUE_NONE, 'If set, the task will yell in uppercase letters')
        }

        $this->application->add($command);

        return $this;
    }

    public function run()
    {
        $this->consoleOutput = new ConsoleOutput();
        $this->application->run(null, $this->consoleOutput);
    }

    public function addLibrary($library)
    {
        $this->library[] = $library;
    }

    public function runTask($name, InputInterface $arguments = null)
    {
        if (!$this->application->has($name)) {
            throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }

        if (empty($arguments)) {
            $arguments = new ArgvInput();
        }

        return $this->application->get($name)->run($arguments, $this->consoleOutput);
    }

    public function __call($name, $arguments = array())
    {
        foreach ($this->library as $library) {
            if (is_callable(array($library, $name))) {
                call_user_func_array(array($library, $name), $arguments);
                break;
            }
        }
    }
}

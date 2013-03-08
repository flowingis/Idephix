<?php

namespace Ideato;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Ideato\Application;
use Ideato\CommandWrapper;
use Ideato\SSH\SshClient;
use Ideato\Util\DocBlockParser;

class Idephix
{
    private $application;
    private $library = array();
    private $output;
    private $sshClient;
    private $targets = array();
    private $currentTarget;
    private $currentTargetName;

    public function __construct(SshClient $sshClient = null, array $targets = null)
    {
        $this->application = new Application();
        $definition = $this->application->getDefinition();
        $definition->addOption(new InputOption('--env', null, InputOption::VALUE_REQUIRED, 'Set remote environment.'));
        $this->output = new ConsoleOutput();
        $this->sshClient = $sshClient;
        $this->targets = $targets;
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

    public function __get($name)
    {
        if ($name === 'output' || $name === 'sshClient') {
            return $this->$name;
        }

        $trace = debug_backtrace();
        trigger_error(
            'Undefined property: '.$name.
            ' in '.$trace[0]['file'].
            ' on line '.$trace[0]['line'],
            E_USER_NOTICE);

        return null;
    }

    /**
     * Per i parametri tipo "--go" devono essere definiti come "$go = false"
     *
     * @param string  $name
     * @param Closure $code
     *
     * @return Idephix
     */
    public function add($name, \Closure $code)
    {
        $command = new CommandWrapper($name);
        $command->setCode($code);

        $reflector = new \ReflectionFunction($code);
        $parser = new DocBlockParser($reflector->getDocComment());
        $command->setDescription($parser->getDescription());

        foreach ($reflector->getParameters() as $parameter) {
            $description = $parser->getParamDescription($parameter->getName());

            if ($parameter->isOptional()) {
                if ($this->isBooleanOption($parameter)) {
                    $command->addOption(
                        $parameter->getName(),
                        null,
                        InputOption::VALUE_NONE,
                        $description
                    );
                } else {
                    $command->addArgument(
                        $parameter->getName(),
                        InputArgument::OPTIONAL,
                        $description,
                        $parameter->getDefaultValue()
                    );
                }
            } else {
                $command->addArgument(
                    $parameter->getName(),
                    InputArgument::REQUIRED,
                    $description
                );
            }
        }

        $this->application->add($command);

        return $this;
    }

    private function buildEnvironment(InputInterface $input)
    {
        $this->currentTarget = null;
        $this->currentTargetName = null;
        $env = $input->getParameterOption(array('--env'));
        if (false !== $env && !empty($env)) {
            if (!isset($this->targets[$env])) {
                throw new \Exception(
                    sprintf(
                        'Wrong environment "%s". Available [%s]',
                        $env,
                        implode(', ', array_keys($this->targets))
                    )
                );
            }

            $this->currentTarget = $this->targets[$env];
            $this->currentTargetName = $env;
        }
    }

    private function openRemoteConnection($host)
    {
        if (!empty($this->currentTarget)) {
            $this->sshClient->setParameters($this->currentTarget['ssh_params']);
            $this->sshClient->setHost($host);
            $this->sshClient->connect();
        }
    }

    private function closeRemoteConnection()
    {
        if (!empty($this->currentTarget)) {
            $this->sshClient->disconnect();
        }
    }

    private function isBooleanOption(\ReflectionParameter $parameter)
    {
        return false === $parameter->getDefaultValue();
    }

    public function getCurrentTarget()
    {
        return $this->currentTarget;
    }

    public function getCurrentTargetName()
    {
        return $this->currentTargetName;
    }

    public function run()
    {
        $input = new ArgvInput();
        try {
            $this->buildEnvironment($input);
        } catch (\Exception $e) {
            $this->output->writeln('<error>'.$e->getMessage().'</error>');

            return;
        }

        // @todo devi ciclare per tutti gli hosts
        $host = isset($this->currentTarget['hosts']) ?
            current($this->currentTarget['hosts']) :
            null;
        $this->openRemoteConnection($host);
        $this->application->run($input, $this->output);
        $this->closeRemoteConnection();
    }

    public function addLibrary($library)
    {
        $this->library[] = $library;
    }

    /**
     * runTask
     * @param string $name the name of the task you want to call
     * @param (...)  arbitrary number of parameter maching the target task interface
     */
    public function runTask($name)
    {
        if (!$this->application->has($name)) {
            throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }

        $arguments = new ArgvInput(array_merge(array('exec_placeholder'), func_get_args()));

        return $this->application->get($name)->run($arguments, $this->output);
    }

    public function remote($cmd, $dryRun = false)
    {
        $this->output->writeln('<info>Remote</info>: '.$cmd);
        if (!$dryRun) {
            return $this->sshClient->exec($cmd);
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

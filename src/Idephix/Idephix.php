<?php

namespace Idephix;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Idephix\Application;
use Idephix\CommandWrapper;
use Idephix\SSH\SshClient;
use Idephix\Extension\IdephixAwareInterface;

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
        $this->application = new Application('Idephix', '0.1');
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
        $command->buildFromCode($code);

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

    public function addLibrary($name, $library)
    {
        if (!is_object($library)) {
            throw new \InvalidArgumentException('The library must be an object');
        }

        if ($library instanceof IdephixAwareInterface) {
            $library->setIdephix($this);
        }

        $this->library[$name] = $library;
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

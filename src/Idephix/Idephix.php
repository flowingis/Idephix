<?php

namespace Idephix;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Output\ConsoleOutput;
use Symfony\Component\Process\Process;
use Idephix\Application;
use Idephix\CommandWrapper;
use Idephix\SSH\SshClient;
use Idephix\Extension\IdephixAwareInterface;
use Idephix\Extension\SelfUpdate\SelfUpdate;

class Idephix
{
    const VERSION = '@package_version@';
    private $application;
    private $library = array();
    private $output;
    private $sshClient;
    private $targets = array();
    private $currentTarget;
    private $currentTargetName;

    public function __construct(SshClient $sshClient = null, array $targets = null, OutputInterface $output = null)
    {
        $this->application = new Application('Idephix', self::VERSION);
        $this->sshClient = $sshClient;
        $this->targets = $targets;

        if (null === $output) {
            $output = new ConsoleOutput();
        }
        $this->output = $output;
        $this->addSelfUpdateCommand();
    }

    public function __call($name, $arguments = array())
    {
        if (isset($this->library[$name])) {
            return $this->library[$name];
        }

        foreach ($this->library as $libName => $library) {
            if (is_callable(array($library, $name))) {
                return call_user_func_array(array($library, $name), $arguments);
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

            $this->currentTarget = array_merge(
                array('hosts' => array()),
                $this->targets[$env]
            );
            $this->currentTargetName = $env;
        }
    }

    private function hasTarget()
    {
        return null !== $this->currentTarget;
    }

    private function openRemoteConnection($host)
    {
        if ($this->hasTarget()) {
            $this->sshClient->setParameters($this->currentTarget['ssh_params']);
            $this->sshClient->setHost($host);
            $this->sshClient->connect();
        }
    }

    private function closeRemoteConnection()
    {
        if ($this->hasTarget()) {
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

        $hosts = $this->hasTarget() ? $this->currentTarget['hosts'] : array(null);

        foreach ($hosts as $host) {
            $this->openRemoteConnection($host);
            $this->application->run($input, $this->output);
            $this->closeRemoteConnection();
        }
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

    public function has($name)
    {
        return $this->application->has($name);
    }

    /**
     * runTask
     * @param string $name the name of the task you want to call
     * @param (...)  arbitrary number of parameter maching the target task interface
     */
    public function runTask($name)
    {
        if (!$this->has($name)) {
            throw new \InvalidArgumentException(sprintf('The command "%s" does not exist.', $name));
        }

        $arguments = new ArgvInput(array_merge(array('exec_placeholder'), func_get_args()));

        return $this->application->get($name)->run($arguments, $this->output);
    }

    public function addSelfUpdateCommand()
    {
        if ('phar:' === substr(__FILE__, 0, 5)) {
            $this->addLibrary('selfUpdate', new SelfUpdate());
            $idx = $this;
            $this
                ->add(
                    'selfupdate',
                    /**
                     * Donwload and update Idephix
                     */
                    function () use ($idx)
                    {
                        $idx->selfUpdate()->update();
                    }
                );
        }
    }

    /**
     * Execute remote command
     *
     * @param string  $cmd command
     * @param boolean $dryRun
     */
    public function remote($cmd, $dryRun = false)
    {
        if (!$this->sshClient->isConnected()) {
            throw new \Exception("Remote function need a valid environment. Specify --env parameter.");
        }
        $this->output->writeln('<info>Remote</info>: '.$cmd);

        if (!$dryRun && 0 != $this->sshClient->exec($cmd)) {
            throw new \Exception("Remote command fail: ".$this->sshClient->getLastError());
        }
    }

    /**
     * Execute local command
     * @param string $cmd Command
     *
     * @return integer The exit status code
     */
    public function local($cmd)
    {
        $output = $this->output;
        $output->writeln("<info>Exec</info>: $cmd");
        $process = new Process($cmd);

        $result = $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });

        if (0 != $result) {
            throw new \Exception("Local command fail: ".$process->getErrorOutput());
        }
    }
}

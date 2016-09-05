<?php

namespace Idephix;

use Symfony\Component\Console\Output\OutputInterface;
use Idephix\Extension\MethodCollection;

class Operations
{
    private $sshClient;

    private $output;

    private $methods;

    public function __construct($sshClient, $output)
    {
        $this->sshClient = $sshClient;
        $this->output = $output;
        $this->methods = MethodCollection::dry();
    }

    public function openRemoteConnection($host, $params)
    {
        if (!is_null($host)) {
            $this->sshClient->setParameters($params);
            $this->sshClient->setHost($host);
            $this->sshClient->connect();
        }
    }

    public function closeRemoteConnection()
    {
        if ($this->sshClient->isConnected()) {
            $this->sshClient->disconnect();
        }
    }

    /**
     * Execute remote command.
     *
     * @param string $cmd command
     * @param boolean $dryRun
     * @throws \Exception
     */
    public function remote($cmd, $dryRun = false)
    {
        if (!$this->sshClient->isConnected()) {
            throw new \Exception('Remote function need a valid environment. Specify --env parameter.');
        }
        $this->output->writeln('<info>Remote</info>: '.$cmd);

        if (!$dryRun && !$this->sshClient->exec($cmd)) {
            throw new \Exception('Remote command fail: '.$this->sshClient->getLastError());
        }
        $this->output->writeln($this->sshClient->getLastOutput());
    }

    /**
     * Execute local command.
     *
     * @param string $cmd Command
     * @param bool $dryRun
     * @param int $timeout
     *
     * @return string the command output
     * @throws \Exception
     */
    public function local($cmd, $dryRun = false, $timeout = 600)
    {
        $output = $this->output;
        $output->writeln("<info>Local</info>: $cmd");

        if ($dryRun) {
            return $cmd;
        }

        $process = $this->buildInvoker($cmd, null, null, null, $timeout);

        $result = $process->run(function ($type, $buffer) use ($output) {
            $output->write($buffer);
        });
        if (0 != $result) {
            throw new \Exception('Local command fail: '.$process->getErrorOutput());
        }

        return $process->getOutput();
    }

    /**
     * Set local command invoker
     * @param string $invokerClassName class name of the local command invoker
     */
    public function setInvoker($invokerClassName)
    {
        $this->invokerClassName = $invokerClassName;
    }

    /**
     * Build command invoker
     * @param string  $cmd     The command line to run
     * @param string  $cwd     The working directory
     * @param array   $env     The environment variables or null to inherit
     * @param string  $stdin   The STDIN content
     * @param integer $timeout The timeout in seconds
     * @param array   $options An array of options for proc_open
     *
     * @return string cmd output
     */
    public function buildInvoker($cmd, $cwd = null, array $env = null, $stdin = null, $timeout = 60, array $options = array())
    {
        $invoker = $this->invokerClassName ?: '\Symfony\Component\Process\Process';

        return new $invoker($cmd, $cwd, $env, $stdin, $timeout, $options);
    }

    public function write($messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->output->write($messages, $newline, $type);
    }

    public function writeln($messages, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->output->writeln($messages, $type);
    }

    public function addMethods(MethodCollection $methods)
    {
        $this->methods = $this->methods->merge($methods);
    }

    public function execute($name, $arguments)
    {
        return $this->methods->execute($name, $arguments);
    }
}

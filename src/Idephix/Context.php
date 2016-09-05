<?php
namespace Idephix;

use Symfony\Component\Console\Output\OutputInterface;

class Context
{
    private $operations;
    private $config;
    private $executor;

    private $currentEnv = null;

    public function __construct(TaskExecutor $executor, Operations $op = null, Config $config = null)
    {
        $this->executor = $executor;
        $this->operations = $op;
        $this->config = $config;
    }

    public static function create(TaskExecutor $executor, Config $config, Operations $op)
    {
        return new static($executor, $config, $op);
    }

    public function getConfig()
    {
        return $this->config;
    }

    public function setEnv($env)
    {
        $this->currentEnv = $env;
    }

    public function getEnv()
    {
        return $this->currentEnv;
    }

    public function getHosts()
    {
        $this->assertEnv();

        return $this->config
                    ->get("envs.{$this->currentEnv}.hosts");
    }

    public function getCurrentHost()
    {
        $this->assertEnv();

        return $this->config
                    ->get("envs.{$this->currentEnv}.hosts")
                    ->current();
    }

    public function getSshParams()
    {
        $this->assertEnv();

        return $this->config
                    ->get("envs.{$this->currentEnv}.hosts")
                    ->current();
    }

    public function openRemoteConnection($host)
    {
        $this->assertEnv();

        $sshParams = $this->config
                          ->get("envs.{$this->currentEnv}.ssh_params");

        $this->operations
             ->openRemoteConnection($this->getCurrentHost(), $sshParams);
    }

    public function closeRemoteConnection()
    {
        $this->operations
             ->closeRemoteConnection();
    }

    public function __call($name, $arguments)
    {
        if ($this->executor->hasTask($name)) {
            return $this->executor->runTask($name, $arguments);
        }

        return $this->operations->execute($name, $arguments);
    }

    /**
     * Execute remote command.
     *
     * @param string $cmd command
     * @param boolean $dryRun
     * @return void
     */
    public function remote($cmd, $dryRun = false)
    {
        $this->operations->remote($cmd, $dryRun);
    }

    /**
     * Execute local command.
     *
     * @param string $cmd Command
     * @param boolean $dryRun
     * @param integer $timeout
     *
     * @return string the command output
     */
    public function local($cmd, $dryRun = false, $timeout = 60)
    {
        return $this->operations->local($cmd, $dryRun, $timeout);
    }

    public function output()
    {
        return $this->operations->output();
    }

    public function write($messages, $newline = false, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->operations->write($messages, $newline, $type);
    }

    public function writeln($messages, $type = OutputInterface::OUTPUT_NORMAL)
    {
        $this->operations->writeln($messages, $type);
    }

    private function assertEnv()
    {
        if (!$this->currentEnv) {
            throw new \RunTimeException('Missing env param');
        }
    }
}

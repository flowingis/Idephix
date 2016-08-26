<?php
namespace Idephix;

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
        //todo add check on currentEnv

        return $this->config['envs'][$this->currentEnv]['hosts'];
    }

    public function openRemoteConnection($host)
    {
        if (!is_null($host)) {
            $this->sshClient->setParameters($this->config['ssh_params']);
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

    public function __call($name, $arguments)
    {
        return $this->executor->runTask($name, $arguments);
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

    public function write($messages, $newline = false, $type = self::OUTPUT_NORMAL)
    {
        $this->operations->write($messages, $newline, $type);
    }

    public function writeln($messages, $type = self::OUTPUT_NORMAL)
    {
        $this->operations->writeln($messages, $type);
    }

}
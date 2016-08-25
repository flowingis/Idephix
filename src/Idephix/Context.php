<?php
namespace Idephix;

class Context implements TaskExecutor
{
    private $operations;
    private $config;

    private $currentEnv = null;

    public function __construct(TaskExecutor $idx, Operations $op = null, Config $config = null)
    {
        $this->idx = $idx;
        $this->operations = $op;
        $this->config = $config;
    }

    public static function create(TaskExecutor $idx, Config $config, Operations $op)
    {
        return new static($idx, $config, $op);
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

    /**
     * @param $name
     * @return integer 0 success, 1 fail
     */
    public function execute($name)
    {
        call_user_func_array(array($this->idx, 'execute'), func_get_args());
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


    public function __call($name, $arguments)
    {
        return call_user_func_array(array($this->idx, $name), $arguments);
    }

}
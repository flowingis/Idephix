<?php

namespace Ideato\SSH;

use Symfony\Component\Process\Process;

class CLISshProxy extends BaseProxy
{
    protected $executable = 'ssh';
    protected $host;
    protected $port = 22;
    protected $user = '';
    protected $password = '';
    protected $privateKeyFile = null;

    private function canConnect()
    {
        if (0 == $this->exec('echo "connected"') &&
            false !== strpos($this->lastOutput, 'connected')) {
            return true;
        }
    }

    public function setExecutable($executable)
    {
        $this->executable = $executable;
    }

    public function connect($host, $port = 22)
    {
        $this->host = $host;
        $this->port = $port;

        return true;
    }

    public function authByPassword($user, $pwd)
    {
        throw new \Exception("Not implemented");
    }

    public function authByAgent($user)
    {
        $this->user = $user;

        return $this->canConnect();
    }

    public function authByPublicKey($user, $public_key_file, $privateKeyFile, $pwd)
    {
        $this->user = $user;
        $this->privateKeyFile = $privateKeyFile;

        return $this->canConnect();
    }

    public function exec($cmd)
    {
        $preparedCmd = $this->prepareCommand($cmd);
        $process = new Process($preparedCmd);
        $process->run();
        $this->lastOutput = $process->getOutput();
        $this->lastError = $process->getErrorOutput();
        $process->getExitCode();

        return $process->getExitCode();
    }

    private function prepareCommand($cmd)
    {
        $user = $this->user ? '-l '.$this->user : '';
        $keyFile = $this->privateKeyFile ? '-i '.$this->privateKeyFile : '';

        return sprintf(
            "%s -p %s %s %s %s %s",
            $this->executable,
            $this->port,
            $keyFile,
            $user,
            $this->host,
            escapeshellarg($cmd));
    }
}
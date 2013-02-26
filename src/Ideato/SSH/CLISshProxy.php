<?php

namespace Ideato\SSH;

class CLISshProxy implements ProxyInterface
{
    protected $executable = 'ssh';
    protected $connection = null;
    protected $host;
    protected $port = 22;
    protected $user = '';
    protected $password = '';
    protected $private_key_file = null;

    private function canConnect()
    {
        if ('connected' == trim($this->exec('echo "connected"'))) {

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

    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd)
    {
        $this->user = $user;
        $this->private_key_file = $private_key_file;
        
        return $this->canConnect();
	}

	public function exec($cmd)
    {
        exec($this->prepareCommand($cmd), $output);

        $output = implode("\n", $output);

        if (strstr($output,'#RETOK#')) {
			$output = strtr($output, array('#RETOK#' => ''));
		}

		return $output;
	}

    private function prepareCommand($cmd)
    {
        $user = $this->user ? '-l '.$this->user : '';
        $key_file = $this->private_key_file ? '-i '.$this->private_key_file : '';

        return sprintf(
                "%s -p %s %s %s %s '%s && echo \"#RETOK#\"'",
                $this->executable,
                $this->port,
                $key_file,
                $user,
                $this->host,
                $cmd);
    }
}
<?php

namespace Ideato\SSH;

class CLISshProxy implements ProxyInterface
{
    protected $executable = 'ssh';
    protected $connection = null;
    protected $host;
    protected $port = 21;
    protected $user = '';
    protected $password = '';

    private function canConnect()
    {
        if ('connected' == $this->exec('echo "connected"')) {

            return true;
        }
    }

    public function setExecutable($executable)
    {
        $this->executable = $executable;
    }

    public function connect($host, $port = 21)
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
        $user = $this->user ? '-l '.$this->user : '';
        $key_file = $this->private_key_file ? '-i '.$this->private_key_file : '';
        exec(
            sprintf(
                "%s -p %s %s %s %s '%s && echo \"#RETOK#\"'",
                $this->executable,
                $this->port,
                $key_file,
                $user,
                $this->host,
                $cmd),
            $output);
        $output = implode("\n", $output);

        if (strstr($output,'#RETOK#')) {
			$output = strtr($output, array('#RETOK#' => ''));
		}

		return $output;
	}
}
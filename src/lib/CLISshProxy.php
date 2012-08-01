<?php

namespace Ideato\Deploy;

class CLISshProxy
{
    protected $connection = null;
    protected $host;
    protected $port = 21;
    protected $user = '';

    public function connect($host, $port = 21, $user = '')
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;

        return true;
    }

    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd)
    {
		return true;
	}

	public function exec($cmd)
    {
        $user = $this->user ? '-l '.$this->user : '';
        exec('ssh -p '.$this->port." ".$user." ".$this->host." '".$cmd.' && echo "#RETOK#"\'', $output);
        $output = implode("\n", $output);

        if (strstr($output,'#RETOK#')) {
			$output = strtr($output, array('#RETOK#' => ''));
		}

		return $output;
	}
}
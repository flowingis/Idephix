<?php

namespace Ideato\SSH;

class PeclSsh2Proxy implements ProxyInterface
{
    protected $connection = null;

    public function connect($host, $port)
    {
        if (!empty($this->connection)) {
            return $this->connection;
        }
        $this->connection = ssh2_connect($host, $port, NULL, array('disconnect', array($this, 'disconnect')));

        return $this->connection;
    }

    public function authByPassword($user, $password)
    {
        return ssh2_auth_password($this->connection, $user, $password);
    }

    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd)
    {
        return ssh2_auth_pubkey_file($this->connection, $user, $public_key_file, $private_key_file, $pwd);
    }

    public function authByAgent($user)
    {
        if (!function_exists('ssh2_auth_agent')) {
            throw new \Exception("ssh2_auth_agent does not exists");
        }

		return ssh2_auth_agent($this->connection, $user);
	}

	public function disconnect($reason, $message, $language)
    {
		$this->connection = NULL;
	}

	public function exec($cmd)
    {
		$stdout = ssh2_exec($this->connection, $cmd." && echo 'RETOK'", 'ansi');
		$stderr = ssh2_fetch_stream($stdout, SSH2_STREAM_STDERR);

		stream_set_blocking($stderr, true);
		$errors = stream_get_contents($stderr);

		stream_set_blocking($stdout, true);
		$output = stream_get_contents($stdout);

        if (strstr($output, 'RETOK')) {
			$output = substr($output, 0, strpos($output, 'RETOK'));
		}

		return $output.$errors;
	}
}
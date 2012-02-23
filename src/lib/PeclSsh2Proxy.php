<?php

namespace Ideato\Deploy;

class PeclSsh2Proxy
{
  protected $connection = null;

	public function connect($host, $port)
  {
		$this->connection = ssh2_connect($host, $port, NULL, array('disconnect', array($this, 'disconnect')));

    return $this->connection;
  }

  public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd)
  {
		return ssh2_auth_pubkey_file($this->connection, $user, $public_key_file, $private_key_file, $pwd);
	}

	private function disconnect($reason, $message, $language) {
		$this->connection = NULL;
	}

	function exec($cmd) {
		$stdout = ssh2_exec($this->connection, $cmd." && echo 'RETOK'", 'ansi');
		$stderr = ssh2_fetch_stream($stdout, SSH2_STREAM_STDERR);

		stream_set_blocking($stderr, true);
		$errors = stream_get_contents($stderr);

		stream_set_blocking($stdout, true);
		$output = stream_get_contents($stdout);

    if (strstr($output,'RETOK')) {
			$output = substr($output, 0, strpos($output,'RETOK'));
		}

		return $output.$errors;
	}
}
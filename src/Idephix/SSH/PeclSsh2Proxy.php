<?php

namespace Idephix\SSH;

class PeclSsh2Proxy extends BaseProxy
{

    public function connect($host, $port)
    {
        if (!empty($this->connection)) {
            return $this->connection;
        }
        $this->connection = ssh2_connect($host, $port, null, array('disconnect', array($this, 'disconnect')));

        return $this->connection;
    }

    public function authByPassword($user, $password)
    {
        return ssh2_auth_password($this->connection, $user, $password);
    }

    public function authByPublicKey($user, $publicKeyFile, $privateKeyFile, $pwd)
    {
        return ssh2_auth_pubkey_file($this->connection, $user, $publicKeyFile, $privateKeyFile, $pwd);
    }

    public function authByAgent($user)
    {
        if (!function_exists('ssh2_auth_agent')) {
            throw new \Exception("ssh2_auth_agent does not exists");
        }

        return ssh2_auth_agent($this->connection, $user);
    }

    public function exec($cmd)
    {
        $stdout = ssh2_exec($this->connection, $cmd.'; echo "_RETURNS_:$?:"', 'ansi');
        $stderr = ssh2_fetch_stream($stdout, SSH2_STREAM_STDERR);

        stream_set_blocking($stderr, true);
        $this->lastError = stream_get_contents($stderr);

        stream_set_blocking($stdout, true);
        $this->lastOutput = stream_get_contents($stdout);

        $returnCode = null;

        $pos = strpos($this->lastOutput, '__RETURNS__:');
        if (false !== $pos) {
            $returnCode = substr($this->lastOutput, $pos, -1);
            $this->lastOutput = substr($this->lastOutput, 0, $pos);
        }

        return $returnCode;
    }

    public function disconnect()
    {
        if (null !== $this->connection) {
            $this->exec('exit');
            $this->connection = null;
        }
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}

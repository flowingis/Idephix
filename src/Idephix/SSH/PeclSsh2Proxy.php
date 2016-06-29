<?php

namespace Idephix\SSH;

class PeclSsh2Proxy extends BaseProxy
{
    const RETURN_TOKEN = '__RETURNS__:';

    public function connect($host, $port = 22)
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
            throw new \Exception('ssh2_auth_agent does not exists');
        }

        return ssh2_auth_agent($this->connection, $user);
    }

    public function isSuccessful()
    {
        return 0 == $this->getExitCode();
    }

    public function exec($cmd)
    {
        $stdout = ssh2_exec($this->connection, $cmd.'; echo "'.self::RETURN_TOKEN.'$?"', 'ansi');
        $stderr = ssh2_fetch_stream($stdout, SSH2_STREAM_STDERR);

        stream_set_blocking($stderr, true);
        $this->lastError = stream_get_contents($stderr);

        stream_set_blocking($stdout, true);
        $this->lastOutput = stream_get_contents($stdout);

        $this->exitCode = -1;

        $pos = strpos($this->lastOutput, self::RETURN_TOKEN);

        if (false === $pos) {
            return false;
        }

        $this->exitCode = substr($this->lastOutput, $pos + strlen(self::RETURN_TOKEN));
        $this->lastOutput = substr($this->lastOutput, 0, $pos);

        return $this->isSuccessful();
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

    /**
     * @inheritdoc
     */
    public function put($localPath, $remotePath)
    {
        return ssh2_scp_send($this->connection, $localPath, $remotePath);
    }

    /**
     * @inheritdoc
     */
    public function get($remotePath, $localPath)
    {
        return ssh2_scp_recv($this->connection, $remotePath, $localPath);
    }
}

<?php

namespace Idephix\SSH;

class SshClient
{
    private $proxy;
    private $params;
    private $host;
    private $connected = false;

    /**
     * Constructor
     *
     * @param ProxyInterface $proxy
     */
    public function __construct(ProxyInterface $proxy = null)
    {
        if (null === $proxy) {
            $proxy = function_exists('ssh2_auth_agent') ? new PeclSsh2Proxy() : new CLISshProxy();
        }

        $this->proxy = $proxy;
    }

    /**
     * @param array $params array('user', 'password', 'public_key_file', 'private_key_file', 'private_key_file_pwd', 'ssh_port')
     */
    public function setParameters($params)
    {
        $this->connected = false;
        $this->params = array_merge(
            array(
                'user'                 => '',
                'password'             => '',
                'public_key_file'      => '',
                'private_key_file'     => '',
                'private_key_file_pwd' => '',
                'ssh_port'             => '22'),
            $params
        );
    }

    /**
     * @throws \Exception
     */
    public function connect()
    {
        if (null === $this->host) {
            throw new \Exception('You must set the host');
        }
        if (!$this->proxy->connect($this->host, $this->params['ssh_port'])) {
            throw new \Exception('Unable to connect');
        }

        if (!empty($this->params['password']) && !$this->proxy->authByPassword($this->params['user'], $this->params['password'])) {
            throw new \Exception('Unable to authenticate via password');
        }

        if (!empty($this->params['public_key_file']) && !$this->proxy->authByPublicKey($this->params['user'], $this->params['public_key_file'], $this->params['private_key_file'], $this->params['private_key_file_pwd'])) {
            throw new \Exception('Unable to authenticate via public/private keys');
        }

        if (empty($this->params['password']) && empty($this->params['public_key_file']) && !$this->proxy->authByAgent($this->params['user'])) {
            throw new \Exception('Unable to authenticate via agent');
        }

        $this->connected = true;

        return true;
    }

    public function disconnect()
    {
        $this->proxy->disconnect();
        $this->connected = false;
    }

    public function isConnected()
    {
        return $this->connected;
    }

    /**
     * @param string $cmd
     * @return bool
     */
    public function exec($cmd)
    {
        return $this->proxy->exec($cmd);
    }

    public function getLastOutput()
    {
        return $this->proxy->getLastOutput();
    }

    public function getLastError()
    {
        return $this->proxy->getLastError();
    }

    public function isSuccessful()
    {
        return $this->proxy->isSuccessful();
    }

    public function getUser()
    {
        return $this->params['user'];
    }

    public function getPort()
    {
        return $this->params['ssh_port'];
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    public function getHost()
    {
        return $this->host;
    }

    /**
     * Copy a file via scp from $localPath to $remotePath
     *
     * @param $localPath source local path
     * @param $remotePath destination remote path
     * @throws \Exception
     * @return bool
     */
    public function put($localPath, $remotePath)
    {
        if (!$this->isConnected()) {
            throw new \Exception('SSH Client is not connected');
        }

        return $this->proxy->put($localPath, $remotePath);
    }

    /**
     * Copy a file via scp from $remotePath to $localPath
     *
     * @param $remotePath source remote path
     * @param $localPath destination local path
     * @throws \Exception
     * @return bool
     */
    public function get($remotePath, $localPath)
    {
        if (!$this->isConnected()) {
            throw new \Exception('SSH Client is not connected');
        }

        return $this->proxy->get($remotePath, $localPath);
    }
}

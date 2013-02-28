<?php

namespace Ideato\SSH;

class SshClient
{
    private $proxy;
    private $params;
    private $host;

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

        if (is_null($proxy)) {
            $this->proxy = new PeclSsh2Proxy();
        }
    }

    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @param array $options array('user', 'password', 'public_key_file', 'private_key_file', 'private_key_file_pwd', 'ssh_port')
     */
    public function setParams($params)
    {
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
        if (!$this->proxy->connect($this->host, $this->params['ssh_port'])) {
            throw new \Exception("Unable to connect");
        }

        if (!empty($this->params['password']) && !$this->proxy->authByPassword($this->params['user'], $this->params['password'])) {
            throw new \Exception("Unable to authenticate via password");
        }

        if (!empty($this->params['public_key_file']) && !$this->proxy->authByPublicKey($this->params['user'], $this->params['public_key_file'], $this->params['private_key_file'], $this->params['private_key_file_pwd'])) {
            throw new \Exception("Unable to authenticate via public/private keys");
        }

        if (!$this->proxy->authByAgent($this->params['user'])) {
            throw new \Exception("Unable to authenticate via agent");
        }

        return true;
    }

    public function exec($cmd)
    {
        return $this->proxy->exec($cmd);
    }

    public function getUser()
    {
        return $this->params['user'];
    }

    public function getPort()
    {
        return $this->params['ssh_port'];
    }


}
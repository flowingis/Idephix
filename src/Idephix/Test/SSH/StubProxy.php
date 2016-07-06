<?php

namespace Idephix\Test\SSH;

use Idephix\SSH\BaseProxy;

class StubProxy extends BaseProxy
{
    protected $lastOutput;
    protected $lastError;

    public function __construct($fakeOutput = 'test out ', $fakeError = 'test err ')
    {
        $this->lastOutput = $fakeOutput;
        $this->lastError = $fakeError;
    }

    public function connect($host, $port)
    {
        if ('fail_connection' === $host) {
            return false;
        }

        return true;
    }

    public function authByPassword($user, $pwd)
    {
        return true;
    }

    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd)
    {
        return true;
    }

    public function authByAgent($user)
    {
        return true;
    }

    public function exec($cmd)
    {
        $this->lastOutput .= $cmd;
        $this->lastError .= $cmd;

        return $cmd;
    }

    public function isConnected()
    {
        return true;
    }

    public function put($localPath, $remotePath)
    {
    }

    public function get($remotePath, $localPath)
    {
    }
}

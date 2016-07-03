<?php

namespace Idephix\SSH;

class FakeSsh2Proxy extends BaseProxy
{
    protected $test;
    protected $lastOutput;
    protected $lastError;

    public function __construct(\PHPUnit_Framework_TestCase $test, $fakeOutput = 'test out ', $fakeError = 'test err ')
    {
        $this->test = $test;
        $this->lastOutput = $fakeOutput;
        $this->lastError = $fakeError;
    }

    public function connect($host, $port)
    {
        if ('fail_connection' === $host) {
            return false;
        }

        $this->test->assertTrue(true);

        return true;
    }

    public function authByPassword($user, $pwd)
    {
        $this->test->assertTrue(true);

        return true;
    }

    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd)
    {
        $this->test->assertTrue(true);

        return true;
    }

    public function authByAgent($user)
    {
        $this->test->assertTrue(true);

        return true;
    }

    public function exec($cmd)
    {
        $this->test->assertTrue(true);
        $this->lastOutput .= $cmd;
        $this->lastError .= $cmd;

        return $cmd;
    }

    public function isConnected()
    {
        $this->test->assertTrue(true, 'isConnected');

        return true;
    }

    public function put($localPath, $remotePath)
    {
        $this->test->assertTrue(true, 'scp '.$localPath.' '.$remotePath);
    }

    public function get($remotePath, $localPath)
    {
        $this->test->assertTrue(true, 'scp '.$remotePath.' '.$localPath);
    }
}

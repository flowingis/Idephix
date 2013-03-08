<?php

namespace Ideato\SSH;

class FakeSsh2Proxy extends BaseProxy
{
    public function __construct($test)
    {
        $this->test = $test;
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
        $this->lastOutput = 'test out '.$cmd;
        $this->lastError = 'test err '.$cmd;

        return $cmd;
    }
}
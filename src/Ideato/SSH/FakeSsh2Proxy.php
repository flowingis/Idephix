<?php

namespace Ideato\SSH;

class FakeSsh2Proxy
{
    public function __construct($test)
    {
        $this->test = $test;
    }

    public function connect($host, $port)
    {
        $this->test->assertTrue(true);

        return true;
    }

    public function authByPublicKey($user, $public_key_file, $private_key_file, $pwd)
    {
        $this->test->assertTrue(true);

        return true;
    }

    private function disconnect($reason, $message, $language)
    {
    }

    function exec($cmd)
    {
        $this->test->assertTrue(true);

        return $cmd;
    }
}
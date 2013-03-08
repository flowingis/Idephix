<?php

namespace Ideato\SSH;

abstract class BaseProxy implements ProxyInterface
{
    protected $connection = null;
    protected $lastError;
    protected $lastOutput;

    public function disconnect()
    {
        $this->connection = null;
    }

    public function getLastError()
    {
        return $this->lastError;
    }

    public function getLastOutput()
    {
        return $this->lastOutput;
    }
}
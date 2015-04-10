<?php

namespace Idephix\SSH;

abstract class BaseProxy implements ProxyInterface
{
    protected $connection = null;
    protected $lastError;
    protected $lastOutput;
    protected $exitCode;

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

    public function getExitCode()
    {
        return $this->exitCode;
    }
}

<?php
namespace Idephix;

use Idephix\Config\ConfigInterface;
use Idephix\Config\Targets\TargetsInterface;
use Idephix\SSH\SshClient;

class Config implements ConfigInterface
{
    private $targets;
    private $sshClient;

    public static function create()
    {
        return new static();
    }
    
    /**
     * @return TargetsInterface
     */
    public function getTargets()
    {
        return $this->targets;
    }

    /**
     * @return SshClient
     */
    public function getSshClient()
    {
        return $this->sshClient;
    }

    public function targets(TargetsInterface $targets)
    {
        $this->targets = $targets;

        return $this;
    }

    public function sshClient(SshClient $client)
    {
        $this->sshClient = $client;

        return $this;
    }
}

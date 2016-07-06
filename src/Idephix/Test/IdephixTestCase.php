<?php

namespace Idephix\Test;

use Idephix\Config;
use Idephix\Context;
use Idephix\SSH\SshClient;
use Idephix\Test\SSH\StubProxy;
use Symfony\Component\Console\Output\StreamOutput;

class IdephixTestCase extends \PHPUnit_Framework_TestCase
{
    protected $output;

    public function getIdephixMock($targets, $targetName)
    {
        $this->output = fopen('php://memory', 'r+');
        $output = new StreamOutput($this->output);
        $currentTarget = Context::fromArray($targets[$targetName]);
        $sshClient = new SshClient(new StubProxy());
        $sshClient->setParameters($currentTarget->get('ssh_params'));
        $sshClient->setHost(current($currentTarget->get('hosts')));

        $idx = new InspectableIdephix(Config::fromArray(array('sshClient' => $sshClient)), $output);
        $idx = $idx
            ->withCurrentTarget(Context::fromArray($targets[$targetName]), $targetName);

        return $idx;
    }
}

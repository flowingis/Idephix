<?php

namespace Idephix\Test;

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
        $sshClient->connect();

        $idx = $this->getMock('\Idephix\IdephixInterface');

        $idx->expects($this->any())
            ->method('sshClient')
            ->will($this->returnValue($sshClient));
        $idx->expects($this->any())
            ->method('output')
            ->will($this->returnValue($output));
        $idx->expects($this->any())
            ->method('getCurrentTarget')
            ->will($this->returnValue($currentTarget));
        $idx->expects($this->any())
            ->method('getCurrentTargetName')
            ->will($this->returnValue($targetName));
        $idx->expects($this->any())
            ->method('local')
            ->will($this->returnCallback(
                function ($cmd) use ($output) {
                    $output->writeln('Local: '.$cmd);
                }
            ));
        $idx->expects($this->any())
            ->method('remote')
            ->will($this->returnCallback(
                function ($cmd) use ($output) {
                    $output->writeln('Remote: '.$cmd);
                }
            ));

        return $idx;
    }
}

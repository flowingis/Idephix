<?php

namespace Idephix\Test;

use Idephix\Config\Config;
use Idephix\SSH\SshClient;
use Idephix\SSH\FakeSsh2Proxy;
use Symfony\Component\Console\Output\StreamOutput;

class IdephixTestCase extends \PHPUnit_Framework_TestCase
{
    public function getIdephixMock($targets, $targetName)
    {
        $this->output = fopen('php://memory', 'r+');
        $output = new StreamOutput($this->output);
        $currentTarget = new Config($targets[$targetName]);
        $sshClient = new SshClient(new FakeSsh2Proxy($this));
        $sshClient->setParameters($currentTarget->get('ssh_params'));
        $sshClient->setHost(current($currentTarget->get('hosts')));
        $sshClient->connect();

        $idx = $this->getMock('\Idephix\IdephixInterface');
        $idx->sshClient = $sshClient;
        $idx->output = $output;

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

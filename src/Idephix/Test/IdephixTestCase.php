<?php

namespace Idephix\Test;

use Idephix\Config;
use Idephix\Context;
use Idephix\Dictionary;
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
        $sshClient = new SshClient(new StubProxy());
        $sshClient->setParameters($targets[$targetName]['ssh_params']);
        $sshClient->setHost(current($targets[$targetName]['hosts']));

        $idx = new InspectableIdephix(Config::fromArray(array('sshClient' => $sshClient)), $output);
        $idx = $idx
            ->withContext(Context::dry($idx)->target($targetName, Dictionary::fromArray($targets[$targetName])));

        return $idx;
    }
}

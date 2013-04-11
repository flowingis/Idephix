<?php

namespace Idephix\Tests\Test;

include_once __DIR__.'/Idephix.php';

use Idephix\SSH\SshClient;
use Idephix\SSH\FakeSsh2Proxy;
use Symfony\Component\Console\Output\StreamOutput;
use Symfony\Component\Console\Input\StringInput;

class IdephixTestCase extends \PHPUnit_Framework_TestCase
{
    public function getIdephixMock($targets, $targetName)
    {
        $this->output = fopen("php://memory", 'r+');
        $output = new StreamOutput($this->output);

        $idx = new Idephix($targets, new SshClient(new FakeSsh2Proxy($this)), $output, new StringInput('idx --env='.$targetName));
        $idx->initFirstHost();

        return $idx;
    }
}
